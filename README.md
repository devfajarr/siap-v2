# Dokumentasi Integrasi Web Service Neo Feeder (Dashboard V2)

Repositori ini telah dilengkapi dengan integrasi ke **Web Service Neo Feeder PDDIKTI** menggunakan arsitektur berbasis Service di Laravel. Dokumentasi ini menjelaskan cara kerja, konfigurasi, serta panduan penggunaan service untuk melakukan request token dan aksi-aksi (ACT) lainnya.

---

## 1. Konfigurasi Kredensial

Kredensial API dikelola melalui environment variable di file `.env` dan dibaca melalui file konfigurasi `config/services.php` agar tetap aman dan fleksibel.

### File `.env`
Tambahkan konfigurasi berikut ke file `.env` Anda (gunakan kredensial resmi/sandbox Anda):
```env
FEEDER_API_URL=http://localhost/ws/sandbox2.php
FEEDER_USERNAME=xxxxx
FEEDER_PASSWORD=xxxxxxxx
```


### File `config/services.php`
Akses konfigurasi di dalam aplikasi menggunakan:
```php
config('services.feeder.base_url')
config('services.feeder.username')
config('services.feeder.password')
```

---

## 2. Arsitektur & Kelas Utama

### A. Service Token & Request: `App\Services\FeederTokenService`
Class ini bertindak sebagai service sentral untuk mengelola otentikasi dan request ke API Neo Feeder.
* **Lokasi File**: `app/Services/FeederTokenService.php`
* **Fitur Utama**:
  * **Penyimpanan Token (Caching)**: Token disimpan di Cache Laravel selama sisa hari berjalan (TTL dinamis hingga pukul `23:59:59` hari tersebut).
  * **Auto-Fallback**: Jika token di cache kedaluwarsa, service akan otomatis menembak API `GetToken` untuk memperbaruinya.
  * **Manajemen ACT**: Menyediakan wrapper dinamis untuk mempermudah request ACT (seperti `GetDictionary`, dll).
  * **Sinkronisasi Data Prodi (Pull)**: Menarik data program studi (`GetProdi`), mencocokkan record dengan database lokal berdasarkan `kode_prodi` atau `nama_prodi` (untuk merelasikan data lama secara aman), dan mengisi field wajib `NOT NULL` dengan generator fallback.
  * **Sinkronisasi Data Mahasiswa (Pull)**: Menarik data riwayat pendidikan (`GetListRiwayatPendidikanMahasiswa`) dan biodata lengkap (`GetBiodataMahasiswa`) mahasiswa serta menyimpan/memperbaruinya ke database lokal (mencocokkan prodi menggunakan `feeder_id_prodi`).
  * **Rate Limiting & Ketahanan**: Menggunakan jeda throttling (`usleep`) di antara request pagination dan retries HTTP (`Http::retry`) untuk memastikan sinkronisasi yang andal dan aman bagi performa server.

### B. Artisan Command: `feeder:renew-token`
Perintah konsol untuk memaksa bypass cache lama dan memperbarui token baru secara langsung ke server API.
* **Lokasi File**: `app/Console/Commands/RenewFeederToken.php`
* **Cara Menjalankan Manual**:
  ```bash
  php artisan feeder:renew-token
  ```

### C. Artisan Command: `feeder:dictionary`
Perintah konsol untuk melihat struktur request/response suatu fungsi di Neo Feeder untuk mempermudah pengembangan aksi (ACT) lainnya.
* **Lokasi File**: `app/Console/Commands/GetFeederDictionary.php`
* **Cara Menjalankan Manual**:
  ```bash
  php artisan feeder:dictionary [NamaFungsi]
  ```
* **Contoh**:
  ```bash
  php artisan feeder:dictionary GetPeriode
  ```

### D. Artisan Command: `feeder:pull-mahasiswas`
Perintah konsol untuk menarik/sinkronisasi data mahasiswa dari Neo Feeder ke database lokal dengan rate limiting/throttling.
* **Lokasi File**: `app/Console/Commands/PullFeederMahasiswas.php`
* **Opsi Parameter**:
  * `--all` : Menarik seluruh data mahasiswa secara sekuensial menggunakan jeda throttling (`FEEDER_SYNC_DELAY_MS`).
  * `--limit=N` : Jumlah record mahasiswa yang ditarik dalam satu batch (default: `100`).
  * `--offset=N` : Indeks mulai untuk penarikan batch (default: `0`).
  * `--create-new` : Buat record mahasiswa baru secara lokal jika NIM tidak ditemukan di database lokal (biodata ditarik otomatis via `GetBiodataMahasiswa` dan tanggal lahir otomatis dinormalisasi ke `YYYY-MM-DD`).
* **Cara Menjalankan Manual**:
  ```bash
  # Menarik 5 data mahasiswa batch pertama
  php artisan feeder:pull-mahasiswas --limit=5

  # Menarik 5 data mahasiswa batch pertama dan membuat data lokal baru jika tidak ada
  php artisan feeder:pull-mahasiswas --limit=5 --create-new

  # Menarik seluruh data mahasiswa dari feeder secara sekuensial dengan rate limit
  php artisan feeder:pull-mahasiswas --all --create-new
  ```


### E. Artisan Command: `feeder:pull-prodis`
Perintah konsol untuk menarik/sinkronisasi data Program Studi dari Neo Feeder ke database lokal dengan rate limiting/throttling.
* **Lokasi File**: `app/Console/Commands/PullFeederProdis.php`
* **Opsi Parameter**:
  * `--all` : Menarik seluruh data Program Studi secara sekuensial menggunakan jeda throttling (`FEEDER_SYNC_DELAY_MS`).
  * `--limit=N` : Jumlah record Program Studi yang ditarik dalam satu batch (default: `100`).
  * `--offset=N` : Indeks mulai untuk penarikan batch (default: `0`).
* **Cara Menjalankan Manual**:
  ```bash
  # Menarik 5 data prodi batch pertama
  php artisan feeder:pull-prodis --limit=5

  # Menarik seluruh data prodi secara sekuensial dengan rate limit
  php artisan feeder:pull-prodis --all
  ```

### F. Auto-Renew Scheduler
Command `feeder:renew-token` telah didaftarkan pada Laravel Task Scheduler untuk berjalan secara otomatis setiap hari pada pukul **00:00**.
* **Lokasi Registrasi**: `app/Console/Kernel.php`
* **Sintaks**:
  ```php
  $schedule->command('feeder:renew-token')->daily();
  ```


---

## 3. Contoh Penggunaan di Controller / Job

Untuk menggunakan service ini, Anda cukup melakukan Dependency Injection pada Controller, Job, atau memanggilnya secara langsung.

### A. Mendapatkan Token (Otomatis dari Cache / API)
```php
use App\Services\FeederTokenService;

class FeederController extends Controller
{
    public function __construct(protected FeederTokenService $feederService)
    {
    }

    public function checkToken()
    {
        // Mendapatkan token aktif (mengambil dari cache jika ada, jika tidak, fetch baru)
        $token = $this->feederService->getToken();
        
        return response()->json([
            'token' => $token
        ]);
    }
}
```

### B. Melakukan Request ACT Lain (Contoh: `GetDictionary`)
Aksi `GetDictionary` memerlukan token valid. Service akan menangani penyediaan token tersebut secara otomatis di belakang layar.
```php
use App\Services\FeederTokenService;

class FeederController extends Controller
{
    public function showDictionary(FeederTokenService $feederService)
    {
        // Memanggil ACT GetDictionary untuk fungsi GetPeriode
        $result = $feederService->getDictionary('GetPeriode');

        if ($result['success']) {
            $dataSchema = $result['data'];
            return response()->json($dataSchema);
        }

        return response()->json([
            'error' => $result['message']
        ], 500);
    }
}
```

### C. Kerangka Kerja untuk ACT Baru di Masa Depan
Anda dapat dengan mudah menambahkan method baru di dalam `FeederTokenService` untuk melayani aksi (ACT) lainnya menggunakan pola yang sama:

```php
/**
 * Contoh penambahan ACT baru (misal: GetPeriode)
 */
public function getPeriode(string $filter = '', int $limit = 10, int $offset = 0): array
{
    $token = $this->getToken();
    
    if (!$token) {
        return ['success' => false, 'message' => 'Token tidak valid'];
    }

    try {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post(config('services.feeder.base_url'), [
                'act' => 'GetPeriode',
                'token' => $token,
                'filter' => $filter,
                'limit' => $limit,
                'offset' => $offset
            ]);

        return [
            'success' => $response->successful(),
            'data' => $response->json()
        ];
    } catch (\Throwable $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
```

---

## 4. Menjalankan Unit & Feature Test

Untuk memastikan seluruh logika token, caching, penanganan error, dan Artisan Command berjalan dengan baik, jalankan perintah pengujian berikut:

```bash
php artisan test --filter=FeederTokenServiceTest
```
Semua test menggunakan `Http::fake()` sehingga tidak akan melakukan koneksi jaringan riil (aman dijalankan tanpa internet/koneksi API target).
