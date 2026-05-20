# Rencana Perbaikan Permanen: Fitur Riwayat Nilai Mahasiswa

## 1. Pendahuluan
Dokumen ini merinci rencana perbaikan untuk fitur "Riwayat Nilai" pada role mahasiswa. Masalah utama adalah menu riwayat nilai hilang/kosong jika data nilai (`NilaiHuruf`) belum diinput oleh dosen, padahal mahasiswa seharusnya tetap dapat melihat daftar semester yang telah/sedang ditempuh.

## 2. Analisa Komponen Terkait

### A. Route Analysis
*   **V1 (Blade):** `presensi/mahasiswa/riwayat/{semester_id}` ditangani oleh `NilaiMahasiswaController@riwayat`.
*   **V2 (Inertia):** `v2/mahasiswa/riwayat/{semester_id}` ditangani oleh `V2\Mahasiswa\NilaiController@riwayat`.
*   **Celah Bug:** Inkonsistensi pendefinisian variabel `$semesters` antara `DashboardController`, `NilaiMahasiswaController`, dan `HandleInertiaRequests`.

### B. Controller & Data Source Analysis
*   **Masalah Utama:** Penggunaan `NilaiHuruf::where('mahasiswa_id', ...)->groupBy('semester_id')` sebagai sumber data menu.
*   **Solusi:** Data menu harus bersumber dari tabel `semesters` yang difilter berdasarkan level semester mahasiswa saat ini.
*   **N+1 Problem:** 
    *   Pada sidebar, looping `$semesters` sering memicu query tambahan untuk setiap item. 
    *   Gunakan Eager Loading: `Semester::with(['...'])->get()`.

## 3. Strategi Perbaikan Teknikal

### A. Refactoring Data Sidebar (Shared Data)
Ubah logika pengambilan data semester agar tidak bergantung pada keberadaan nilai:
1.  Ambil semester mahasiswa saat ini dari `mahasiswa->kelas->semester->semester` (misal: Semester 3).
2.  Query tabel `semesters` di mana kolom `semester` <= 3.
3.  Implementasikan ini di `HandleInertiaRequests` (untuk V2) dan pastikan Controller V1 juga menggunakan logika yang sama.

### B. Antisipasi Celah Keamanan
*   Pastikan mahasiswa tidak dapat mengakses `semester_id` yang lebih tinggi dari semester berjalannya melalui manipulasi URL.
*   Tambahkan validasi di `riwayat($semester_id)` untuk mengecek apakah `$semester_id` tersebut valid untuk mahasiswa yang bersangkutan.

## 4. Migrasi Tampilan ke V2 (shadcn-vue)

### A. Component Structure
*   Gunakan komponen `ScrollArea` dari shadcn-vue untuk submenu yang panjang.
*   Implementasikan `Collapsible` untuk menu Riwayat Nilai agar lebih clean.
*   Gunakan `Badge` untuk menandai semester aktif.

### B. View Optimization
*   **Template:** Pindahkan logic Blade `@forelse` ke Vue `v-for`.
*   **Loading State:** Implementasikan skeleton loading saat berpindah antar riwayat semester menggunakan fitur `Link` dari Inertia.

## 5. Langkah-Langkah Implementasi

1.  **Update Shared Data (Inertia):**
    Modifikasi `app/Http/Middleware/HandleInertiaRequests.php` untuk menghitung riwayat semester mahasiswa secara dinamis.
    
2.  **Refactor Controller V2:**
    Update `app/Http/Controllers/V2/Mahasiswa/NilaiController.php` untuk memvalidasi akses semester.

3.  **Frontend Update:**
    Update file Vue sidebar (biasanya di `resources/js/Layouts/...`) untuk menggunakan data `semesters` yang baru.

4.  **Legacy Support (Optional):**
    Update `NilaiMahasiswaController` jika masih ingin mempertahankan versi Blade dengan logic yang sama.

## 6. Validasi & Testing
*   **Test Case 1:** Login sebagai mahasiswa baru (Semester 1) -> Pastikan menu muncul meskipun nilai kosong.
*   **Test Case 2:** Login sebagai mahasiswa Semester 5 -> Pastikan muncul Semester 1 s/d 5.
*   **Test Case 3:** Akses URL semester 6 (ilegal) -> Pastikan sistem me-redirect atau menampilkan error 403.
*   **Performance:** Cek jumlah query menggunakan Laravel Debugbar atau clockwork (Pastikan tidak ada duplikasi query `semesters`).
