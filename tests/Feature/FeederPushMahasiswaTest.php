<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Semester;
use App\Services\FeederTokenService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FeederPushMahasiswaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('feeder_token');
    }

    /**
     * Helper to create test entities.
     */
    protected function createTestStudent(string $nim, string $nik = '1234567890123456'): Mahasiswa
    {
        $prodi = Prodi::firstOrCreate(
            ['kode_prodi' => 'TI'],
            [
                'nama_prodi' => 'Teknik Informatika',
                'singkatan' => 'TI',
                'jenjang' => 'D3',
                'feeder_id_prodi' => 'prodi-uuid-feeder',
                'alias_nama' => 'Information Technical',
                'alias_jenjang' => 'Diploma Three',
            ]
        );

        $semester = Semester::firstOrCreate(['semester' => 1], ['status' => 1]);

        $kelas = Kelas::firstOrCreate(
            ['kode_kelas' => 'TI-2023-A'],
            [
                'nama_kelas' => 'TI-2023-A',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );

        return Mahasiswa::create([
            'nama_lengkap' => 'Test Student Push',
            'tahun_masuk' => '2023',
            'nim' => $nim,
            'nisn' => '12345678',
            'nik' => $nik,
            'email' => $nim.'@sawunggalih.ac.id',
            'password' => bcrypt('password'),
            'alamat' => 'Jl. Test Push No 1',
            'no_telephone' => '081234567890',
            'tanggal_lahir' => '2004-01-01',
            'tempat_lahir' => 'Purworejo',
            'nama_ibu' => 'Ibu Kandung',
            'jenis_kelamin' => 'Laki-Laki',
            'kelas_id' => $kelas->id,
            'status_mahasiswa' => 'Aktif',
        ]);
    }

    /**
     * Test successful push of a new student (insert biodata + insert registration).
     */
    public function test_push_mahasiswa_full_success(): void
    {
        Cache::put('feeder_token', 'mocked-token', 60);
        $student = $this->createTestStudent('210102001', '1111122222333334');

        Http::fake([
            '*/ws/sandbox2.php' => function (Request $request) {
                $body = json_decode($request->body(), true);

                if ($body['act'] === 'GetBiodataMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [], // Empty means not exists in Feeder yet
                    ], 200);
                }

                if ($body['act'] === 'InsertBiodataMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            'id_mahasiswa' => 'new-feeder-mahasiswa-uuid',
                        ],
                    ], 200);
                }

                if ($body['act'] === 'GetListRiwayatPendidikanMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [],
                    ], 200);
                }

                if ($body['act'] === 'InsertRiwayatPendidikanMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            'id_registrasi_mahasiswa' => 'new-feeder-registrasi-uuid',
                        ],
                    ], 200);
                }

                return Http::response([], 404);
            },
        ]);

        $service = new FeederTokenService;
        $result = $service->pushMahasiswaToFeeder($student);

        $this->assertTrue($result['success']);
        $this->assertEquals('new-feeder-mahasiswa-uuid', $result['id_mahasiswa']);
        $this->assertEquals('new-feeder-registrasi-uuid', $result['id_registrasi_mahasiswa']);

        // Check local DB is updated
        $student->refresh();
        $this->assertEquals('new-feeder-mahasiswa-uuid', $student->feeder_id_mahasiswa);
        $this->assertEquals('new-feeder-registrasi-uuid', $student->feeder_id_registrasi);
    }

    /**
     * Test push of a student where biodata already exists in Feeder.
     */
    public function test_push_mahasiswa_biodata_exists(): void
    {
        Cache::put('feeder_token', 'mocked-token', 60);
        $student = $this->createTestStudent('210102002', '1111122222333335');

        Http::fake([
            '*/ws/sandbox2.php' => function (Request $request) {
                $body = json_decode($request->body(), true);

                if ($body['act'] === 'GetBiodataMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_mahasiswa' => 'existing-feeder-mahasiswa-uuid',
                                'nik' => '1111122222333335',
                            ],
                        ],
                    ], 200);
                }

                if ($body['act'] === 'GetListRiwayatPendidikanMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [],
                    ], 200);
                }

                if ($body['act'] === 'InsertRiwayatPendidikanMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            'id_registrasi_mahasiswa' => 'new-feeder-registrasi-uuid',
                        ],
                    ], 200);
                }

                return Http::response([], 404);
            },
        ]);

        $service = new FeederTokenService;
        $result = $service->pushMahasiswaToFeeder($student);

        $this->assertTrue($result['success']);
        $this->assertEquals('existing-feeder-mahasiswa-uuid', $result['id_mahasiswa']);
        $this->assertEquals('new-feeder-registrasi-uuid', $result['id_registrasi_mahasiswa']);

        $student->refresh();
        $this->assertEquals('existing-feeder-mahasiswa-uuid', $student->feeder_id_mahasiswa);
        $this->assertEquals('new-feeder-registrasi-uuid', $student->feeder_id_registrasi);
    }

    /**
     * Test push of a student where both biodata and registration already exist.
     */
    public function test_push_mahasiswa_already_fully_exists(): void
    {
        Cache::put('feeder_token', 'mocked-token', 60);
        $student = $this->createTestStudent('210102003', '1111122222333336');

        Http::fake([
            '*/ws/sandbox2.php' => function (Request $request) {
                $body = json_decode($request->body(), true);

                if ($body['act'] === 'GetBiodataMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_mahasiswa' => 'existing-feeder-mahasiswa-uuid',
                                'nik' => '1111122222333336',
                            ],
                        ],
                    ], 200);
                }

                if ($body['act'] === 'GetListRiwayatPendidikanMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_registrasi_mahasiswa' => 'existing-feeder-registrasi-uuid',
                                'nim' => '210102003',
                            ],
                        ],
                    ], 200);
                }

                return Http::response([], 404);
            },
        ]);

        $service = new FeederTokenService;
        $result = $service->pushMahasiswaToFeeder($student);

        $this->assertTrue($result['success']);
        $this->assertEquals('existing-feeder-mahasiswa-uuid', $result['id_mahasiswa']);
        $this->assertEquals('existing-feeder-registrasi-uuid', $result['id_registrasi_mahasiswa']);

        $student->refresh();
        $this->assertEquals('existing-feeder-mahasiswa-uuid', $student->feeder_id_mahasiswa);
        $this->assertEquals('existing-feeder-registrasi-uuid', $student->feeder_id_registrasi);
    }

    /**
     * Test push failure when InsertBiodataMahasiswa fails.
     */
    public function test_push_mahasiswa_biodata_insert_failure(): void
    {
        Cache::put('feeder_token', 'mocked-token', 60);
        $student = $this->createTestStudent('210102004', '1111122222333337');

        Http::fake([
            '*/ws/sandbox2.php' => function (Request $request) {
                $body = json_decode($request->body(), true);

                if ($body['act'] === 'GetBiodataMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [],
                    ], 200);
                }

                if ($body['act'] === 'InsertBiodataMahasiswa') {
                    return Http::response([
                        'error_code' => 101,
                        'error_desc' => 'NIK Duplikat atau Salah',
                    ], 200);
                }

                return Http::response([], 404);
            },
        ]);

        $service = new FeederTokenService;
        $result = $service->pushMahasiswaToFeeder($student);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Gagal membuat biodata', $result['message']);
    }

    /**
     * Test Artisan command helper.
     */
    public function test_push_mahasiswa_artisan_command(): void
    {
        Cache::put('feeder_token', 'mocked-token', 60);
        $student = $this->createTestStudent('210102999', '9999122222333337');

        Http::fake([
            '*/ws/sandbox2.php' => function (Request $request) {
                $body = json_decode($request->body(), true);
                if ($body['act'] === 'GetBiodataMahasiswa') {
                    return Http::response(['error_code' => 0, 'data' => [['id_mahasiswa' => 'cmd-mhs-uuid']]], 200);
                }
                if ($body['act'] === 'GetListRiwayatPendidikanMahasiswa') {
                    return Http::response(['error_code' => 0, 'data' => [['id_registrasi_mahasiswa' => 'cmd-reg-uuid']]], 200);
                }

                return Http::response([], 404);
            },
        ]);

        $exitCode = Artisan::call('feeder:push-mahasiswa', ['nim' => '210102999']);

        $this->assertEquals(0, $exitCode);
        $student->refresh();
        $this->assertEquals('cmd-mhs-uuid', $student->feeder_id_mahasiswa);
        $this->assertEquals('cmd-reg-uuid', $student->feeder_id_registrasi);
    }
}
