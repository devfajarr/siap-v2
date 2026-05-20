# Laporan Pembaharuan: Fitur Riwayat Nilai Mahasiswa (V2)

## 1. Ringkasan Perubahan
Dilakukan perbaikan pada logika penarikan data riwayat semester untuk mahasiswa. Sebelumnya, menu riwayat nilai hanya muncul jika mahasiswa sudah memiliki data di tabel `nilai_hurufs`. Sekarang, riwayat semester ditampilkan secara dinamis berdasarkan semester aktif mahasiswa saat ini, memungkinkan mahasiswa baru untuk melihat menu meskipun nilai belum diinput.

## 2. Detail Teknis Perubahan

### A. Backend (Middleware & Shared Data)
- **File:** `app/Http/Middleware/HandleInertiaRequests.php`
- **Perubahan:** Mengubah query `semesters` untuk role Mahasiswa. Sekarang menggunakan relasi `Mahasiswa -> Kelas -> Semester` untuk menentukan level semester saat ini, kemudian mengambil semua data dari tabel `semesters` yang memiliki nilai `semester` lebih kecil atau sama dengan level tersebut.

### B. Backend (Controllers - V2 & V1)
- **Files:** 
    - `app/Http/Controllers/V2/Mahasiswa/NilaiController.php`
    - `app/Http/Controllers/NilaiMahasiswaController.php`
- **Perubahan:** 
    - Implementasi logika penarikan data semester yang sama dengan middleware untuk menjaga konsistensi data.
    - Penambahan **Security Validation** pada metode `riwayat` dan `khs`. Sistem sekarang memvalidasi `semester_id` yang diminta untuk memastikan mahasiswa tidak dapat mengakses data semester yang lebih tinggi dari semester aktif mereka melalui manipulasi URL.

### C. Frontend (Blade & Vue)
- **Blade Sidebar:** `resources/views/partials/_sidebar.blade.php` diperbarui untuk mendukung struktur model `Semester` secara langsung (menggantikan akses via relasi `NilaiHuruf`).
- **Vue Sidebar:** `resources/js/Components/Sidebar.vue` secara otomatis mendukung perubahan ini karena menggunakan data yang dishare melalui Inertia Middleware.

## 3. Dampak Keamanan
- Penutupan celah akses data masa depan (IDOR prevention) pada fitur riwayat nilai dan cetak KHS.

## 4. Cara Pengujian
1. Login sebagai mahasiswa.
2. Pastikan menu "Riwayat Nilai" di sidebar menampilkan daftar semester dari Semester 1 hingga semester aktif saat ini.
3. Coba akses URL riwayat semester di atas semester aktif (misal mahasiswa semester 2 mencoba akses semester 3). Sistem harus melakukan redirect dengan pesan error.
