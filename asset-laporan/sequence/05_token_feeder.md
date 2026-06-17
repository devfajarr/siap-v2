# Sequence Diagram: Pembaruan Otomatis Token Feeder

```mermaid
sequenceDiagram
    autonumber
    participant Scheduler as Kernel.php (Console)
    participant Cmd as RenewFeederToken.php (Command)
    participant Service as FeederTokenService
    participant API as PDDIKTI Feeder API
    participant DB as Database (settings table)

    Scheduler->>Cmd: Execute Command (siap:renew-feeder-token)
    Cmd->>Service: executeRenewToken()
    Service->>API: HTTP POST: Fetch Session Token (username, password)
    alt API Request Berhasil
        API-->>Service: HTTP Response: Token Success (token string)
        Service->>DB: Update Setting Record (feeder_token, token)
        DB-->>Service: Update Setting Success
        Service-->>Cmd: Return Token Success
        Cmd->>Scheduler: Write CLI Log: Success
    else API Request Gagal / Kredensial Salah
        API-->>Service: HTTP Response: Token Unauthorized
        Service-->>Cmd: Throw System Exception: Sync Gagal
        Cmd->>Scheduler: Write CLI Log: Error
    end
```

Sequence diagram ini menggambarkan alur umum pembaruan token akses otomatis oleh Scheduler, yang berlaku untuk sinkronisasi sesi komunikasi dengan server eksternal PDDIKTI. Scheduler menjalankan perintah terjadwal secara berkala, sistem melakukan permintaan token sesi baru ke API PDDIKTI Feeder menggunakan kredensial terdaftar, lalu mengembalikan pesan pengecualian dan menulis log error jika kredensial salah atau koneksi gagal. Setelah respons API valid dan token berhasil diperoleh, sistem memperbarui nilai token di database, dan akhirnya menulis laporan sukses pada sistem log. Alur ini mewakili proses pemeliharaan sesi integrasi data nasional yang berjalan di latar belakang.
