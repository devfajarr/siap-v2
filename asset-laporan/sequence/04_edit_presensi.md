# Sequence Diagram: Pengajuan Edit Presensi Kuliah

```mermaid
sequenceDiagram
    autonumber
    actor Dosen
    participant View as Presensi/Detail.vue
    participant Ctrl as RequestEditPresensiController
    participant ReqModel as RequestEditPresensi (Model)
    participant AbsModel as Absen (Model)
    participant DB as Database
    actor Admin

    Dosen->>View: Akses Halaman Presensi & Select Jadwal
    Dosen->>View: Input Keterangan & Submit Request Edit
    View->>Ctrl: POST /v2/dosen/request-edit-presensi (jadwal_id, keterangan)
    Ctrl->>ReqModel: create(jadwal_id, keterangan, status: pending)
    ReqModel->>DB: Insert Request Record
    DB-->>ReqModel: Insert Success
    Ctrl-->>View: Response Success Event
    View-->>Dosen: Render Success Toast

    Note over Admin, DB: Execute Validasi Request
    Admin->>Ctrl: Execute Approve Request (request_id)
    Ctrl->>ReqModel: findOrFail(request_id)->update(status: approved)
    ReqModel->>DB: Update Request Status: Approved
    DB-->>ReqModel: Update Request Success
    Ctrl->>AbsModel: where(jadwal_id)->update(kehadiran)
    AbsModel->>DB: Update Absen Record (jadwal_id)
    DB-->>AbsModel: Update Absen Success
    Ctrl-->>Admin: Render Success Status
```

Sequence diagram ini menggambarkan alur umum pengajuan perubahan kehadiran oleh Dosen dengan persetujuan Admin, yang berlaku untuk semua koreksi data presensi perkuliahan yang telah berlalu. Dosen mengisi data perubahan presensi serta mengirimkan permohonan edit, sistem menyimpan data pengajuan dengan status tertunda (pending) ke database, dan mengembalikan pesan sukses setelah pengajuan berhasil dikirim. Setelah admin melakukan verifikasi dan menyetujui permohonan tersebut, sistem memperbarui status pengajuan menjadi disetujui, memperbarui data kehadiran mahasiswa pada jadwal terkait di database, dan akhirnya menampilkan status keberhasilan kepada admin. Alur ini mewakili koordinasi dua tingkat dalam pengelolaan akurasi data absensi kuliah.
