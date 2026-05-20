# Instruksi Implementasi Fitur Pengajuan PKL Berkelompok (v2)

## 1. Ringkasan Fitur
Fitur ini memungkinkan seorang mahasiswa (sebagai Ketua Kelompok) untuk mengajukan permohonan "Ijin PKL" dan/atau "Ijin Memperoleh Data PKL" dengan menambahkan anggota kelompoknya (partner). Dengan fitur ini, mahasiswa yang ditambahkan sebagai anggota tidak perlu lagi mengajukan permohonan yang sama. Sistem akan merepresentasikan satu permohonan tersebut untuk seluruh anggota kelompok.

## 2. Perubahan Database (Migration)
Dibutuhkan modifikasi pada tabel `permohonan_surats` untuk menyimpan data anggota kelompok.
- **Kolom Baru:** Tambahkan kolom `anggota_tim` dengan tipe data `json` (atau `text` yang berisi array JSON) yang bersifat *nullable*.
- **Data yang Disimpan:** Array of ID mahasiswa (contoh: `[12, 45, 67]`).

**Langkah:**
1.  Buat migration baru: `php artisan make:migration add_anggota_tim_to_permohonan_surats_table`
2.  Isi migration:
    ```php
    public function up()
    {
        Schema::table('permohonan_surats', function (Blueprint $table) {
            $table->json('anggota_tim')->nullable()->after('mahasiswa_id');
        });
    }

    public function down()
    {
        Schema::table('permohonan_surats', function (Blueprint $table) {
            $table->dropColumn('anggota_tim');
        });
    }
    ```
3.  **Update Model:** Pada `App\Models\PermohonanSurat`, tambahkan `$casts` untuk atribut `anggota_tim` menjadi `array` agar Eloquent otomatis men-decode JSON saat diakses.

## 3. Alur Kerja Frontend (Vue 3 & Inertia - shadcn-vue)
File yang terdampak (estimasi): `resources/js/Pages/Mahasiswa/PermohonanSurat/Index.vue` atau `resources/js/Pages/Mahasiswa/PermohonanSurat/Components/FormPengajuan.vue` (tergantung struktur komponen).

1. **Form Pengajuan:**
   - Gunakan komponen Combobox/Select dari `shadcn-vue` yang mendukung pencarian (*searchable*) atau fitur Multi-Select (contoh: `vue-multiselect` atau implementasi `Command` dari shadcn-vue).
   - Fetch data mahasiswa lain secara dinamis (via API endpoint baru jika datanya besar, atau kirimkan via Inertia props jika memungkinkan dan sudah difilter per kelas/prodi).
   - Kirimkan data *partner* ini di dalam state Inertia `useForm` sebagai array `anggota_tim`.
   - Patuhi **Code Style Guide**: Tombol Submit harus menggunakan state `form.processing` untuk efek loading. Modal/Dialog harus memiliki header `#4B49AC`.
   
2. **Tabel Data Permohonan (Data Table):**
   - Modifikasi tabel riwayat permohonan untuk menampilkan badge atau keterangan posisi mahasiswa: **"Ketua"** (jika `mahasiswa_id` == user saat ini) atau **"Anggota"** (jika ID user ada di dalam `anggota_tim`).
   - Sediakan tombol/icon rincian (mata/info) untuk melihat *list* lengkap anggota tim (bisa menggunakan *Tooltip* atau *Dialog* shadcn-vue).
   - **Batasi aksi UI:** Tombol Edit dan Hapus (ikon *pencil* / *trash*) **HANYA** muncul dan dapat diklik oleh Ketua kelompok.

## 4. Alur Kerja Backend (Controller V2)
File yang terdampak: `app/Http/Controllers/V2/Mahasiswa/PermohonanSuratController.php`

1. **Metode `index` (Menampilkan Riwayat):**
   - Modifikasi query pengambilan data agar anggota tim juga dapat melihat permohonan.
   ```php
   $permohonans = PermohonanSurat::where('mahasiswa_id', $user->id)
       ->orWhereJsonContains('anggota_tim', (string)$user->id) // atau sesuai tipe data JSON
       ->with(['mahasiswa', 'mahasiswa.kelas.prodi'])
       ->latest()
       ->get(); 
       // Sesuaikan dengan logic pagination jika menggunakan tabel server-side di V2
   ```
2. **Metode `store` (Menyimpan Pengajuan):**
   - Validasi input `anggota_tim` (contoh: `array|max:3`, di mana elemen adalah ID valid).
   - Simpan form request ke database.
3. **Metode `update` dan `destroy` (Sisi Keamanan/Otorisasi):**
   - Tambahkan pengecekan otorisasi keras: `if ($permohonan->mahasiswa_id !== $user->id) { abort(403); }`. Anggota kelompok tidak diizinkan melakukan modifikasi data backend.

## 5. Alur Kerja Admin & View Cetak Surat (Controller V2 Admin)
File yang terdampak: 
- `app/Http/Controllers/V2/Admin/PermohonanSuratController.php` (metode `cetakDokumen`)
- File Blade PDF (seperti `resources/views/pages/permohonan_surat/ijin_pkl.blade.php` - *Tergantung apakah PDF menggunakan Blade atau sistem lain*)

1. **Controller Admin:**
   - Saat metode untuk cetak dokumen dipanggil, lakukan fetch data relasi `Mahasiswa` berdasarkan array ID yang ada di `anggota_tim`.
   - Sisipkan data *collection* seluruh anggota (Ketua + partner) ke view/service PDF.
2. **View PDF (Blade):**
   - Lakukan refactoring pada format cetakan surat.
   - Ubah dari *single value* menjadi *looping* untuk daftar nama mahasiswa.
   - Pastikan nama Ketua diletakkan di urutan nomor 1.

## 6. Skenario Uji (Test Cases)
1. **Pengajuan (Frontend):** Mahasiswa A memilih Mhs B dan C via komponen UI Multi-Select, menekan tombol Submit (loading state muncul). Data tersimpan sebagai JSON.
2. **Dashboard Anggota:** Login sebagai Mahasiswa B (Inertia view). Cek riwayat permohonan, pastikan baris permohonan PKL muncul dengan status Draf/Proses, berlabel "Anggota", dan tanpa aksi Edit/Hapus.
3. **Notifikasi:** Kaprodi menerima satu kali notifikasi terpusat.
4. **Pencetakan Surat (Admin V2):** Admin mengklik cetak pada permohonan tersebut, PDF harus melooping nama Mahasiswa A, B, dan C.
5. **Validasi Error (Otorisasi):** Mahasiswa B (Anggota) mencoba mengirimkan request `DELETE /v2/mahasiswa/permohonan-surat/{id}` via Postman/Console. Backend harus mengembalikan status `403 Forbidden`.
