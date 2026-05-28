<?php

namespace Tests\Feature\V2;

use App\Models\Admin;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\PengajuanCetakKartuUjian;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KartuUjianTest extends TestCase
{
    use DatabaseTransactions;

    protected Mahasiswa $mahasiswa;

    protected Admin $admin;

    protected Semester $semester;

    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Clear existing questionnaires to isolate the check behavior
        \App\Models\Questionnaire::query()->delete();
        \App\Models\QuestionnaireResponse::query()->delete();

        $prodi = Prodi::create([
            'nama_prodi' => 'Teknik Elektro',
            'singkatan' => 'TE',
            'kode_prodi' => '1001',
            'jenjang' => 'D3',
            'alias_nama' => 'Electrical Engineering',
            'alias_jenjang' => 'Associate Degree',
        ]);

        $this->semester = Semester::create([
            'semester' => 2,
            'status' => 1,
        ]);

        $this->kelas = Kelas::create([
            'kode_kelas' => 'K001',
            'nama_kelas' => 'TE-2A',
            'jenis_kelas' => 'Reguler',
            'id_prodi' => $prodi->id,
            'id_semester' => $this->semester->id,
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'nama_lengkap' => 'Budi Santoso',
            'nim' => '12345678',
            'nisn' => '0012345678',
            'nik' => '3301010101010001',
            'email' => 'budi@test.com',
            'password' => Hash::make('password'),
            'alamat' => 'Purworejo',
            'no_telephone' => '08122334455',
            'tanggal_lahir' => '2004-05-10',
            'tempat_lahir' => 'Purworejo',
            'nama_ibu' => 'Siti',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'status_krs' => 0,
            'tahun_masuk' => '2024',
        ]);

        $this->admin = Admin::create([
            'nama' => 'Admin Keuangan',
            'email' => 'admin.keuangan@test.com',
            'password' => Hash::make('password'),
            'no_telephone' => '08998877665',
        ]);

        TahunAkademik::create([
            'tahun_akademik' => '2025/2026',
            'semester' => 'Genap',
            'status' => '1',
        ]);
    }

    /**
     * Test student can access index page.
     */
    public function test_student_can_access_jadwal_ujian_page(): void
    {
        $response = $this->actingAs($this->mahasiswa, 'mahasiswa')
            ->get('/v2/mahasiswa/jadwal-ujian');

        $response->assertStatus(200);
    }

    /**
     * Test student can submit exam card request with valid proofs.
     */
    public function test_student_can_submit_exam_card_request_with_proofs(): void
    {
        // Mock files
        $buktiSpp = UploadedFile::fake()->image('spp.jpg');
        $buktiUjian = UploadedFile::fake()->image('ujian.jpg');

        $response = $this->actingAs($this->mahasiswa, 'mahasiswa')
            ->post('/v2/mahasiswa/jadwal-ujian/ajukan', [
                'jenis_ujian' => 'uts',
                'bulan_spp' => 'Mei',
                'tahun_spp' => 2026,
                'bukti_spp' => $buktiSpp,
                'bukti_pembayaran_ujian' => $buktiUjian,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pengajuan_cetak_kartu_ujians', [
            'mahasiswa_id' => $this->mahasiswa->id,
            'semester_id' => $this->semester->id,
            'jenis_ujian' => 'uts',
            'bulan_spp' => 'Mei',
            'tahun_spp' => 2026,
            'status' => 0, // Pending
        ]);
    }

    /**
     * Test admin can view exam card request index.
     */
    public function test_admin_can_view_exam_card_requests(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/v2/admin/pengajuan-kartu-ujian');

        $response->assertStatus(200);
    }

    /**
     * Test admin can approve student's exam card request.
     */
    public function test_admin_can_approve_exam_card_request(): void
    {
        // First create the request
        $pengajuan = PengajuanCetakKartuUjian::create([
            'mahasiswa_id' => $this->mahasiswa->id,
            'semester_id' => $this->semester->id,
            'jenis_ujian' => 'uts',
            'bukti_spp' => 'bukti_kartu_ujian/spp.jpg',
            'bukti_pembayaran_ujian' => 'bukti_kartu_ujian/ujian.jpg',
            'bulan_spp' => 'Mei',
            'tahun_spp' => 2026,
            'status' => 0,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put("/v2/admin/pengajuan-kartu-ujian/{$pengajuan->id}/status", [
                'status' => '1', // Approve / Selesai
                'keterangan' => 'Silakan ambil di akademik.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pengajuan_cetak_kartu_ujians', [
            'id' => $pengajuan->id,
            'status' => 1,
            'keterangan' => 'Silakan ambil di akademik.',
            'petugas_id' => $this->admin->id,
        ]);

        // Check notification created
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->mahasiswa->id,
            'notifiable_type' => Mahasiswa::class,
        ]);
    }

    /**
     * Test admin can reject student's exam card request.
     */
    public function test_admin_can_reject_exam_card_request(): void
    {
        $pengajuan = PengajuanCetakKartuUjian::create([
            'mahasiswa_id' => $this->mahasiswa->id,
            'semester_id' => $this->semester->id,
            'jenis_ujian' => 'uts',
            'bukti_spp' => 'bukti_kartu_ujian/spp.jpg',
            'bukti_pembayaran_ujian' => 'bukti_kartu_ujian/ujian.jpg',
            'bulan_spp' => 'Mei',
            'tahun_spp' => 2026,
            'status' => 0,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put("/v2/admin/pengajuan-kartu-ujian/{$pengajuan->id}/status", [
                'status' => '2', // Reject
                'keterangan' => 'Bukti SPP buram/tidak terbaca.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pengajuan_cetak_kartu_ujians', [
            'id' => $pengajuan->id,
            'status' => 2,
            'keterangan' => 'Bukti SPP buram/tidak terbaca.',
            'petugas_id' => $this->admin->id,
        ]);
    }
}
