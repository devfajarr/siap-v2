# SIAP — Sistem Informasi Akademik Politeknik

> Platform manajemen akademik berbasis web untuk mendukung seluruh proses perkuliahan, mulai dari pengelolaan data master, presensi, penilaian, KRS, hingga pelaporan dan kuisioner AMI.

---

## 📋 Tentang Aplikasi

**SIAP** adalah Sistem Informasi Akademik Politeknik yang dibangun untuk memudahkan pengelolaan administrasi dan kegiatan akademik secara digital. Sistem ini mencakup seluruh siklus perkuliahan — dari penjadwalan dan presensi harian, pengisian nilai, pengajuan KRS dan pembayaran, hingga monitoring dan pelaporan oleh pimpinan.

Sistem ini dirancang untuk mendukung **10+ peran pengguna** yang masing-masing memiliki dashboard, hak akses, dan alur kerja tersendiri.

---

## 🚀 Fitur Utama

### 🔐 Autentikasi Multi-Peran
- Login terpisah untuk setiap peran (Admin, Mahasiswa, Dosen, Kaprodi, Wakil Direktur, Direktur, Pegawai, Orang Tua, dan jabatan struktural seperti BPMI, Kemahasiswaan, Perpustakaan, Sarpras, Personalia)
- Sistem **first-login** yang memaksa pengguna mengubah password awal
- Lupa password dengan verifikasi melalui WhatsApp OTP
- Dukungan **multi-jabatan**: satu akun dapat memegang beberapa jabatan sekaligus (misalnya, Dosen yang juga BPMI)

### 🏫 Manajemen Data Master (Admin)
- Program Studi, Semester, Tahun Akademik, Kelas, dan Ruangan
- Data Dosen, Kaprodi, Wakil Direktur, Direktur, dan Pegawai
- Mata Kuliah (Matkul) per program studi dan semester
- Jabatan Struktural

### 📅 Penjadwalan
- **Jadwal Mengajar**: penugasan dosen ke kelas per hari dan ruangan
- **Jadwal Ujian**: penjadwalan UTS dan UAS beserta pengawas (dosen/pegawai polimorfik)

### ✅ Presensi & Kontrak Kuliah
- Input presensi oleh dosen per pertemuan (hadir, telat, absen, sakit, izin)
- Presensi maksimal 14 pertemuan per semester
- **Kontrak Kuliah**: kesepakatan antara dosen dan mahasiswa di awal semester
- Pengajuan edit presensi yang memerlukan persetujuan admin

### 📊 Penilaian Akademik
- Input nilai per komponen: Tugas, UTS, UAS, Etika, dan Keaktifan
- Rekap Nilai otomatis dengan konversi ke huruf mutu
- KHS (Kartu Hasil Studi) per semester
- Riwayat nilai per semester yang dapat dilihat mahasiswa dan orang tua

### 📝 KRS & Pembayaran
- Pengisian KRS (Kartu Rencana Studi) oleh mahasiswa
- Validasi dan persetujuan KRS oleh Dosen Pembimbing Akademik (DPA)
- Pencatatan pembayaran SPP dan status pembayaran
- Cetak KRS dan KHS dalam format PDF

### 📄 Permohonan Surat
- Mahasiswa dapat mengajukan berbagai jenis surat (SKM, surat aktif kuliah, dll.)
- Anggota tim dapat ditambahkan ke permohonan surat
- Proses persetujuan dan pencetakan surat oleh admin
- Riwayat surat yang sudah selesai

### 📋 Pengajuan & Rekap Dokumen
- **Rekap Presensi**: pengajuan dan persetujuan rekapitulasi kehadiran
- **Rekap Berita Acara**: dokumentasi kegiatan perkuliahan
- **Rekap Kontrak Kuliah**: dokumentasi kontrak kuliah yang telah disetujui
- **Pengajuan Cetak KHS & Kartu Ujian**: mahasiswa dapat meminta cetak dokumen fisik

### 🎓 Bimbingan Mahasiswa (DPA)
- Fitur konsultasi/bimbingan antara mahasiswa dan Dosen Pembimbing Akademik
- Notifikasi pesan yang belum dibaca secara real-time di sidebar
- Polling otomatis setiap 30 detik untuk memperbarui jumlah pesan baru

### 👨‍👩‍👧 Portal Orang Tua
- Orang tua dapat memantau absensi, nilai KHS, KRS, dan keuangan anak
- Dukungan multi-anak: orang tua dengan lebih dari satu anak dapat berpindah antar profil anak
- Verifikasi kontak WhatsApp untuk keamanan akun

### 📊 Monitoring & Pelaporan (Kaprodi, Wadir, Direktur)
- Monitoring perkuliahan real-time per program studi
- Monitoring nilai dan rekap hasil studi
- Rekap presensi, berita acara, dan kontrak kuliah
- Persetujuan multi-level (Dosen → Kaprodi → Wadir/Direktur)

### 📝 Sistem Kuisioner (AMI & Pelayanan)
- Tiga kategori kuisioner: **Kuis Pelayanan**, **Kinerja Pengajar**, dan **Kuisioner AMI**
- Pembuatan kuisioner dinamis dengan seksi dan berbagai tipe pertanyaan (radio, select, checkbox, teks bebas)
- Halaman pengisian untuk responden (mahasiswa, dosen, pegawai)
- **Analitik respons**: visualisasi hasil per pertanyaan dengan persentase jawaban
- Filter analitik per dosen untuk kuisioner Kinerja Pengajar
- Ekspor hasil kuisioner ke **Excel/CSV**
- Dikelola oleh BPMI (dapat diakses juga oleh dosen/pegawai berperan sebagai BPMI)

### 🔔 Notifikasi Real-time
- Notifikasi in-app menggunakan **Laravel Reverb** (WebSocket)
- Notifikasi pengiriman jadwal mengajar melalui **WhatsApp**
- Background jobs untuk pengiriman notifikasi (queue-based)

### 💬 Pesan Internal
- Sistem pesan antar pengguna (dosen ↔ mahasiswa)
- Mendukung reply/thread pesan

---

## 🏗️ Arsitektur & Teknologi

### Backend
| Teknologi | Versi | Keterangan |
|---|---|---|
| PHP | 8.3 | Runtime utama |
| Laravel | 10 | Framework backend |
| Inertia.js (Laravel) | 2.x | Koneksi server-client SPA |
| Laravel Reverb | 1.x | WebSocket server untuk real-time |
| Laravel Sanctum | 3.x | Autentikasi API |
| Maatwebsite Excel | 3.x | Export Excel/CSV |
| Tighten Ziggy | 2.x | Named route di frontend |

### Frontend
| Teknologi | Versi | Keterangan |
|---|---|---|
| Vue.js | 3.x | Framework UI |
| Inertia.js (Vue3) | 1.x | SPA tanpa API terpisah |
| Tailwind CSS | 3.x | Utility-first CSS |
| shadcn-vue | 2.x | Komponen UI siap pakai |
| Vite | 5.x | Bundler & dev server |
| Lucide Vue Next | 0.244+ | Library ikon |
| VueUse | 14.x | Composable Vue utilities |

### Database
- **MySQL** — Database relasional utama
- 70+ tabel migrasi mencakup seluruh entitas akademik

### Infrastructure
- Queue Jobs untuk notifikasi WhatsApp dan pengiriman jadwal
- WebSocket (Reverb + Pusher-compatible) untuk fitur real-time
- Storage lokal untuk foto profil mahasiswa

---

## 👥 Peran Pengguna

| Peran | Akses |
|---|---|
| **Admin** | Pengelolaan data master, mahasiswa, jadwal, nilai, pembayaran, KRS, surat, kuisioner |
| **Mahasiswa** | Dashboard, nilai, KRS, jadwal ujian, permohonan surat, bimbingan DPA, kuisioner |
| **Dosen** | Presensi, kontrak kuliah, input nilai, validasi KRS (jika DPA), bimbingan mahasiswa |
| **Kaprodi** | Monitoring perkuliahan, nilai, rekap, persetujuan dokumen, permohonan surat |
| **Wakil Direktur** | Monitoring menyeluruh, persetujuan rekap presensi, kontrak, berita acara |
| **Direktur** | Monitoring tingkat institusi, rekap, pelaporan |
| **Pegawai** | Dashboard pegawai, kuisioner AMI |
| **Orang Tua** | Pantau absensi, nilai, KRS, dan keuangan anak |
| **BPMI** | Manajemen dan analitik kuisioner |
| **Jabatan Struktural** | Kemahasiswaan, Perpustakaan, Sarpras, Personalia — akses kuisioner AMI |

---

## ⚙️ Cara Instalasi

### Prasyarat
- PHP >= 8.1
- Composer
- Node.js >= 18 & npm
- MySQL
- (Opsional) Pusher-compatible server untuk WebSocket

### Langkah Instalasi

```bash
# 1. Clone repositori
git clone <url-repo> siap
cd siap

# 2. Install dependensi PHP
composer install

# 3. Install dependensi Node.js
npm install

# 4. Salin file konfigurasi environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Konfigurasi database di .env
# DB_DATABASE=nama_database
# DB_USERNAME=username
# DB_PASSWORD=password

# 7. Jalankan migrasi database
php artisan migrate

# 8. (Opsional) Jalankan seeder
php artisan db:seed

# 9. Build aset frontend
npm run build

# 10. Jalankan server
php artisan serve
```

### Konfigurasi Environment Penting

```env
# Aplikasi
APP_NAME="SIAP Politeknik"
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_DATABASE=siap

# WebSocket (Real-time)
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret

# WhatsApp Notification
# Konfigurasi sesuai provider WhatsApp gateway yang digunakan

# Broadcasting
BROADCAST_DRIVER=reverb
QUEUE_CONNECTION=database
```

### Menjalankan untuk Development

```bash
# Terminal 1: Frontend dev server
npm run dev

# Terminal 2: Queue worker (untuk notifikasi)
php artisan queue:work

# Terminal 3: WebSocket server
php artisan reverb:start

# Atau gunakan satu perintah (jika dikonfigurasi di composer.json)
composer run dev
```

---

## 📁 Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/          # Controller legacy (Blade-based)
│   └── Controllers/V2/       # Controller V2 (Inertia-based)
│       ├── Admin/
│       ├── Dosen/
│       ├── Kaprodi/
│       ├── Mahasiswa/
│       ├── Direktur/
│       ├── Pegawai/
│       ├── OrangTua/
│       └── Respondent/
├── Models/                   # Eloquent models
├── Jobs/                     # Background jobs (notifikasi)
├── Events/                   # WebSocket events
└── Services/                 # Business logic services

resources/js/
├── Components/               # Komponen Vue yang dapat digunakan ulang
│   ├── Sidebar.vue           # Navigasi sidebar dinamis per peran
│   ├── Navbar.vue
│   ├── NotificationDropdown.vue
│   └── ui/                   # Komponen shadcn-vue
├── Layouts/
│   └── AdminLayout.vue       # Layout utama untuk semua halaman
└── Pages/                    # Halaman Inertia per peran
    ├── Admin/
    ├── Mahasiswa/
    ├── Dosen/
    ├── Kaprodi/
    ├── Direktur/
    ├── Pegawai/
    ├── OrangTua/
    ├── Respondent/
    └── Auth/

routes/
└── web.php                   # Semua route aplikasi (v2/ prefix untuk Inertia)
```

---

## 🎨 Design System

- **Primary Color**: `#4B49AC` (Ungu institusi)
- **Danger Color**: `#FF4747`
- **Font**: Nunito (Google Fonts)
- **Border Radius**: `8px` (rounded-lg)
- **Mode**: Light (dengan dukungan dark mode pada beberapa komponen)
- **Komponen UI**: shadcn-vue dengan Radix Vue sebagai primitif

---

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test --compact

# Jalankan test spesifik
php artisan test --compact tests/Feature/ExampleTest.php

# Jalankan dengan filter nama
php artisan test --compact --filter=namaTest
```

> **Penting**: Selalu gunakan `--env=testing` untuk perintah database saat testing agar tidak memodifikasi database development.

---

## 📜 Lisensi

Sistem ini merupakan perangkat lunak internal. Seluruh hak cipta dilindungi.

---

## 🙏 Kontribusi

Sistem ini dikembangkan dan dikelola oleh tim internal. Untuk pertanyaan atau kontribusi, hubungi tim pengembang.
