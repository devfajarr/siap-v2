# Sequence Diagram: Pengajuan Surat

```mermaid
sequenceDiagram
    autonumber
    actor Mahasiswa
    participant View as PermohonanSurat/Index.vue (Inertia)
    participant Ctrl as PermohonanSuratController
    participant Model as PermohonanSurat (Model)
    participant DB as Database
    participant Service as WhatsappService

    Mahasiswa->>View: Select Jenis Permohonan & Input Anggota Tim
    Mahasiswa->>View: Input Data Dinamis Surat
    Mahasiswa->>View: Submit Form Pengajuan
    View->>Ctrl: POST /v2/mahasiswa/permohonan-surat (payload)
    Ctrl->>Ctrl: Execute Validasi: Payload & Anggota Tim (Max 5)
    
    alt Tipe Surat: PKL / Observasi / TA
        Ctrl->>Model: whereIn('jenis_permohonan', PKL_types)->where('setuju_kaprodi', '!=', 2)
        Model->>DB: Select Active Submissions
        DB-->>Model: Dataset Active Submissions
        Ctrl->>Ctrl: Cek Partisipasi Mahasiswa & Anggota Tim
        alt Anggota Terdaftar di Kelompok Lain
            Ctrl-->>View: Tampilkan Exception: Data Pengajuan Aktif Lain Terdeteksi
            View-->>Mahasiswa: Render Validation Alert
        end
    end

    Ctrl->>Ctrl: Execute Validasi: Field Dinamis berdasarkan Tipe Surat
    Ctrl->>Model: create(payload)
    Model->>DB: Insert Record PermohonanSurat
    DB-->>Model: Insert Success
    
    opt Pengiriman Notifikasi WhatsApp (app.whatsapp_notification = true)
        Ctrl->>Service: kirim(no_telp_kaprodi, template_message)
        Service->>Service: HTTP POST: Trigger WA API Gateway
    end

    Ctrl-->>View: Response Success Event
    View-->>Mahasiswa: Render Success Toast
```

Sequence diagram ini menggambarkan alur umum pengajuan surat akademik oleh Mahasiswa, yang berlaku untuk beberapa kategori surat seperti PKL, observasi, dan tugas akhir. Mahasiswa mengisi jenis surat beserta data kelompok dan field dinamis lainnya, sistem melakukan validasi batas anggota kelompok dan memeriksa keaktifan pengajuan serupa di database, lalu mengembalikan pesan kesalahan jika ditemukan duplikasi partisipasi aktif anggota kelompok. Setelah seluruh data dinyatakan valid, sistem menyimpan data permohonan baru ke database, memicu pengiriman notifikasi WhatsApp otomatis ke ketua program studi jika fitur diaktifkan, dan akhirnya menampilkan status sukses pengajuan. Alur ini mewakili standardisasi prosedur administrasi surat menyurat mahasiswa.
