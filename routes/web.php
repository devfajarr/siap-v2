<?php

use App\Http\Controllers\AktifController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirekturController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\EtikaController;
use App\Http\Controllers\InformasiLandingPageController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JadwalUjianController;
use App\Http\Controllers\KaprodiController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\KrsPembayaranController;
use App\Http\Controllers\LembarMonitoringController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MatkulController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\NilaiMahasiswaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PemberitahuanController;
use App\Http\Controllers\PengajuanRekapBeritaController;
use App\Http\Controllers\PengajuanRekapkontrakController;
use App\Http\Controllers\PengajuanRekapPresensiController;
use App\Http\Controllers\PermohonanSuratController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekapNilaiController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\UasController;
use App\Http\Controllers\UtsController;
use App\Http\Controllers\WadirController;
use App\Models\InformasiLandingPage;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $brosurs = InformasiLandingPage::where('tipe', 'brosur')->latest()->get();

    return view('index', compact('brosurs'));
})->middleware('guest');

// AUTH
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'processLogin'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/first-login', [AuthController::class, 'processFirstLogin'])
    ->name('first.login')->middleware(['auth:admin,direktur,wakil_direktur,kaprodi,mahasiswa,dosen']);
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware(['auth:admin,direktur,wakil_direktur,kaprodi,mahasiswa,dosen'])->name('change.password');

// DASHBOARD V2
Route::prefix('v2')->group(function () {
    Route::middleware(['auth:dosen'])->group(function () {
        Route::get('/dosen/kontrak', [App\Http\Controllers\V2\Dosen\KontrakController::class, 'index'])->name('v2.dosen.kontrak.index');
        Route::get('/dosen/kontrak/create/{id}', [App\Http\Controllers\V2\Dosen\KontrakController::class, 'create'])->name('v2.dosen.kontrak.create');
        Route::post('/dosen/kontrak', [App\Http\Controllers\V2\Dosen\KontrakController::class, 'store'])->name('v2.dosen.kontrak.store');
        Route::get('/dosen/kontrak/edit/{id}', [App\Http\Controllers\V2\Dosen\KontrakController::class, 'edit'])->name('v2.dosen.kontrak.edit');
        Route::put('/dosen/kontrak/update/{id}', [App\Http\Controllers\V2\Dosen\KontrakController::class, 'update'])->name('v2.dosen.kontrak.update');
        Route::post('/dosen/kontrak/import', [App\Http\Controllers\V2\Dosen\KontrakController::class, 'importWithReplace'])->name('import.kontrak');
        Route::get('/dosen/kontrak/download-template', [App\Http\Controllers\V2\Dosen\KontrakController::class, 'downloadFormat'])->name('download.format.kontrak');
    });

    Route::get('/dosen/kontrak/rekap/{matkul_id}/{kelas_id}/{jadwal_id}', [App\Http\Controllers\V2\Dosen\KontrakController::class, 'rekap'])
        ->name('v2.dosen.kontrak.rekap')
        ->middleware('auth:dosen,wakil_direktur,kaprodi,admin,direktur');
});

Route::prefix('/presensi')->group(function () {
    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth:admin,mahasiswa,direktur,wakil_direktur,dosen,kaprodi');

    // DATA MASTER
    Route::prefix('/data-master')->middleware('auth:admin')->group(function () {
        Route::resource('/data-kelas', KelasController::class)->except(['show']);
        Route::get('/data-matkul/search', [MatkulController::class, 'search'])->name('data-matkul.search');
        Route::resource('/data-matkul', MatkulController::class)->except(['show']);
        Route::resource('/data-prodi', ProdiController::class)->except(['show']);
        Route::resource('/data-semester', SemesterController::class)->except(['show']);
        Route::put('/status', [SemesterController::class, 'gantiStatus'])->name('status.update');
        Route::resource('/data-ruangan', RuanganController::class)->except(['show']);
        Route::resource('/data-tahun-akademik', TahunAkademikController::class)->except(['show']);
        Route::resource('/data-direktur', DirekturController::class)->except(['show']);
        Route::resource('/data-dosen', DosenController::class)->except(['show']);
        Route::get('/data-dosen-export', [DosenController::class, 'export'])->name('dosen.export');
        Route::post('/data-dosen-import', [DosenController::class, 'import'])->name('data-dosen-import');
        Route::get('/data-dosen-download-format', [DosenController::class, 'downloadFormat'])->name('download.format.dosen');
        Route::resource('/data-pegawai', PegawaiController::class)->except(['show']);
        Route::get('/data-pegawai-export', [PegawaiController::class, 'export'])->name('pegawai.export');
        Route::post('/data-pegawai-import', [PegawaiController::class, 'import'])->name('pegawai.import');
        Route::get('/data-pegawai-download-format', [PegawaiController::class, 'downloadFormat'])->name('download.format.pegawai');
        Route::resource('/data-kaprodi', KaprodiController::class)->except(['show']);
        Route::resource('/data-wadir', WadirController::class)->except(['show']);
    });

    // MAHASISWA
    Route::get('/data-mahasiswa/export', [MahasiswaController::class, 'export'])
        ->name('data-mahasiswa-export-all')
        ->middleware('auth:admin');
    Route::resource('/data-mahasiswa', MahasiswaController::class)->except(['show'])->middleware('auth:admin');
    Route::get('/data-mahasiswa/{id}', [MahasiswaController::class, 'kelas'])->middleware('auth:admin');
    Route::post('/data-mahasiswa/move', [MahasiswaController::class, 'pindahKelas'])->middleware('auth:admin');
    Route::post('/data-mahasiswa/delete', [MahasiswaController::class, 'deleteCheck'])->middleware('auth:admin');
    Route::get('/presensi/data-mahasiswa/search', [MahasiswaController::class, 'search'])->name('data-mahasiswa.search')->middleware('auth:admin');
    Route::post('/data-mahasiswa/import', [MahasiswaController::class, 'import'])->name('data-mahasiswa-import')->middleware('auth:admin');
    Route::get('/data-mahasiswa/export/{id}', [MahasiswaController::class, 'exportSetiapKelas'])
        ->name('data-mahasiswa-export')
        ->middleware('auth:admin');

    // JADWAL
    Route::resource('/jadwal-mengajar', JadwalController::class)->middleware('auth:admin')->except(['show']);
    Route::get('/jadwal-mengajar/search', [JadwalController::class, 'search'])
        ->name('jadwal.search')
        ->middleware('auth:admin');

    // EDIT PRESENSI
    Route::get('/data-presensi/ajukan-edit', [PresensiController::class, 'requestEdit'])->name('request.edit.index')->middleware('auth:admin');
    Route::put('/data-presensi/ajukan-edit', [PresensiController::class, 'requestEditVerify'])->name('request.edit.verify');

    // PRESENSI
    Route::resource('/data-presensi', PresensiController::class)->middleware('auth:dosen');
    Route::get('/data-presensi/isi-presensi/{id}', [PresensiController::class, 'absen'])->middleware('auth:dosen');
    Route::get('/data-presensi/edit/{id}/{matkuls_id}/{kelas_id}/{jadwal_id}', [PresensiController::class, 'edit'])->middleware('auth:dosen');
    Route::get('/data-presensi/rekap/1-7/{matkuls_id}/{kelas_id}/{jadwal_id}', [PresensiController::class, 'rekap1to7'])->middleware('auth:dosen,kaprodi,admin,wakil_direktur,direktur');
    Route::get('/data-presensi/rekap/8-14/{matkuls_id}/{kelas_id}/{jadwal_id}', [PresensiController::class, 'rekap8to14'])->middleware('auth:dosen,kaprodi,admin,wakil_direktur,direktur');
    Route::get('/data-presensi/get-pertemuan/{id}', [PresensiController::class, 'getPertemuan'])->name('presensi.get.pertemuan')->middleware('auth:dosen');
    Route::post('data-presensi/ajukan-edit/store', [PresensiController::class, 'storeRequestEdit'])->name('store.ajukan.edit')->middleware('auth:dosen');
    Route::get('/data-presensi/edit/pertemuan/{id}/{matkuls_id}/{kelas_id}/{jadwal_id}', [PresensiController::class, 'editPertemuan'])->middleware('auth:dosen');
    Route::put('/data-presensi/update/pertemuan', [PresensiController::class, 'updatePertemuan'])->name('update.pertemuan.presensi')->middleware('auth:dosen');

    // BERITA ACARA
    Route::get('/data-presensi/rekap/berita-acara-perkuliahan/1-7/{matkuls_id}/{kelas_id}/{jadwal_id}', [PresensiController::class, 'berita1to7'])->middleware('auth:dosen,kaprodi,admin,wakil_direktur,direktur');
    Route::get('/data-presensi/rekap/berita-acara-perkuliahan/8-14/{matkuls_id}/{kelas_id}/{jadwal_id}', [PresensiController::class, 'berita8to14'])->middleware('auth:dosen,kaprodi,admin,wakil_direktur,direktur');

    // KONTRAK
    Route::resource('/data-kontrak', KontrakController::class)->middleware('auth:dosen')->except(['show']);
    Route::get('/data-kontrak/isi-kontrak/{id}', [KontrakController::class, 'create'])->middleware('auth:dosen');
    Route::post('/data-kontrak/import', [KontrakController::class, 'importWithReplace'])->name('import.kontrak.legacy')->middleware('auth:dosen');
    Route::get('/data-kontrak/download-template', [KontrakController::class, 'downloadFormat'])->name('download.format.kontrak.legacy')->middleware('auth:dosen');
    Route::get('/data-kontrak/rekap/{matkuls_id}/{kelas_id}/{jadwals_id}', [KontrakController::class, 'rekap'])->middleware('auth:dosen,wakil_direktur,kaprodi,admin,direktur');

    // PENGAJUAN PRESENSI
    Route::prefix('/pengajuan-konfirmasi')->middleware('auth:kaprodi,wakil_direktur,direktur,dosen')->group(function () {
        Route::resource('/rekap-presensi', PengajuanRekapPresensiController::class);
        Route::get('/presensi-disetujui', [PengajuanRekapPresensiController::class, 'confirm'])->middleware('auth:kaprodi,wakil_direktur');
        Route::get('/rekap-presensi/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [PengajuanRekapPresensiController::class, 'edit']);
        Route::put('/rekap-presensi/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [PengajuanRekapPresensiController::class, 'update']);

        // PENGAJUAN BERITA
        Route::resource('/rekap-berita', PengajuanRekapBeritaController::class);
        Route::get('/rekap-berita/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [PengajuanRekapBeritaController::class, 'edit']);
        Route::put('/rekap-berita/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [PengajuanRekapBeritaController::class, 'update']);
        Route::get('/berita-disetujui', [PengajuanRekapBeritaController::class, 'confirm']);

        // PENGAJUAN KONTRAK
        Route::resource('/rekap-kontrak', PengajuanRekapkontrakController::class);
        Route::get('/rekap-kontrak/{jadwal_id}/{matkul_id}/{kelas_id}', [PengajuanRekapkontrakController::class, 'edit']);
        Route::put('/rekap-kontrak/{jadwal_id}/{matkul_id}/{kelas_id}', [PengajuanRekapkontrakController::class, 'update']);
        Route::get('/kontrak-disetujui', [PengajuanRekapkontrakController::class, 'confirm']);
    });

    Route::prefix('/data-nilai')->middleware('auth:dosen,admin')->group(function () {
        // NILAI
        Route::get('/{kelas_id}', [NilaiController::class, 'index']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/detail', [NilaiController::class, 'detail']);

        // TUGAS
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/tugas', [TugasController::class, 'index']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/tugas/create', [TugasController::class, 'create']);
        Route::post('/{kelas_id}/{matkul_id}/{jadwal_id}/tugas/', [TugasController::class, 'store']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/tugas/{tugas_ke}/edit', [TugasController::class, 'edit'])->name('tugas.edit');
        Route::put('/{kelas_id}/{matkul_id}/{jadwal_id}/tugas/{tugas_ke}', [TugasController::class, 'update'])->name('tugas.update');
        Route::delete('/{kelas_id}/{matkul_id}/{jadwal_id}/tugas/{tugas_ke}/delete', [TugasController::class, 'destroy']);

        // UAS
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/uas', [UasController::class, 'index']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/uas/create', [UasController::class, 'create']);
        Route::Post('/{kelas_id}/{matkul_id}/{jadwal_id}/uas', [UasController::class, 'store']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/uas/edit', [UasController::class, 'edit']);
        Route::put('/{kelas_id}/{matkul_id}/{jadwal_id}/uas', [UasController::class, 'update']);
        Route::delete('/{kelas_id}/{matkul_id}/{jadwal_id}/uas/', [UasController::class, 'destroy']);

        // UTS
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/uts', [UtsController::class, 'index']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/uts/create', [UtsController::class, 'create']);
        Route::Post('/{kelas_id}/{matkul_id}/{jadwal_id}/uts', [UtsController::class, 'store']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/uts/edit', [UtsController::class, 'edit']);
        Route::put('/{kelas_id}/{matkul_id}/{jadwal_id}/uts', [UtsController::class, 'update']);
        Route::delete('/{kelas_id}/{matkul_id}/{jadwal_id}/uts', [UtsController::class, 'destroy']);

        // ETIKA
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/etika', [EtikaController::class, 'index']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/etika/create', [EtikaController::class, 'create']);
        Route::Post('/{kelas_id}/{matkul_id}/{jadwal_id}/etika', [EtikaController::class, 'store']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/etika/edit', [EtikaController::class, 'edit']);
        Route::put('/{kelas_id}/{matkul_id}/{jadwal_id}/etika', [EtikaController::class, 'update']);
        Route::delete('/{kelas_id}/{matkul_id}/{jadwal_id}/etika', [EtikaController::class, 'destroy']);

        // AKTIF
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/aktif', [AktifController::class, 'index']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/aktif/create', [AktifController::class, 'create']);
        Route::Post('/{kelas_id}/{matkul_id}/{jadwal_id}/aktif', [AktifController::class, 'store']);
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/aktif/edit', [AktifController::class, 'edit']);
        Route::put('/{kelas_id}/{matkul_id}/{jadwal_id}/aktif', [AktifController::class, 'update']);
        Route::delete('/{kelas_id}/{matkul_id}/{jadwal_id}/aktif', action: [AktifController::class, 'destroy']);

        // REKAP NILAI
        Route::get('/{kelas_id}/{matkul_id}/{jadwal_id}/rekap', [RekapNilaiController::class, 'index']);
        Route::post('/rekap', [RekapNilaiController::class, 'store']);
        Route::get('/pengajuan/rekap-nilai', [RekapNilaiController::class, 'pengajuan']);
        Route::get('/pengajuan/nilai-disetuju', [RekapNilaiController::class, 'disetujui']);
        Route::get('/pengajuan/rekap-nilai/{kelas_id}/{matkul_id}/{jadwal_id}', [RekapNilaiController::class, 'diajukan']);
        Route::put('/pengajuan/rekap-nilai/{kelas_id}/{matkul_id}/{jadwal_id}', [RekapNilaiController::class, 'update']);
        Route::get('rekap/{kelas_id}/{matkul_id}/{jadwal_id}', [RekapNilaiController::class, 'rekap']);
    });

    // PEMBAYARAN
    Route::prefix('/pembayaran')->middleware('auth:admin')->group(function () {
        Route::get('/diajukan', [KrsPembayaranController::class, 'diajukan']);
        Route::get('/disetujui', [KrsPembayaranController::class, 'disetujui']);
        Route::get('/diajukan/{id}/edit', [KrsPembayaranController::class, 'edit']);
        Route::get('/disetujui/{id}/edit', [KrsPembayaranController::class, 'edit']);
        Route::put('/diajukan/update/{id}', [KrsPembayaranController::class, 'update']);
    });

    // KRS ADMIN
    Route::prefix('/krs')->middleware('auth:admin')->group(function () {
        Route::get('/kategori', [KrsPembayaranController::class, 'showKelas']);
        Route::get('/kategori/{id}', [KrsPembayaranController::class, 'showDetailMhs']);
        // Route::get('/kategori/{id}', [KrsPembayaranController::class, 'showDetailMhs']);
        Route::get('/kategori/cetak/{id}', [KrsPembayaranController::class, 'krsCetakAdmin']);
        Route::get('/export/{id}', [KrsPembayaranController::class, 'exportKrs'])->name('krs.export');
        Route::get('/export-all', [KrsPembayaranController::class, 'exportKrsAll'])->name('krs.export.all');
    });

    // KRS DOSEN
    Route::prefix('/krs')->middleware('auth:dosen')->group(function () {
        Route::get('/diajukan', [KrsPembayaranController::class, 'krsDiajukan']);
        Route::get('/disetujui', [KrsPembayaranController::class, 'krsDisetujui']);
        Route::get('/diajukan/{id}/edit', [KrsPembayaranController::class, 'krsEdit']);
        Route::get('/disetujui/{id}/edit', [KrsPembayaranController::class, 'krsEdit']);
        Route::put('/diajukan/{id}/update', [KrsPembayaranController::class, 'krsUpdate']);
    });

    Route::prefix('/data')->middleware('auth:admin,kaprodi,wakil_direktur,direktur')->group(function () {

        // DATA PERKULIAHAN
        Route::get('/perkuliahan', [PresensiController::class, 'kategori']);
        Route::get('/perkuliahan/{id}', [PresensiController::class, 'detailMatkul']);

        // DATA PRESENSI
        Route::get('/presence/{matkul_id}/{kelas_id}/{jadwal_id}/1-7', [PresensiController::class, 'cekPresensi1to7']);
        Route::get('/presence/{matkul_id}/{kelas_id}/{jadwal_id}/8-14', [PresensiController::class, 'cekPresensi8to14']);

        // DATA RESUME / BERITA ACARA PERKULAHAN
        Route::get('/resume', [PresensiController::class, 'kategoriInResume']);
        Route::get('/resume/{id}', [PresensiController::class, 'detailMatkulResume']);
        Route::get('/resume/{matkul_id}/{kelas_id}/{jadwal_id}/1-7', [PresensiController::class, 'cekResume1to7']);
        Route::get('/resume/{matkul_id}/{kelas_id}/{jadwal_id}/8-14', [PresensiController::class, 'cekResume8to14']);

        // DATA NILAI
        Route::get('/value', [NilaiController::class, 'kategori']);
        Route::get('/value/{id}', [NilaiController::class, 'detailMatkul']);
        Route::get('/value/{kelas_id}/{matkul_id}/{jadwal_id}/cek', [NilaiController::class, 'cekNilai']);

        // DATA KONTRAK
        Route::get('/contract', [KontrakController::class, 'kategori']);
        Route::get('/contract/{id}', [KontrakController::class, 'detailMatkul']);
        Route::get('/contract/{matkul_id}/{kelas_id}/{jadwal_id}/cek', [KontrakController::class, 'cekKontrak']);

        // DATA MATKUL (KAPRODI)
        Route::get('/matkul', [MatkulController::class, 'kategoriSemester']);
        Route::get('/matkul/{id}', [MatkulController::class, 'detailMatkulSemester']);
    });

    // SAKLAR NOTIF WA
    Route::get('/get-daily-schedule', [SettingsController::class, 'getDailySchedule']);
    Route::post('/toggle-daily-schedule', [SettingsController::class, 'toggleDailySchedule']);

    // HALAMAN MAHASISWA
    Route::prefix('/mahasiswa')->middleware('auth:mahasiswa')->group(function () {
        // KHS MHS
        Route::get('/nilai', [NilaiMahasiswaController::class, 'index']);
        Route::get('/riwayat/{semester_id}', [NilaiMahasiswaController::class, 'riwayat']);
        Route::get('/khs/{semester}', [NilaiMahasiswaController::class, 'khs']);
        Route::get('riwayat/khs/{semester}', [NilaiMahasiswaController::class, 'riwayatKhs']);

        // KRS MHS
        Route::get('/krs_pembayaran', [KrsPembayaranController::class, 'index']);
        Route::post('/krs_pembayaran', [KrsPembayaranController::class, 'createPembayaran'])->name('upload_bukti_pembayaran');
        Route::post('/krs', [KrsPembayaranController::class, 'pengajuanKrs']);
        Route::put('/krs/{id}/update', [KrsPembayaranController::class, 'krsUpdate']);
        Route::get('/krs/{id}/cetak', [KrsPembayaranController::class, 'krsCetak']);
        Route::get('/permohonan_surat', [PermohonanSuratController::class, 'index']);

        // PERMOHONAN SURAT
        Route::get('/permohonan_surat', [PermohonanSuratController::class, 'index']);
        Route::post('/permohonan_surat', [PermohonanSuratController::class, 'store'])->name('create-permohonan-surat');
        Route::get('/permohonan_surat/{id}/edit', [PermohonanSuratController::class, 'edit'])->name('permohonan-surat-edit');
        Route::put('/permohonan_surat/{id}/update', [PermohonanSuratController::class, 'update'])->name('permohonan-surat-update');
        Route::delete('/permohonan_surat/{id}/delete', [PermohonanSuratController::class, 'delete'])->name('permohonan-surat-delete');
    });

    // PEMBERITAHUAN / CHAT
    Route::prefix('/pemberitahuan')->middleware('auth:wakil_direktur,direktur,dosen,kaprodi,mahasiswa')->group(function () {
        Route::post('/send', [PemberitahuanController::class, 'sendMessage'])
            ->name('send.message');
        Route::get('/show', [PemberitahuanController::class, 'getMessages'])
            ->name('get.messages');
        Route::get('/showWhereDosen', [PemberitahuanController::class, 'getMessagesDosen'])
            ->name('get.messages.alternative');
        Route::get('/unread-messages', [PemberitahuanController::class, 'getUnreadMessageCount'])
            ->name('get.unread.count');
        Route::get('/unread-count/{contactId}/{contactType}', [
            PemberitahuanController::class,
            'getUnreadMessageCountByContact',
        ])
            ->name('get.unread.count.by.contact');
        Route::get('/contacts-dosen', [PemberitahuanController::class, 'getJadwalContactsDosen'])
            ->name('get.contacts.dosen');
        Route::post('/mark-as-read', [PemberitahuanController::class, 'markAsReadManual'])
            ->name('mark.as.read');

        // Bimbingan Akademik / Perwalian (Mahasiswa <-> DPA)
        Route::get('/contacts-guidance', [PemberitahuanController::class, 'getGuidanceContacts'])
            ->name('get.contacts.guidance');
        Route::get('/show-guidance', [PemberitahuanController::class, 'getGuidanceMessages'])
            ->name('get.messages.guidance');
        Route::post('/send-guidance', [PemberitahuanController::class, 'sendGuidanceMessage'])
            ->name('send.message.guidance');
        Route::post('/mark-guidance-read', [PemberitahuanController::class, 'markGuidanceAsRead'])
            ->name('mark.read.guidance');
    });

    // NOTIFIKASI
    Route::get('/get-notif', [NotificationController::class, 'getNotifications'])->middleware('auth:dosen,wakil_direktur,direktur,mahasiswa,admin,kaprodi');
    Route::post('/mark-notif-read', [NotificationController::class, 'markNotificationsAsRead'])->middleware('auth:dosen,wakil_direktur,direktur,mahasiswa,admin,kaprodi');

    Route::get('/lembar-monitoring/{jadwal_id}', [LembarMonitoringController::class, 'index']);

    // PERMOHONAN SURAT
    Route::prefix('/permohonan_surat')->group(function () {
        Route::get('/verifikasi', [PermohonanSuratController::class, 'verify']);
        Route::put('/verifikasi', [PermohonanSuratController::class, 'verifying'])->name('permohonan-surat-verifying');

        Route::get('/cetak-surat', [PermohonanSuratController::class, 'disetujui'])->name('permohonan-surat-disetujui');
        Route::put('/cetak-surat', [PermohonanSuratController::class, 'cetak'])->name('permohonan-surat-disetujui-update');
        Route::get('/selesai-surat', [PermohonanSuratController::class, 'selesai'])->name('permohonan-surat-selesai');
    });

    // INFORMASI TAMBAHAN
    Route::get('/informasi_tambahan', [InformasiLandingPageController::class, 'index'])->middleware('auth:admin');
    Route::post('/informasi_tambahan/kalender', [InformasiLandingPageController::class, 'storeKalender'])->middleware('auth:admin')->name('upload.kalender');
    Route::post('/informasi_tambahan/brosur', [InformasiLandingPageController::class, 'storeBrosur'])->middleware('auth:admin')->name('upload.brosur');
    Route::delete('/infomasi_tambahan/delete', [InformasiLandingPageController::class, 'destroy'])->name('informasi-tambahan-delete')->middleware('auth:admin');

    Route::post('/change-profile-picture', [ProfileController::class, 'changeProfilePicture'])->name('change.profile.picture')->middleware('auth:mahasiswa');

    // JADWAL UJIAN
    Route::resource('/jadwal-ujian', JadwalUjianController::class)->except(['show'])->middleware('auth:admin');
    Route::get('/jadwal-ujian/search', [JadwalUjianController::class, 'search'])->name('jadwal-ujian.search')->middleware('auth:admin');
});

use App\Http\Controllers\V2\Admin\DashboardController as AdminDashboardV2;
use App\Http\Controllers\V2\Admin\DataNilaiController;
use App\Http\Controllers\V2\Admin\InformasiTambahanController;
use App\Http\Controllers\V2\Admin\JadwalMengajarController;
use App\Http\Controllers\V2\Admin\PembayaranController;
use App\Http\Controllers\V2\Admin\PengajuanEditPresensiController;
use App\Http\Controllers\V2\Dosen\BimbinganController;
use App\Http\Controllers\V2\Dosen\KrsController;
use App\Http\Controllers\V2\Kaprodi\ApprovalController;
use App\Http\Controllers\V2\Kaprodi\DataPerkuliahanController;
use App\Http\Controllers\V2\Kaprodi\MonitoringController;

Route::prefix('v2')->middleware(['auth:admin,mahasiswa,direktur,wakil_direktur,dosen,kaprodi'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardV2::class, 'index'])->name('v2.admin.dashboard');
    Route::get('/dosen/dashboard', [App\Http\Controllers\V2\Dosen\DashboardController::class, 'index'])->name('v2.dosen.dashboard');
    Route::get('/kaprodi/dashboard', [App\Http\Controllers\V2\Kaprodi\DashboardController::class, 'index'])->name('v2.kaprodi.dashboard');

    // Kaprodi Monitoring
    Route::middleware('auth:kaprodi')->prefix('kaprodi')->name('v2.kaprodi.')->group(function () {
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('matkul', [MonitoringController::class, 'matkul'])->name('matkul.index');
            Route::get('matkul/{semester_id}', [MonitoringController::class, 'matkulDetail'])->name('matkul.detail');

            Route::get('perkuliahan', [MonitoringController::class, 'perkuliahan'])->name('perkuliahan.index');
            Route::get('perkuliahan/{kelas_id}', [MonitoringController::class, 'perkuliahanDetail'])->name('perkuliahan.detail');
            Route::get('perkuliahan/presensi/{matkul_id}/{kelas_id}/{jadwal_id}/{rentang}', [MonitoringController::class, 'presensiCek'])->name('perkuliahan.presensi-cek');

            Route::get('nilai', [MonitoringController::class, 'nilai'])->name('nilai.index');
            Route::get('nilai/{kelas_id}', [MonitoringController::class, 'nilaiDetail'])->name('nilai.detail');
            Route::get('nilai/cek/{matkul_id}/{kelas_id}/{jadwal_id}', [MonitoringController::class, 'nilaiCek'])->name('nilai.cek');
        });

        // Kaprodi Approval
        Route::prefix('rekap-presensi')->name('rekap-presensi.')->group(function () {
            Route::get('/', [ApprovalController::class, 'presensiIndex'])->name('index');
            Route::get('detail/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [ApprovalController::class, 'presensiDetail'])->name('detail');
            Route::post('approve/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [ApprovalController::class, 'presensiApprove'])->name('approve');
        });

        // Kaprodi Data Perkuliahan
        Route::get('/data-perkuliahan', [DataPerkuliahanController::class, 'index'])->name('data-perkuliahan.index');
        Route::get('/data-perkuliahan/{id}', [DataPerkuliahanController::class, 'show'])->name('data-perkuliahan.show');
        Route::get('/data-perkuliahan/presensi-cetak/{matkul_id}/{kelas_id}/{jadwal_id}/{rentang}', [DataPerkuliahanController::class, 'cetakPresensi'])->name('data-perkuliahan.presensi-cetak');
        Route::get('/data-perkuliahan/bap-cetak/{matkul_id}/{kelas_id}/{jadwal_id}/{rentang}', [DataPerkuliahanController::class, 'cetakBap'])->name('data-perkuliahan.bap-cetak');

        Route::prefix('rekap-berita')->name('rekap-berita.')->group(function () {
            Route::get('/', [ApprovalController::class, 'beritaIndex'])->name('index');
            Route::get('detail/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [ApprovalController::class, 'beritaDetail'])->name('detail');
            Route::post('approve/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [ApprovalController::class, 'beritaApprove'])->name('approve');
        });

        Route::prefix('rekap-kontrak')->name('rekap-kontrak.')->group(function () {
            Route::get('/', [ApprovalController::class, 'kontrakIndex'])->name('index');
            Route::get('detail/{jadwal_id}/{matkul_id}/{kelas_id}', [ApprovalController::class, 'kontrakDetail'])->name('detail');
            Route::post('approve/{jadwal_id}/{matkul_id}/{kelas_id}', [ApprovalController::class, 'kontrakApprove'])->name('approve');
        });

        Route::prefix('permohonan-surat')->name('permohonan-surat.')->group(function () {
            Route::get('diajukan', [ApprovalController::class, 'suratDiajukan'])->name('diajukan');
            Route::get('disetujui', [ApprovalController::class, 'suratDisetujui'])->name('disetujui');
            Route::post('approve', [ApprovalController::class, 'permohonanSuratApprove'])->name('approve');
            Route::delete('reject/{id}', [ApprovalController::class, 'permohonanSuratReject'])->name('reject');
        });

        Route::post('switch-prodi', [ApprovalController::class, 'switchProdi'])->name('switch-prodi');
    });

    // Direktur & Wadir V2 Routes
    Route::middleware('auth:direktur,wakil_direktur')->prefix('direktur')->name('v2.direktur.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\V2\Direktur\DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('rekap-presensi')->name('rekap-presensi.')->group(function () {
            Route::get('/', [App\Http\Controllers\V2\Direktur\ApprovalController::class, 'presensiIndex'])->name('index');
            Route::get('detail/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [App\Http\Controllers\V2\Direktur\ApprovalController::class, 'presensiDetail'])->name('detail');
            Route::post('approve/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [App\Http\Controllers\V2\Direktur\ApprovalController::class, 'presensiApprove'])->name('approve');
        });

        Route::prefix('rekap-berita')->name('rekap-berita.')->group(function () {
            Route::get('/', [App\Http\Controllers\V2\Direktur\ApprovalController::class, 'beritaIndex'])->name('index');
            Route::get('detail/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [App\Http\Controllers\V2\Direktur\ApprovalController::class, 'beritaDetail'])->name('detail');
            Route::post('approve/{pertemuan}/{matkul_id}/{kelas_id}/{jadwal_id}', [App\Http\Controllers\V2\Direktur\ApprovalController::class, 'beritaApprove'])->name('approve');
        });

        Route::prefix('rekap-kontrak')->name('rekap-kontrak.')->group(function () {
            Route::get('/', [App\Http\Controllers\V2\Direktur\ApprovalController::class, 'kontrakIndex'])->name('index');
            Route::get('detail/{jadwal_id}/{matkul_id}/{kelas_id}', [App\Http\Controllers\V2\Direktur\ApprovalController::class, 'kontrakDetail'])->name('detail');
            Route::post('approve/{jadwal_id}/{matkul_id}/{kelas_id}', [App\Http\Controllers\V2\Direktur\ApprovalController::class, 'kontrakApprove'])->name('approve');
        });

        // Direktur Monitoring
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('perkuliahan', [App\Http\Controllers\V2\Direktur\MonitoringController::class, 'perkuliahan'])->name('perkuliahan.index');
            Route::get('perkuliahan/{kelas_id}', [App\Http\Controllers\V2\Direktur\MonitoringController::class, 'perkuliahanDetail'])->name('perkuliahan.detail');
            Route::get('perkuliahan/presensi/{matkul_id}/{kelas_id}/{jadwal_id}/{rentang}', [App\Http\Controllers\V2\Direktur\MonitoringController::class, 'presensiCek'])->name('perkuliahan.presensi-cek');

            Route::get('nilai', [App\Http\Controllers\V2\Direktur\MonitoringController::class, 'nilai'])->name('nilai.index');
            Route::get('nilai/{kelas_id}', [App\Http\Controllers\V2\Direktur\MonitoringController::class, 'nilaiDetail'])->name('nilai.detail');
            Route::get('nilai/cek/{matkul_id}/{kelas_id}/{jadwal_id}', [App\Http\Controllers\V2\Direktur\MonitoringController::class, 'nilaiCek'])->name('nilai.cek');
        });
    });

    // Dosen Presensi
    Route::middleware('auth:dosen')->prefix('dosen')->group(function () {
        Route::get('/data-presensi', [App\Http\Controllers\V2\Dosen\PresensiController::class, 'index'])->name('v2.dosen.presensi.index');
        Route::post('/data-presensi/request-edit', [App\Http\Controllers\V2\Dosen\PresensiController::class, 'requestEdit'])->name('v2.dosen.presensi.request-edit');
        Route::get('/data-presensi/create/{id}', [App\Http\Controllers\V2\Dosen\PresensiController::class, 'create'])->name('v2.dosen.presensi.create');
        Route::get('/data-presensi/{jadwal_id}/edit/{pertemuan}', [App\Http\Controllers\V2\Dosen\PresensiController::class, 'edit'])->name('v2.dosen.presensi.edit');
        Route::post('/data-presensi', [App\Http\Controllers\V2\Dosen\PresensiController::class, 'store'])->name('v2.dosen.presensi.store');
        Route::put('/data-presensi/{jadwal_id}/update/{pertemuan}', [App\Http\Controllers\V2\Dosen\PresensiController::class, 'update'])->name('v2.dosen.presensi.update');

        // Dosen KRS (Validasi KRS DPA)
        Route::get('/krs', [KrsController::class, 'index'])->name('v2.dosen.krs.index');
        Route::get('/krs/{id}/detail', [KrsController::class, 'detail'])->name('v2.dosen.krs.detail');
        Route::put('/krs/{id}/approve', [KrsController::class, 'approve'])->name('v2.dosen.krs.approve');

        // Dosen Bimbingan Akademik
        Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('v2.dosen.bimbingan.index');
    });

    // Dosen Nilai (V2)
    Route::middleware('auth:dosen')->prefix('dosen/nilai')->name('v2.dosen.nilai.')->group(function () {
        Route::get('/', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'index'])->name('index');
        Route::get('/{jadwal_id}', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'show'])->name('show');

        // Tugas
        Route::post('/{jadwal_id}/tugas', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'storeTugas'])->name('tugas.store');
        Route::put('/{jadwal_id}/tugas/{tugas_ke}', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'updateTugas'])->name('tugas.update');
        Route::delete('/{jadwal_id}/tugas/{tugas_ke}', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'destroyTugas'])->name('tugas.destroy');

        // UTS
        Route::post('/{jadwal_id}/uts', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'storeUts'])->name('uts.store');
        Route::put('/{jadwal_id}/uts', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'updateUts'])->name('uts.update');

        // UAS
        Route::post('/{jadwal_id}/uas', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'storeUas'])->name('uas.store');
        Route::put('/{jadwal_id}/uas', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'updateUas'])->name('uas.update');

        // Etika
        Route::post('/{jadwal_id}/etika', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'storeEtika'])->name('etika.store');
        Route::put('/{jadwal_id}/etika', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'updateEtika'])->name('etika.update');

        // Keaktifan
        Route::post('/{jadwal_id}/aktif', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'storeAktif'])->name('aktif.store');
        Route::put('/{jadwal_id}/aktif', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'updateAktif'])->name('aktif.update');

        // Pengajuan Rekap Nilai
        Route::post('/{jadwal_id}/rekap', [App\Http\Controllers\V2\Dosen\NilaiController::class, 'pengajuanRekap'])->name('rekap.store');
    });

    Route::get('/profile', [App\Http\Controllers\V2\ProfileController::class, 'edit'])->name('v2.profile.edit');

    // Admin Data Master
    Route::middleware('auth:admin')->prefix('admin/data-master')->group(function () {
        Route::resource('data-matkul', App\Http\Controllers\V2\Admin\MatkulController::class)
            ->names('v2.admin.data-matkul')
            ->except(['show', 'create', 'edit']);

        Route::resource('data-prodi', App\Http\Controllers\V2\Admin\ProdiController::class)
            ->names('v2.admin.data-prodi')
            ->except(['show', 'create', 'edit']);

        Route::resource('data-semester', App\Http\Controllers\V2\Admin\SemesterController::class)
            ->names('v2.admin.data-semester')
            ->except(['show', 'create', 'edit', 'update']);

        Route::resource('data-kelas', App\Http\Controllers\V2\Admin\KelasController::class)
            ->names('v2.admin.data-kelas')
            ->except(['show', 'create', 'edit']);

        Route::resource('tahun-akademik', App\Http\Controllers\V2\Admin\TahunAkademikController::class)
            ->names('v2.admin.tahun-akademik')
            ->except(['show', 'create', 'edit']);

        Route::resource('data-ruangan', App\Http\Controllers\V2\Admin\RuanganController::class)
            ->names('v2.admin.data-ruangan')
            ->except(['show', 'create', 'edit']);

        Route::resource('data-pegawai', App\Http\Controllers\V2\Admin\PegawaiController::class)
            ->names('v2.admin.data-pegawai')
            ->except(['show', 'create', 'edit']);

        Route::resource('data-dosen', App\Http\Controllers\V2\Admin\DosenController::class)
            ->names('v2.admin.data-dosen')
            ->except(['show', 'create', 'edit']);

        Route::get('data-dosen/export', [App\Http\Controllers\V2\Admin\DosenController::class, 'export'])
            ->name('v2.admin.data-dosen.export');
        Route::post('data-dosen/import', [App\Http\Controllers\V2\Admin\DosenController::class, 'import'])
            ->name('v2.admin.data-dosen.import');
        Route::get('data-dosen/download-format', [App\Http\Controllers\V2\Admin\DosenController::class, 'downloadFormat'])
            ->name('v2.admin.data-dosen.download-format');

        Route::resource('data-kaprodi', App\Http\Controllers\V2\Admin\KaprodiController::class)
            ->names('v2.admin.data-kaprodi')
            ->except(['show', 'create', 'edit']);
        Route::resource('data-wadir', App\Http\Controllers\V2\Admin\WadirController::class)
            ->names('v2.admin.data-wadir')
            ->except(['show', 'create', 'edit']);
        Route::resource('data-direktur', App\Http\Controllers\V2\Admin\DirekturController::class)
            ->names('v2.admin.data-direktur')
            ->except(['show', 'create', 'edit']);

        Route::get('data-pegawai/export', [App\Http\Controllers\V2\Admin\PegawaiController::class, 'export'])
            ->name('v2.admin.data-pegawai.export');
        Route::post('data-pegawai/import', [App\Http\Controllers\V2\Admin\PegawaiController::class, 'import'])
            ->name('v2.admin.data-pegawai.import');
        Route::get('data-pegawai/download-format', [App\Http\Controllers\V2\Admin\PegawaiController::class, 'downloadFormat'])
            ->name('v2.admin.data-pegawai.download-format');

        Route::post('data-semester/ganti-status', [App\Http\Controllers\V2\Admin\SemesterController::class, 'gantiStatus'])
            ->name('v2.admin.data-semester.ganti-status');
    });

    // Data Mahasiswa (Tanpa data-master prefix)
    Route::middleware('auth:admin')->prefix('admin')->group(function () {
        Route::resource('jadwal-mengajar', JadwalMengajarController::class)
            ->names('v2.admin.jadwal-mengajar')
            ->except(['create', 'edit', 'show']);

        Route::resource('jadwal-ujian', App\Http\Controllers\V2\Admin\JadwalUjianController::class)
            ->names('v2.admin.jadwal-ujian')
            ->except(['create', 'edit', 'show']);

        Route::resource('data-mahasiswa', App\Http\Controllers\V2\Admin\MahasiswaController::class)
            ->names('v2.admin.data-mahasiswa')
            ->parameters(['data-mahasiswa' => 'id'])
            ->except(['create', 'edit']);
        Route::post('data-mahasiswa/bulk-delete', [App\Http\Controllers\V2\Admin\MahasiswaController::class, 'bulkDelete'])
            ->name('v2.admin.data-mahasiswa.bulk-delete');
        Route::post('data-mahasiswa/pindah-kelas', [App\Http\Controllers\V2\Admin\MahasiswaController::class, 'pindahKelas'])
            ->name('v2.admin.data-mahasiswa.pindah-kelas');
        Route::post('data-mahasiswa/import', [App\Http\Controllers\V2\Admin\MahasiswaController::class, 'import'])
            ->name('v2.admin.data-mahasiswa.import');
        Route::get('data-mahasiswa/export/{id}', [App\Http\Controllers\V2\Admin\MahasiswaController::class, 'export'])
            ->name('v2.admin.data-mahasiswa.export');
        Route::get('data-mahasiswa/export-all', [App\Http\Controllers\V2\Admin\MahasiswaController::class, 'exportAll'])
            ->name('v2.admin.data-mahasiswa.export-all');

        // Pengajuan Edit Presensi
        Route::get('pengajuan-edit-presensi', [PengajuanEditPresensiController::class, 'index'])
            ->name('v2.admin.pengajuan-edit-presensi.index');
        Route::post('pengajuan-edit-presensi/verify', [PengajuanEditPresensiController::class, 'verify'])
            ->name('v2.admin.pengajuan-edit-presensi.verify');

        // Data Perkuliahan
        Route::resource('data-perkuliahan', App\Http\Controllers\V2\Admin\DataPerkuliahanController::class)
            ->names('v2.admin.data-perkuliahan')
            ->only(['index', 'show']);

        // Data Nilai
        Route::resource('data-nilai', DataNilaiController::class)
            ->names('v2.admin.data-nilai')
            ->only(['index', 'show']);

        // Rekap Nilai
        Route::prefix('rekap-nilai')->name('v2.admin.rekap-nilai.')->group(function () {
            Route::get('pengajuan', [App\Http\Controllers\V2\Admin\RekapNilaiController::class, 'pengajuan'])->name('pengajuan');
            Route::get('disetujui', [App\Http\Controllers\V2\Admin\RekapNilaiController::class, 'disetujui'])->name('disetujui');
        });

        // Pembayaran
        Route::prefix('pembayaran')->name('v2.admin.pembayaran.')->group(function () {
            Route::get('diajukan', [PembayaranController::class, 'diajukan'])->name('diajukan');
            Route::get('disetujui', [PembayaranController::class, 'disetujui'])->name('disetujui');
            Route::put('{id}', [PembayaranController::class, 'update'])->name('update');
        });

        // KRS
        Route::prefix('krs')->name('v2.admin.krs.')->group(function () {
            Route::get('kategori', [App\Http\Controllers\V2\Admin\KrsController::class, 'index'])->name('kategori');
            Route::get('kategori/cetak/{id}', [App\Http\Controllers\V2\Admin\KrsController::class, 'cetak'])->name('cetak');
            Route::get('kategori/{id}', [App\Http\Controllers\V2\Admin\KrsController::class, 'show'])->name('show');
        });

        // Permohonan Surat
        Route::prefix('permohonan-surat')->name('v2.admin.permohonan-surat.')->group(function () {
            Route::get('cetak', [App\Http\Controllers\V2\Admin\PermohonanSuratController::class, 'cetak'])->name('cetak');
            Route::get('selesai', [App\Http\Controllers\V2\Admin\PermohonanSuratController::class, 'selesai'])->name('selesai');
            Route::put('{id}/terbitkan', [App\Http\Controllers\V2\Admin\PermohonanSuratController::class, 'terbitkan'])->name('terbitkan');
            Route::get('{id}/cetak-dokumen', [App\Http\Controllers\V2\Admin\PermohonanSuratController::class, 'cetakDokumen'])->name('cetak-dokumen');
        });

        // Informasi Tambahan
        Route::prefix('informasi-tambahan')->name('v2.admin.informasi-tambahan.')->group(function () {
            Route::get('/', [InformasiTambahanController::class, 'index'])->name('index');
            Route::post('kalender', [InformasiTambahanController::class, 'storeKalender'])->name('store-kalender');
            Route::post('brosur', [InformasiTambahanController::class, 'storeBrosur'])->name('store-brosur');
            Route::delete('{id}', [InformasiTambahanController::class, 'destroy'])->name('destroy');
        });
    });

    // Mahasiswa Routes
    Route::middleware('auth:mahasiswa')->prefix('mahasiswa')->name('v2.mahasiswa.')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\V2\Mahasiswa\DashboardController::class, 'index'])->name('dashboard');

        // Nilai & KHS
        Route::prefix('nilai')->name('nilai.')->group(function () {
            Route::get('/', [App\Http\Controllers\V2\Mahasiswa\NilaiController::class, 'index'])->name('index');
        });
        Route::get('riwayat/{semester_id}', [App\Http\Controllers\V2\Mahasiswa\NilaiController::class, 'riwayat'])->name('riwayat');
        Route::get('khs/{semester_id}', [App\Http\Controllers\V2\Mahasiswa\NilaiController::class, 'khs'])->name('khs');

        // KRS & Pembayaran
        Route::prefix('krs_pembayaran')->name('krs-pembayaran.')->group(function () {
            Route::get('/', [App\Http\Controllers\V2\Mahasiswa\KrsPembayaranController::class, 'index'])->name('index');
            Route::post('upload', [App\Http\Controllers\V2\Mahasiswa\KrsPembayaranController::class, 'uploadPembayaran'])->name('upload');
            Route::post('pengajuan', [App\Http\Controllers\V2\Mahasiswa\KrsPembayaranController::class, 'pengajuanKrs'])->name('pengajuan');
            Route::put('persetujuan/{id}', [App\Http\Controllers\V2\Mahasiswa\KrsPembayaranController::class, 'persetujuanKrs'])->name('persetujuan');
            Route::get('cetak/{id}', [App\Http\Controllers\V2\Mahasiswa\KrsPembayaranController::class, 'cetakKrs'])->name('cetak');
        });

        // Permohonan Surat Mahasiswa
        Route::prefix('permohonan-surat')->name('permohonan-surat.')->group(function () {
            Route::get('/', [App\Http\Controllers\V2\Mahasiswa\PermohonanSuratController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\V2\Mahasiswa\PermohonanSuratController::class, 'store'])->name('store');
            Route::put('{id}', [App\Http\Controllers\V2\Mahasiswa\PermohonanSuratController::class, 'update'])->name('update');
            Route::delete('{id}', [App\Http\Controllers\V2\Mahasiswa\PermohonanSuratController::class, 'destroy'])->name('destroy');
        });
    });
});
