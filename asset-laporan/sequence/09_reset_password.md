# Sequence Diagram: Reset Password (via WhatsApp OTP)

```mermaid
sequenceDiagram
    autonumber
    actor User
    participant View as ForgotPassword (Inertia View)
    participant Ctrl as ForgotPasswordController
    participant Limiter as RateLimiter (Facade)
    participant Model as ContactVerification (Model)
    participant DB as Database
    participant Job as SendWhatsappResetPasswordJob

    User->>View: Input Username & Select Role
    User->>View: Request OTP Code
    View->>Ctrl: POST /v2/forgot-password/send-otp (username, role)
    Ctrl->>Ctrl: execute resolveUser(username, role)
    
    alt User Tidak Ditemukan / Nomor WhatsApp Kosong
        Ctrl-->>View: Tampilkan Exception: Pengguna Tidak Ditemukan / No Phone
    end

    Ctrl->>Limiter: tooManyAttempts(limiterKey, 3)
    Limiter-->>Ctrl: Limiter Status (bool)
    
    alt Rate Limit Terlampaui
        Ctrl-->>View: Tampilkan Exception: Rate Limit Exceeded
    else Akses Diizinkan
        Ctrl->>Ctrl: Generate 6-Digit OTP Code
        Ctrl->>Model: where(existing_otp)->delete()
        
        Ctrl->>Model: create([code => Hash::make(code), expires_at])
        Model->>DB: Insert Record ContactVerification
        DB-->>Model: Insert Success
        
        Ctrl->>Job: dispatch(no_telephone, code)
        Job-->>Ctrl: Job Dispatched
        
        Ctrl-->>View: Response JSON: OTP Sent Success (Masked Phone)
        View-->>User: Render Form Input OTP & Password Baru
    end

    Note over User, DB: Langkah 2: Verifikasi & Reset Password
    User->>View: Input OTP & Password Baru
    User->>View: Submit Reset Password
    View->>Ctrl: POST /v2/forgot-password/verify-reset (payload)
    Ctrl->>Ctrl: Execute Validasi: OTP 6 Digit & Validasi Password
    
    Ctrl->>Model: where(verifiable)->first()
    Model->>DB: Select ContactVerification
    DB-->>Model: Verification Record
    
    Ctrl->>Ctrl: Cek Expiration & Execute Hash::check(code)
    
    alt OTP Expired / Kode Mismatch
        Ctrl-->>View: Tampilkan Exception: OTP Invalid / Expired
    else OTP Cocok & Aktif
        Ctrl->>User: update(['password' => Hash::make, 'is_first_login' => false])
        User->>DB: Update User Password
        DB-->>User: Update Success
        
        Ctrl->>Model: delete()
        Model->>DB: Delete ContactVerification Record
        DB-->>Model: Delete Success
        
        Ctrl->>Limiter: clear(limiterKey)
        Ctrl-->>View: Response JSON: Reset Success
        View-->>User: Redirect: Login
    end
```

Sequence diagram ini menggambarkan alur umum pemulihan kata sandi melalui kode OTP WhatsApp oleh Pengguna yang lupa kredensialnya, yang berlaku untuk seluruh aktor yang terdaftar dalam sistem. Pengguna meminta kode verifikasi dengan mengirimkan nama pengguna dan peran, sistem melakukan validasi status akun serta batasan frekuensi pengiriman (rate limit) di database, lalu mengembalikan pesan kesalahan jika data tidak sesuai atau batas percobaan terlampaui. Setelah verifikasi awal sukses dan OTP baru terkirim melalui antrean pesan WhatsApp, pengguna memasukkan kode beserta kata sandi baru yang kemudian divalidasi oleh sistem, dan akhirnya memperbarui data kredensial serta menghapus token verifikasi di database ketika data tersebut dinyatakan valid. Alur ini mewakili prosedur pemulihan mandiri dengan verifikasi dua langkah (2FA) berbasis nomor kontak terdaftar.
