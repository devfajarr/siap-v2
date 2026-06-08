<?php

namespace Tests\Feature\V2;

use App\Models\Admin;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Semester;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MahasiswaControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $admin;

    protected Prodi $prodi;

    protected Prodi $prodiLain;

    protected Kelas $kelasReguler;

    protected Kelas $kelasKaryawan;

    protected Kelas $kelasLainProdi;

    protected Mahasiswa $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nama' => 'Admin Pindah Kelas Test',
            'email' => 'admin-pindah-kelas@test.com',
            'no_telephone' => '08100000001',
            'password' => Hash::make('password'),
        ]);

        $semester = Semester::firstOrCreate(['semester' => 1], ['status' => 1]);

        $this->prodi = Prodi::firstOrCreate(
            ['kode_prodi' => 'TI-PKT'],
            [
                'nama_prodi' => 'Teknik Informatika PKT',
                'singkatan' => 'TI-PKT',
                'jenjang' => 'D3',
                'alias_nama' => 'TI Test',
                'alias_jenjang' => 'Diploma Three',
            ]
        );

        $this->prodiLain = Prodi::firstOrCreate(
            ['kode_prodi' => 'AK-PKT'],
            [
                'nama_prodi' => 'Akuntansi PKT',
                'singkatan' => 'AK-PKT',
                'jenjang' => 'D3',
                'alias_nama' => 'AK Test',
                'alias_jenjang' => 'Diploma Three',
            ]
        );

        $this->kelasReguler = Kelas::create([
            'nama_kelas' => 'TI 1A PKT',
            'kode_kelas' => 'PKT001',
            'jenis_kelas' => 'Reguler',
            'id_prodi' => $this->prodi->id,
            'id_semester' => $semester->id,
        ]);

        $this->kelasKaryawan = Kelas::create([
            'nama_kelas' => 'TI 1B PKT',
            'kode_kelas' => 'PKT002',
            'jenis_kelas' => 'Karyawan',
            'id_prodi' => $this->prodi->id,
            'id_semester' => $semester->id,
        ]);

        $this->kelasLainProdi = Kelas::create([
            'nama_kelas' => 'AK 1A PKT',
            'kode_kelas' => 'PKT003',
            'jenis_kelas' => 'Reguler',
            'id_prodi' => $this->prodiLain->id,
            'id_semester' => $semester->id,
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'nama_lengkap' => 'Mahasiswa Pindah Test',
            'nim' => '24000099',
            'nisn' => '9988776655',
            'nik' => '3300000000000099',
            'jenis_kelamin' => 'L',
            'email' => 'mhs-pindah@test.com',
            'no_telephone' => '081000099',
            'status' => 1,
            'password' => Hash::make('password'),
            'tanggal_lahir' => '2004-01-01',
            'tempat_lahir' => 'Test City',
            'tahun_masuk' => '2024',
            'kelas_id' => $this->kelasReguler->id,
            'alamat' => 'Jl. Test No. 99',
            'nama_ibu' => 'Ibu Test PKT',
        ]);
    }

    /**
     * Test pindah kelas dalam jenis yang sama (Reguler → Reguler) berhasil.
     */
    public function test_pindah_kelas_sejenis_berhasil(): void
    {
        $semester2 = Semester::firstOrCreate(['semester' => 2], ['status' => 1]);
        $kelasTujuan = Kelas::create([
            'nama_kelas' => 'TI 2A PKT',
            'kode_kelas' => 'PKT004',
            'jenis_kelas' => 'Reguler',
            'id_prodi' => $this->prodi->id,
            'id_semester' => $semester2->id,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.data-mahasiswa.pindah-kelas'), [
                'ids' => [$this->mahasiswa->id],
                'kelas_id' => $kelasTujuan->id,
                'source_kelas_id' => $this->kelasReguler->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mahasiswas', [
            'id' => $this->mahasiswa->id,
            'kelas_id' => $kelasTujuan->id,
        ]);
    }

    /**
     * Test pindah kelas lintas jenis (Reguler → Karyawan) berhasil.
     */
    public function test_pindah_kelas_lintas_jenis_berhasil(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.data-mahasiswa.pindah-kelas'), [
                'ids' => [$this->mahasiswa->id],
                'kelas_id' => $this->kelasKaryawan->id,
                'source_kelas_id' => $this->kelasReguler->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mahasiswas', [
            'id' => $this->mahasiswa->id,
            'kelas_id' => $this->kelasKaryawan->id,
        ]);

        // Flash message seharusnya menyebut "Karyawan"
        $response->assertSessionHas('success');
        $this->assertStringContainsString(
            'Karyawan',
            session('success')
        );
    }

    /**
     * Test pindah kelas ke prodi berbeda harus ditolak.
     */
    public function test_pindah_kelas_lintas_prodi_ditolak(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.data-mahasiswa.pindah-kelas'), [
                'ids' => [$this->mahasiswa->id],
                'kelas_id' => $this->kelasLainProdi->id,
                'source_kelas_id' => $this->kelasReguler->id,
            ]);

        $response->assertSessionHasErrors('kelas_id');

        // Data mahasiswa tidak berubah
        $this->assertDatabaseHas('mahasiswas', [
            'id' => $this->mahasiswa->id,
            'kelas_id' => $this->kelasReguler->id,
        ]);
    }

    /**
     * Test ids kosong harus ditolak validasi.
     */
    public function test_pindah_kelas_ids_kosong_ditolak(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.data-mahasiswa.pindah-kelas'), [
                'ids' => [],
                'kelas_id' => $this->kelasKaryawan->id,
                'source_kelas_id' => $this->kelasReguler->id,
            ]);

        $response->assertSessionHasErrors('ids');
    }

    /**
     * Test kelas_id yang tidak ada di database harus ditolak validasi.
     */
    public function test_pindah_kelas_target_tidak_valid_ditolak(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.data-mahasiswa.pindah-kelas'), [
                'ids' => [$this->mahasiswa->id],
                'kelas_id' => 99999,
                'source_kelas_id' => $this->kelasReguler->id,
            ]);

        $response->assertSessionHasErrors('kelas_id');
    }
}
