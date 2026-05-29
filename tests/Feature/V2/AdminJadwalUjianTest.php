<?php

namespace Tests\Feature\V2;

use App\Models\Admin;
use App\Models\Dosen;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\Matkul;
use App\Models\Pegawai;
use App\Models\Prodi;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminJadwalUjianTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $admin;

    protected Dosen $dosen;

    protected Pegawai $pegawai;

    protected Semester $semester;

    protected Kelas $kelas;

    protected Matkul $matkul;

    protected Ruangan $ruangan;

    protected TahunAkademik $tahunAkademik;

    protected function setUp(): void
    {
        parent::setUp();

        $prodi = Prodi::create([
            'nama_prodi' => 'Teknik Informatika',
            'singkatan' => 'TI',
            'kode_prodi' => '1002',
            'jenjang' => 'D3',
            'alias_nama' => 'Informatics Engineering',
            'alias_jenjang' => 'Associate Degree',
        ]);

        $this->semester = Semester::create([
            'semester' => 2,
            'status' => 1,
        ]);

        $this->kelas = Kelas::create([
            'kode_kelas' => 'K002',
            'nama_kelas' => 'TI-2A',
            'jenis_kelas' => 'Reguler',
            'id_prodi' => $prodi->id,
            'id_semester' => $this->semester->id,
        ]);

        $this->matkul = Matkul::create([
            'nama_matkul' => 'Algoritma Pemrograman',
            'alias' => 'Programming Algorithms',
            'kode' => 'TI201',
            'semester_id' => $this->semester->id,
            'prodi_id' => $prodi->id,
        ]);

        $this->ruangan = Ruangan::create([
            'nama' => 'Lab Komputer 1',
        ]);

        $this->dosen = Dosen::create([
            'nama' => 'Dosen Penguji',
            'nidn' => '0987654321',
            'pembimbing_akademik' => 0,
            'jenis_kelamin' => 'L',
            'no_telephone' => '08123456789',
            'agama' => 'Islam',
            'status' => 1,
            'tanggal_lahir' => '1980-01-01',
            'tempat_lahir' => 'Purworejo',
            'email' => 'dosen.penguji@test.com',
            'password' => Hash::make('password'),
        ]);

        $this->pegawai = Pegawai::create([
            'nama' => 'Pegawai Administrasi',
            'nuptk' => '1122334455',
            'jenis_kelamin' => 'L',
            'no_telephone' => '08776655443',
            'agama' => 'Kristen',
            'status' => 1,
            'tanggal_lahir' => '1990-05-15',
            'tempat_lahir' => 'Purworejo',
            'email' => 'pegawai.admin@test.com',
            'password' => Hash::make('password'),
        ]);

        $this->admin = Admin::create([
            'nama' => 'Admin Akademik',
            'email' => 'admin.akademik@test.com',
            'password' => Hash::make('password'),
            'no_telephone' => '08998877665',
        ]);

        $this->tahunAkademik = TahunAkademik::create([
            'tahun_akademik' => '2025/2026',
            'semester' => 'Genap',
            'status' => '1',
        ]);
    }

    public function test_admin_can_access_jadwal_ujian_index(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/v2/admin/jadwal-ujian');

        $response->assertStatus(200);
    }

    public function test_admin_can_store_jadwal_ujian_with_dosen_as_pengawas(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post('/v2/admin/jadwal-ujian', [
                'matkuls_id' => $this->matkul->id,
                'pengawas_value' => "dosen-{$this->dosen->id}",
                'kelas_id' => $this->kelas->id,
                'ruangans_id' => $this->ruangan->id,
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '10:00',
                'tanggal' => '2026-06-15',
                'jenis_ujian' => 'uts',
                'tahun' => '2025/2026',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('jadwal_ujians', [
            'matkuls_id' => $this->matkul->id,
            'pengawas_id' => $this->dosen->id,
            'pengawas_type' => Dosen::class,
            'kelas_id' => $this->kelas->id,
            'ruangans_id' => $this->ruangan->id,
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '10:00:00',
            'tanggal' => '2026-06-15',
            'jenis_ujian' => 'uts',
            'tahun' => '2025/2026',
        ]);
    }

    public function test_admin_can_store_jadwal_ujian_with_pegawai_as_pengawas(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post('/v2/admin/jadwal-ujian', [
                'matkuls_id' => $this->matkul->id,
                'pengawas_value' => "pegawai-{$this->pegawai->id}",
                'kelas_id' => $this->kelas->id,
                'ruangans_id' => $this->ruangan->id,
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '10:00',
                'tanggal' => '2026-06-15',
                'jenis_ujian' => 'uts',
                'tahun' => '2025/2026',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('jadwal_ujians', [
            'matkuls_id' => $this->matkul->id,
            'pengawas_id' => $this->pegawai->id,
            'pengawas_type' => Pegawai::class,
            'kelas_id' => $this->kelas->id,
            'ruangans_id' => $this->ruangan->id,
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '10:00:00',
            'tanggal' => '2026-06-15',
            'jenis_ujian' => 'uts',
            'tahun' => '2025/2026',
        ]);
    }

    public function test_admin_can_update_jadwal_ujian(): void
    {
        $jadwal = JadwalUjian::create([
            'matkuls_id' => $this->matkul->id,
            'pengawas_id' => $this->dosen->id,
            'pengawas_type' => Dosen::class,
            'kelas_id' => $this->kelas->id,
            'ruangans_id' => $this->ruangan->id,
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'tanggal' => '2026-06-15',
            'jenis_ujian' => 'uts',
            'tahun' => '2025/2026',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put("/v2/admin/jadwal-ujian/{$jadwal->id}", [
                'matkuls_id' => $this->matkul->id,
                'pengawas_value' => "pegawai-{$this->pegawai->id}",
                'kelas_id' => $this->kelas->id,
                'ruangans_id' => $this->ruangan->id,
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '11:00',
                'tanggal' => '2026-06-15',
                'jenis_ujian' => 'uts',
                'tahun' => '2025/2026',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('jadwal_ujians', [
            'id' => $jadwal->id,
            'pengawas_id' => $this->pegawai->id,
            'pengawas_type' => Pegawai::class,
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '11:00:00',
        ]);
    }

    public function test_admin_can_delete_jadwal_ujian(): void
    {
        $jadwal = JadwalUjian::create([
            'matkuls_id' => $this->matkul->id,
            'pengawas_id' => $this->dosen->id,
            'pengawas_type' => Dosen::class,
            'kelas_id' => $this->kelas->id,
            'ruangans_id' => $this->ruangan->id,
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'tanggal' => '2026-06-15',
            'jenis_ujian' => 'uts',
            'tahun' => '2025/2026',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete("/v2/admin/jadwal-ujian/{$jadwal->id}");

        $response->assertRedirect();

        $this->assertSoftDeleted('jadwal_ujians', [
            'id' => $jadwal->id,
        ]);
    }
}
