# Sequence Diagram: First Login (Force Change Password)

```mermaid
sequenceDiagram
    autonumber
    actor User
    participant View as ForceChangePassword (Inertia View)
    participant Ctrl as ForceChangePasswordController
    participant Model as User/Mahasiswa (Model)
    participant DB as Database
    participant Session as Session (Facade)

    Note over User, Ctrl: Login Berhasil dengan Flag is_first_login = true
    Ctrl-->>View: Redirect: /force-change-password
    View-->>User: Render Halaman ForceChangePassword
    
    User->>View: Input Password Baru & Konfirmasi
    User->>View: Submit Form Password Baru
    View->>Ctrl: POST /v2/force-change-password (password)
    
    Ctrl->>Ctrl: Execute Validasi: Karakter, Angka, Simbol & Minimal 8 Digit
    
    alt Validasi Password Lemah / Tidak Cocok
        Ctrl-->>View: Tampilkan Exception: Validasi Password Lemah
        View-->>User: Render Validation Error
    else Validasi Password Sukses
        Ctrl->>Model: update(['password' => Hash::make, 'is_first_login' => false])
        Model->>DB: Update Password & Flag
        DB-->>Model: Update Success
        
        Ctrl->>Session: regenerate()
        Session-->>Ctrl: Session ID Regenerated
        
        Ctrl->>Ctrl: Resolve redirect route by role
        Ctrl-->>View: Redirect: Dashboard Role (flash success)
        View-->>User: Render Dashboard V2
    end
```

Sequence diagram ini menggambarkan alur umum pergantian password wajib saat login pertama oleh Pengguna baru, yang berlaku bagi semua pengguna yang belum pernah memperbarui kredensial bawaan mereka. Pengguna diarahkan ke halaman khusus setelah login dengan penanda login pertama aktif, sistem melakukan validasi kekuatan kata sandi baru yang dimasukkan, lalu mengembalikan pesan kesalahan jika kata sandi dinilai terlalu lemah atau tidak memenuhi syarat minimal keamanan. Setelah kata sandi baru dinyatakan valid, sistem memperbarui hash sandi serta mematikan bendera login pertama di database, meregenerasi ID sesi untuk keamanan, dan akhirnya mengarahkan pengguna ke dasbor peran masing-masing dengan pesan sukses. Alur ini mewakili pertahanan awal dalam mengamankan hak akses pengguna baru.
