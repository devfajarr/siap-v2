# Plan Implementasi Fitur Pengajuan PKL Berkelompok (v2)

## 1. Ringkasan Fitur
Fitur ini memungkinkan seorang mahasiswa (sebagai Ketua Kelompok) untuk mengajukan permohonan "Ijin PKL" dan/atau "Ijin Memperoleh Data PKL" dengan menambahkan anggota kelompoknya (partner). Dengan fitur ini, mahasiswa yang ditambahkan sebagai anggota tidak perlu lagi mengajukan permohonan yang sama. Sistem akan merepresentasikan satu permohonan tersebut untuk seluruh anggota kelompok.

## 2. Perubahan Database (Migration)
Dibutuhkan modifikasi pada tabel `permohonan_surats` untuk menyimpan data anggota kelompok.
- **Kolom Baru:** Tambahkan kolom `anggota_tim` dengan tipe data `json` (atau `text` yang berisi array JSON) yang bersifat *nullable*.
- **Data yang Disimpan:** Array of ID mahasiswa (contoh: `[12, 45, 67]`).

**Langkah:**
Buat migration baru: `php artisan make:migration add_anggota_tim_to_permohonan_surats_table`
```php
public function up()
{
    Schema::table('permohonan_surats', function (Blueprint $table) {
        $table->json('anggota_tim')->nullable()->after('mahasiswa_id');
    });
}
```
*Note: Update Model `PermohonanSurat` untuk menambahkan `$casts` atribut `anggota_tim` menjadi `array`.*

## 3. Alur Kerja Frontend (View)
File yang terdampak: `resources/views/pages/mahasiswa/permohonan_surat/index.blade.php`

1. **Form Pengajuan:**
   - Pada form dengan id `ijinPKL` (dan opsi lain jika relevan seperti `memperolehDataPKL`), tambahkan input dinamis (Select2 disarankan) untuk mencari dan memilih mahasiswa lain sebagai partner.
   - Kirimkan data ini dalam bentuk array `anggota_tim[]`.
   - Hanya tampilkan mahasiswa dari program studi/kelas yang sama (jika aturan bisnis membatasi).
2. **Tabel Data Permohonan:**
   - Tambahkan kolom/informasi di tabel riwayat permohonan yang menunjukkan apakah mahasiswa tersebut adalah **Ketua** (pengaju asli) atau **Anggota**.
   - Tampilkan nama-nama anggota tim pada rincian (modal `Lihat`).
   - Batasi akses: Hanya "Ketua" yang dapat melakukan **Edit** atau **Hapus** permohonan (sebelum divalidasi Kaprodi).

## 4. Alur Kerja Backend (Controller)
File yang terdampak: `app/Http/Controllers/PermohonanSuratController.php`

1. **Metode `index` (Menampilkan Riwayat):**
   - Modifikasi query pengambilan data permohonan agar mahasiswa yang ditambahkan sebagai anggota tetap bisa melihat surat tersebut di dashboard-nya.
   ```php
   $permohonans = PermohonanSurat::where('mahasiswa_id', $this->userId)
       ->orWhereJsonContains('anggota_tim', (string)$this->userId)
       ->with('mahasiswa', 'mahasiswa.kelas.prodi')
       ->latest()
       ->paginate(5);
   ```
2. **Metode `store` (Menyimpan Pengajuan):**
   - Validasi input `anggota_tim` (pastikan ID valid dan tidak melebihi batas maksimal anggota kelompok, misal max 3).
   - Simpan data `anggota_tim` (array) ke database bersama data pengajuan.
3. **Metode `update` dan `delete`:**
   - Pastikan ada pengecekan otorisasi: hanya mahasiswa dengan ID yang sama dengan `mahasiswa_id` (Ketua) yang boleh mengupdate atau menghapus permohonan tersebut. Anggota hanya bisa melihat.
4. **Metode `cetak` (Cetak Surat oleh Admin/Akademik):**
   - Controller perlu menyiapkan relasi data `Mahasiswa` untuk seluruh ID yang ada di dalam `anggota_tim`.
   - Mengirimkan koleksi data seluruh anggota ke view cetak.

## 5. Alur Kerja View Cetak Surat
File yang terdampak: `resources/views/pages/permohonan_surat/ijin_pkl.blade.php` (dan template terkait)

1. **Modifikasi Tabel/Daftar Nama:**
   - Ubah dari menampilkan 1 nama mahasiswa secara statis menjadi *looping* array data anggota.
   - Mahasiswa pengaju (`mahasiswa_id`) ditampilkan di urutan pertama (sebagai Ketua).
   - Diikuti oleh data anggota yang ditarik berdasarkan ID di `anggota_tim`.

## 6. Skenario Uji (Test Cases)
1. **Pengajuan:** Mahasiswa A mengajukan PKL dengan anggota Mahasiswa B dan C. Verifikasi data `anggota_tim` masuk sebagai JSON di DB.
2. **Dashboard Anggota:** Login sebagai Mahasiswa B. Pastikan permohonan PKL yang diajukan Mahasiswa A muncul dengan status Draf/Proses, dan Mahasiswa B tidak bisa menghapusnya.
3. **Notifikasi:** Kaprodi menerima satu kali notifikasi untuk permohonan kelompok.
4. **Pencetakan Surat:** Admin mencetak surat, pastikan PDF/halaman cetak menampilkan nama Mahasiswa A, B, dan C.
5. **Validasi Error:** Coba masukkan ID mahasiswa yang tidak valid atau mahasiswa dari luar prodi (jika dilarang) sebagai anggota tim. Sistem harus menolak.
