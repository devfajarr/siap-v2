# Sequence Diagram: Pengajuan Kartu Ujian

```mermaid
sequenceDiagram
    autonumber
    actor Mahasiswa
    participant View as JadwalUjian/Index.vue (Inertia)
    participant Ctrl as JadwalUjianController
    participant Settings as Settings (Model)
    participant Model as PengajuanCetakKartuUjian (Model)
    participant Storage as Storage (Facade)
    participant DB as Database

    Mahasiswa->>View: Select Jenis Ujian & Upload Berkas Pembayaran
    Mahasiswa->>View: Submit Form Kartu Ujian
    View->>Ctrl: POST /v2/mahasiswa/jadwal-ujian/ajukan (payload)
    
    Ctrl->>Settings: where('key', 'buka_kartu_uts/uas')->value('value')
    Settings->>DB: Select Period Settings
    DB-->>Settings: Period Status (bool)
    Settings-->>Ctrl: Period Status
    
    alt Periode Pengajuan Ditutup
        Ctrl-->>View: Tampilkan Exception: Periode Pengajuan Ditutup
        View-->>Mahasiswa: Render Error Alert
    end

    Ctrl->>Ctrl: Execute Validasi: Bukti SPP, Bukti Ujian & Bulan/Tahun SPP
    
    alt Jenis Ujian: UTS
        Ctrl->>Mahasiswa: hasCompletedServiceEvaluations()
        Mahasiswa-->>Ctrl: Status Evaluasi Pelayanan (bool)
        alt Evaluasi Pelayanan Belum Lengkap
            Ctrl-->>View: Tampilkan Exception: Kuesioner Pelayanan Belum Lengkap
            View-->>Mahasiswa: Render Error Alert
        end
    else Jenis Ujian: UAS
        Ctrl->>Mahasiswa: hasCompletedAllTeacherEvaluations()
        Mahasiswa-->>Ctrl: Status Evaluasi Dosen (bool)
        alt Evaluasi Dosen Belum Lengkap
            Ctrl-->>View: Tampilkan Exception: Kuesioner Dosen Belum Lengkap
            View-->>Mahasiswa: Render Error Alert
        end
    end

    Ctrl->>Storage: store('bukti_kartu_ujian', 'public')
    Storage-->>Ctrl: File Paths (buktiSppPath, buktiUjianPath)
    
    Ctrl->>Model: where(existing_request)->first()
    Model->>DB: Query Existing Request
    DB-->>Model: Request Record (or null)
    
    opt Request Record Exists
        Ctrl->>Storage: delete(old_file_paths)
    end

    Ctrl->>Model: updateOrCreate(payload)
    Model->>DB: Insert / Update Record
    DB-->>Model: Save Success

    Ctrl-->>View: Response Success Event
    View-->>Mahasiswa: Render Success Toast
```

Sequence diagram ini menggambarkan alur umum pengajuan cetak kartu ujian oleh Mahasiswa, yang berlaku untuk pelaksanaan ujian tengah semester (UTS) dan ujian akhir semester (UAS). Mahasiswa mengunggah berkas pembayaran serta memilih jenis ujian, sistem melakukan validasi status buka-tutup periode cetak kartu dan memeriksa kelengkapan pengisian kuesioner evaluasi wajib di database, lalu mengembalikan pesan penolakan jika periode ditutup atau kuesioner belum lengkap. Setelah seluruh syarat terpenuhi dan berkas baru berhasil disimpan ke penyimpanan publik, sistem membuat atau memperbarui rekaman pengajuan di database serta menghapus berkas lama jika ada, dan akhirnya menampilkan pesan sukses cetak kartu. Alur ini mewakili pengondisian syarat akademik dan keuangan sebelum masa ujian berlangsung.
