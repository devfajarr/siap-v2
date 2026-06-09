<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\TahunAkademik;
use App\Services\FeederTokenService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Feature tests for FeederTokenService and RenewFeederToken command.
 */
class FeederTokenServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Clear Cache before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('feeder_token');
    }

    /**
     * Test successful token retrieval and caching.
     */
    public function test_get_token_success(): void
    {
        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    'token' => 'mocked-jwt-token-123',
                ],
            ], 200),
        ]);

        $service = new FeederTokenService;
        $token = $service->getToken();

        $this->assertEquals('mocked-jwt-token-123', $token);
        $this->assertTrue(Cache::has('feeder_token'));
        $this->assertEquals('mocked-jwt-token-123', Cache::get('feeder_token'));
    }

    /**
     * Test that token is retrieved from cache on subsequent calls.
     */
    public function test_get_token_cached(): void
    {
        Cache::put('feeder_token', 'cached-token-abc', 60);

        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $service = new FeederTokenService;
        $token = $service->getToken();

        $this->assertEquals('cached-token-abc', $token);
    }

    /**
     * Test that force refresh bypasses cache and updates it.
     */
    public function test_get_token_force_refresh(): void
    {
        Cache::put('feeder_token', 'old-token', 60);

        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    'token' => 'new-token',
                ],
            ], 200),
        ]);

        $service = new FeederTokenService;
        $token = $service->getToken(true);

        $this->assertEquals('new-token', $token);
        $this->assertEquals('new-token', Cache::get('feeder_token'));
    }

    /**
     * Test token retrieval API failure response.
     */
    public function test_get_token_api_failure(): void
    {
        Http::fake([
            '*/ws/sandbox2.php' => Http::response('Server Error', 500),
        ]);

        $service = new FeederTokenService;
        $token = $service->getToken();

        $this->assertNull($token);
        $this->assertFalse(Cache::has('feeder_token'));
    }

    /**
     * Test token retrieval when API returns Postgres fatal connection error.
     */
    public function test_get_token_api_postgres_error(): void
    {
        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'length' => 100,
                'name' => 'error',
                'severity' => 'FATAL',
                'code' => '3D000',
                'file' => 'postinit.c',
                'line' => 848,
                'routine' => 'InitPostgres',
            ], 200),
        ]);

        $service = new FeederTokenService;
        $token = $service->getToken();

        $this->assertNull($token);
        $this->assertFalse(Cache::has('feeder_token'));
    }

    /**
     * Test successful GetDictionary query using valid token.
     */
    public function test_get_dictionary_success(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);

        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    'fields' => ['kode_prodi', 'nama_prodi'],
                ],
            ], 200),
        ]);

        $service = new FeederTokenService;
        $result = $service->getDictionary('GetPeriode');

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['data']['error_code']);
    }

    /**
     * Test GetDictionary query when token cannot be retrieved.
     */
    public function test_get_dictionary_failure(): void
    {
        Http::fake([
            '*/ws/sandbox2.php' => Http::response('Unauthenticated', 401),
        ]);

        $service = new FeederTokenService;
        $result = $service->getDictionary('GetPeriode');

        $this->assertFalse($result['success']);
        $this->assertEquals('Failed to retrieve a valid API token.', $result['message']);
    }

    /**
     * Test Artisan command successfully renews the token.
     */
    public function test_artisan_command_success(): void
    {
        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    'token' => 'renewed-by-artisan',
                ],
            ], 200),
        ]);

        $exitCode = Artisan::call('feeder:renew-token');

        $this->assertEquals(0, $exitCode);
        $this->assertEquals('renewed-by-artisan', Cache::get('feeder_token'));
    }

    /**
     * Test Artisan command failure when token retrieval fails.
     */
    public function test_artisan_command_failure(): void
    {
        Http::fake([
            '*/ws/sandbox2.php' => Http::response('Server Error', 500),
        ]);

        $exitCode = Artisan::call('feeder:renew-token');

        $this->assertEquals(1, $exitCode);
    }

    /**
     * Test Artisan command dictionary success.
     */
    public function test_artisan_command_dictionary_success(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);

        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    'fields' => ['kode_prodi', 'nama_prodi'],
                ],
            ], 200),
        ]);

        $exitCode = Artisan::call('feeder:dictionary', ['fungsi' => 'GetPeriode']);

        $this->assertEquals(0, $exitCode);
    }

    /**
     * Test Artisan command dictionary failure.
     */
    public function test_artisan_command_dictionary_failure(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);

        Http::fake([
            '*/ws/sandbox2.php' => Http::response('Server Error', 500),
        ]);

        $exitCode = Artisan::call('feeder:dictionary', ['fungsi' => 'GetPeriode']);

        $this->assertEquals(1, $exitCode);
    }

    /**
     * Test pulling mahasiswas batch and successfully matching/updating local records.
     */
    public function test_pull_mahasiswas_match_success(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);
        Cache::put('feeder_active_period_id', '20241', 60);

        $prodi = Prodi::firstOrCreate(
            ['nama_prodi' => 'Teknik Informatika'],
            [
                'singkatan' => 'TI',
                'kode_prodi' => 'TI101',
                'jenjang' => 'D3',
                'alias_nama' => 'Information Technical',
                'alias_jenjang' => 'Diploma Three',
            ]
        );
        $semester = Semester::firstOrCreate(['semester' => 1], ['status' => 1]);
        $kelas = Kelas::firstOrCreate(
            ['kode_kelas' => '99999'],
            [
                'nama_kelas' => 'Test Kelas',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );

        $mahasiswa = Mahasiswa::create([
            'nama_lengkap' => 'Matching Student',
            'tahun_masuk' => '2024',
            'nim' => 'NIM-MATCH-123',
            'nisn' => '1234567890',
            'nik' => '1234567890123456',
            'email' => 'match@test.com',
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Test 1',
            'no_telephone' => '081234567890',
            'tanggal_lahir' => '2004-01-01',
            'tempat_lahir' => 'Jakarta',
            'nama_ibu' => 'Ibu Kandung',
            'jenis_kelamin' => 'Laki-Laki',
            'kelas_id' => $kelas->id,
        ]);

        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    [
                        'id_mahasiswa' => 'feeder-student-uuid',
                        'id_registrasi_mahasiswa' => 'feeder-reg-uuid',
                        'nim' => 'NIM-MATCH-123',
                        'nama_mahasiswa' => 'Matching Student',
                        'nama_program_studi' => 'Teknik Informatika',
                        'id_periode_masuk' => '20241',
                    ],
                ],
            ], 200),
        ]);

        $service = new FeederTokenService;
        $result = $service->pullMahasiswas('', 10, 0, false);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['records_count']);
        $this->assertEquals(1, $result['stats']['matched']);

        $mahasiswa->refresh();
        $this->assertEquals('feeder-student-uuid', $mahasiswa->feeder_id_mahasiswa);
        $this->assertEquals('feeder-reg-uuid', $mahasiswa->feeder_id_registrasi);
    }

    /**
     * Test pulling mahasiswas batch and successfully creating new local records.
     */
    public function test_pull_mahasiswas_create_new_success(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);
        Cache::put('feeder_active_period_id', '20241', 60);

        $prodi = Prodi::firstOrCreate(
            ['nama_prodi' => 'Teknik Informatika'],
            [
                'singkatan' => 'TI',
                'kode_prodi' => 'TI101',
                'jenjang' => 'D3',
                'alias_nama' => 'Information Technical',
                'alias_jenjang' => 'Diploma Three',
            ]
        );
        $semester = Semester::firstOrCreate(['semester' => 1], ['status' => 1]);
        $kelas = Kelas::firstOrCreate(
            ['kode_kelas' => '99999'],
            [
                'nama_kelas' => 'Test Kelas',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );

        Http::fake([
            '*/ws/sandbox2.php' => function (Request $request) {
                $body = json_decode($request->body(), true);
                if ($body['act'] === 'GetListRiwayatPendidikanMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_mahasiswa' => 'new-student-uuid',
                                'id_registrasi_mahasiswa' => 'new-reg-uuid',
                                'nim' => 'NIM-NEW-999',
                                'nama_mahasiswa' => 'New Student',
                                'nama_program_studi' => 'Teknik Informatika',
                                'id_periode_masuk' => '20241',
                            ],
                        ],
                    ], 200);
                }

                if ($body['act'] === 'GetBiodataMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_mahasiswa' => 'new-student-uuid',
                                'nik' => '9991234567890123',
                                'nisn' => '99912345',
                                'jalan' => 'Jl. Baru',
                                'kelurahan' => 'Kelurahan Baru',
                                'email' => 'new@test.com',
                                'handphone' => '08129999999',
                                'tanggal_lahir' => '2004-05-05',
                                'tempat_lahir' => 'Surabaya',
                                'nama_ibu_kandung' => 'Ibu Baru',
                                'jenis_kelamin' => 'P',
                            ],
                        ],
                    ], 200);
                }

                return Http::response([], 404);
            },
        ]);

        $service = new FeederTokenService;
        $result = $service->pullMahasiswas('', 10, 0, true);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['records_count']);
        $this->assertEquals(1, $result['stats']['created']);

        $newMahasiswa = Mahasiswa::where('nim', 'NIM-NEW-999')->first();
        $this->assertNotNull($newMahasiswa);
        $this->assertEquals('New Student', $newMahasiswa->nama_lengkap);
        $this->assertEquals('9991234567890123', $newMahasiswa->nik);
        $this->assertEquals('Perempuan', $newMahasiswa->jenis_kelamin);
        $this->assertEquals('new-student-uuid', $newMahasiswa->feeder_id_mahasiswa);
    }

    /**
     * Test pulling prodis batch and successfully matching/updating local records.
     */
    public function test_pull_prodis_match_success(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);

        $prodi = Prodi::create([
            'nama_prodi' => 'Teknik Informatika',
            'singkatan' => 'TI',
            'kode_prodi' => 'TI101',
            'jenjang' => 'D3',
            'alias_nama' => 'Information Technical',
            'alias_jenjang' => 'Diploma Three',
        ]);

        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    [
                        'id_prodi' => 'prodi-uuid-123',
                        'kode_program_studi' => 'TI101',
                        'nama_program_studi' => 'Teknik Informatika',
                        'nama_jenjang_pendidikan' => 'D3',
                    ],
                ],
            ], 200),
        ]);

        $service = new FeederTokenService;
        $result = $service->pullProdis('', 10, 0);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['records_count']);
        $this->assertEquals(1, $result['stats']['matched']);

        $prodi->refresh();
        $this->assertEquals('prodi-uuid-123', $prodi->feeder_id_prodi);
    }

    /**
     * Test pulling prodis batch and successfully creating new local records with fallback logic.
     */
    public function test_pull_prodis_create_new_success(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);

        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    [
                        'id_prodi' => 'prodi-uuid-new',
                        'kode_program_studi' => 'AK999',
                        'nama_program_studi' => 'Akuntansi Baru',
                        'nama_jenjang_pendidikan' => 'S1',
                    ],
                ],
            ], 200),
        ]);

        $service = new FeederTokenService;
        $result = $service->pullProdis('', 10, 0);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['records_count']);
        $this->assertEquals(1, $result['stats']['created']);

        $newProdi = Prodi::where('kode_prodi', 'AK999')->first();
        $this->assertNotNull($newProdi);
        $this->assertEquals('Akuntansi Baru', $newProdi->nama_prodi);
        $this->assertEquals('AB', $newProdi->singkatan); // uppercase initials of "Akuntansi Baru"
        $this->assertEquals('Akuntansi Baru', $newProdi->alias_nama);
        $this->assertEquals('Bachelor Degree', $newProdi->alias_jenjang);
        $this->assertEquals('prodi-uuid-new', $newProdi->feeder_id_prodi);
    }

    /**
     * Test successful synchronization of active academic period.
     */
    public function test_sync_active_period_success(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);

        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    [
                        'id_semester' => '20241',
                        'id_tahun_ajaran' => '2024',
                        'nama_semester' => '2024/2025 Ganjil',
                        'semester' => '1',
                        'a_periode_aktif' => '1',
                    ],
                ],
            ], 200),
        ]);

        // Seed some academic years
        TahunAkademik::firstOrCreate(['tahun_akademik' => '2024/2025'], ['status' => 0]);
        TahunAkademik::firstOrCreate(['tahun_akademik' => '2023/2024'], ['status' => 1]);

        $service = new FeederTokenService;
        $result = $service->syncActivePeriod();

        $this->assertTrue($result['success']);
        $this->assertEquals('20241', $result['id_semester']);
        $this->assertEquals('2024/2025', $result['tahun_akademik']);

        // Verify local DB status
        $this->assertEquals(1, TahunAkademik::where('tahun_akademik', '2024/2025')->first()->status);
        $this->assertEquals(0, TahunAkademik::where('tahun_akademik', '2023/2024')->first()->status);
        $this->assertEquals('20241', Cache::get('feeder_active_period_id'));
    }

    /**
     * Test pulling mahasiswas with status and exit details.
     */
    public function test_pull_mahasiswas_with_status_and_exit_details(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);
        Cache::put('feeder_active_period_id', '20241', 60);

        $prodi = Prodi::firstOrCreate(
            ['nama_prodi' => 'Teknik Informatika'],
            [
                'singkatan' => 'TI',
                'kode_prodi' => 'TI101',
                'jenjang' => 'D3',
                'alias_nama' => 'Information Technical',
                'alias_jenjang' => 'Diploma Three',
                'feeder_id_prodi' => 'prodi-feeder-123',
            ]
        );
        $semester = Semester::firstOrCreate(['semester' => 3], ['status' => 1]);
        $kelas = Kelas::firstOrCreate(
            ['kode_kelas' => '99998'],
            [
                'nama_kelas' => 'Test Kelas Level 3',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );

        $mahasiswa = Mahasiswa::create([
            'nama_lengkap' => 'Graduating Student',
            'tahun_masuk' => '2023',
            'nim' => 'NIM-GRAD-777',
            'nisn' => '77745678',
            'nik' => '7774567890123456',
            'email' => 'grad@test.com',
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Grad 1',
            'no_telephone' => '081277777777',
            'tanggal_lahir' => '2004-01-01',
            'tempat_lahir' => 'Jakarta',
            'nama_ibu' => 'Ibu Kandung',
            'jenis_kelamin' => 'Laki-Laki',
            'kelas_id' => $kelas->id,
        ]);

        Http::fake([
            '*/ws/sandbox2.php' => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    [
                        'id_mahasiswa' => 'grad-student-uuid',
                        'id_registrasi_mahasiswa' => 'grad-reg-uuid',
                        'nim' => 'NIM-GRAD-777',
                        'nama_mahasiswa' => 'Graduating Student',
                        'id_prodi' => 'prodi-feeder-123',
                        'nama_program_studi' => 'Teknik Informatika',
                        'id_periode_masuk' => '20231', // level: (2024-2023)*2 + (1-1) + 1 = 3
                        'id_jenis_keluar' => '1',
                        'keterangan_keluar' => 'Lulus',
                        'tanggal_keluar' => '30-05-2025',
                    ],
                ],
            ], 200),
        ]);

        $service = new FeederTokenService;
        $result = $service->pullMahasiswas('', 10, 0, false);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['records_count']);

        $mahasiswa->refresh();
        $this->assertEquals('Lulus', $mahasiswa->status_mahasiswa);
        $this->assertEquals('1', $mahasiswa->id_jenis_keluar);
        $this->assertEquals('2025-05-30', $mahasiswa->tanggal_keluar);
        $this->assertEquals('20231', $mahasiswa->id_periode_masuk);
        $this->assertEquals($kelas->id, $mahasiswa->kelas_id);
    }

    /**
     * Test mapping new student with NIM digit 5 = 1 to Reguler class.
     */
    public function test_pull_mahasiswas_nim_digit5_reguler(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);
        Cache::put('feeder_active_period_id', '20241', 60);

        $prodi = Prodi::firstOrCreate(
            ['nama_prodi' => 'Teknik Informatika'],
            [
                'singkatan' => 'TI',
                'kode_prodi' => 'TI101',
                'jenjang' => 'D3',
                'alias_nama' => 'Information Technical',
                'alias_jenjang' => 'Diploma Three',
                'feeder_id_prodi' => 'prodi-feeder-123',
            ]
        );
        $semester = Semester::firstOrCreate(['semester' => 1], ['status' => 1]);
        $kelasReguler = Kelas::firstOrCreate(
            ['kode_kelas' => '99991'],
            [
                'nama_kelas' => 'Test Kelas Reguler',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );
        $kelasKaryawan = Kelas::firstOrCreate(
            ['kode_kelas' => '99992'],
            [
                'nama_kelas' => 'Test Kelas Karyawan',
                'jenis_kelas' => 'Karyawan',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );

        Http::fake([
            '*/ws/sandbox2.php' => function (Request $request) {
                $body = json_decode($request->body(), true);
                if ($body['act'] === 'GetListRiwayatPendidikanMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_mahasiswa' => 'stud-reg-uuid',
                                'id_registrasi_mahasiswa' => 'reg-reg-uuid',
                                'nim' => '202410001', // Digit 5 = 1 (Reguler)
                                'nama_mahasiswa' => 'Student Reguler',
                                'id_prodi' => 'prodi-feeder-123',
                                'nama_program_studi' => 'Teknik Informatika',
                                'id_periode_masuk' => '20241',
                            ],
                        ],
                    ], 200);
                }

                if ($body['act'] === 'GetBiodataMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_mahasiswa' => 'stud-reg-uuid',
                                'nik' => '123',
                                'nisn' => '123',
                                'email' => 'reg@test.com',
                                'tanggal_lahir' => '2004-01-01',
                            ],
                        ],
                    ], 200);
                }

                return Http::response([], 404);
            },
        ]);

        $service = new FeederTokenService;
        $result = $service->pullMahasiswas('', 10, 0, true);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['stats']['created']);

        $mahasiswa = Mahasiswa::where('nim', '202410001')->first();
        $this->assertNotNull($mahasiswa);
        $this->assertEquals($kelasReguler->id, $mahasiswa->kelas_id);
    }

    /**
     * Test mapping new student with NIM digit 5 = 2 to Karyawan class.
     */
    public function test_pull_mahasiswas_nim_digit5_karyawan(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);
        Cache::put('feeder_active_period_id', '20241', 60);

        $prodi = Prodi::firstOrCreate(
            ['nama_prodi' => 'Teknik Informatika'],
            [
                'singkatan' => 'TI',
                'kode_prodi' => 'TI101',
                'jenjang' => 'D3',
                'alias_nama' => 'Information Technical',
                'alias_jenjang' => 'Diploma Three',
                'feeder_id_prodi' => 'prodi-feeder-123',
            ]
        );
        $semester = Semester::firstOrCreate(['semester' => 1], ['status' => 1]);
        $kelasReguler = Kelas::firstOrCreate(
            ['kode_kelas' => '99991'],
            [
                'nama_kelas' => 'Test Kelas Reguler',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );
        $kelasKaryawan = Kelas::firstOrCreate(
            ['kode_kelas' => '99992'],
            [
                'nama_kelas' => 'Test Kelas Karyawan',
                'jenis_kelas' => 'Karyawan',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );

        Http::fake([
            '*/ws/sandbox2.php' => function (Request $request) {
                $body = json_decode($request->body(), true);
                if ($body['act'] === 'GetListRiwayatPendidikanMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_mahasiswa' => 'stud-kary-uuid',
                                'id_registrasi_mahasiswa' => 'reg-kary-uuid',
                                'nim' => '202420001', // Digit 5 = 2 (Karyawan)
                                'nama_mahasiswa' => 'Student Karyawan',
                                'id_prodi' => 'prodi-feeder-123',
                                'nama_program_studi' => 'Teknik Informatika',
                                'id_periode_masuk' => '20241',
                            ],
                        ],
                    ], 200);
                }

                if ($body['act'] === 'GetBiodataMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_mahasiswa' => 'stud-kary-uuid',
                                'nik' => '456',
                                'nisn' => '456',
                                'email' => 'kary@test.com',
                                'tanggal_lahir' => '2004-01-01',
                            ],
                        ],
                    ], 200);
                }

                return Http::response([], 404);
            },
        ]);

        $service = new FeederTokenService;
        $result = $service->pullMahasiswas('', 10, 0, true);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['stats']['created']);

        $mahasiswa = Mahasiswa::where('nim', '202420001')->first();
        $this->assertNotNull($mahasiswa);
        $this->assertEquals($kelasKaryawan->id, $mahasiswa->kelas_id);
    }

    /**
     * Test mapping new student with NIM digit 5 != 1 or 2 defaults to Reguler class.
     */
    public function test_pull_mahasiswas_nim_digit5_default(): void
    {
        Cache::put('feeder_token', 'cached-token-123', 60);
        Cache::put('feeder_active_period_id', '20241', 60);

        $prodi = Prodi::firstOrCreate(
            ['nama_prodi' => 'Teknik Informatika'],
            [
                'singkatan' => 'TI',
                'kode_prodi' => 'TI101',
                'jenjang' => 'D3',
                'alias_nama' => 'Information Technical',
                'alias_jenjang' => 'Diploma Three',
                'feeder_id_prodi' => 'prodi-feeder-123',
            ]
        );
        $semester = Semester::firstOrCreate(['semester' => 1], ['status' => 1]);
        $kelasReguler = Kelas::firstOrCreate(
            ['kode_kelas' => '99991'],
            [
                'nama_kelas' => 'Test Kelas Reguler',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );
        $kelasKaryawan = Kelas::firstOrCreate(
            ['kode_kelas' => '99992'],
            [
                'nama_kelas' => 'Test Kelas Karyawan',
                'jenis_kelas' => 'Karyawan',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );

        Http::fake([
            '*/ws/sandbox2.php' => function (Request $request) {
                $body = json_decode($request->body(), true);
                if ($body['act'] === 'GetListRiwayatPendidikanMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_mahasiswa' => 'stud-other-uuid',
                                'id_registrasi_mahasiswa' => 'reg-other-uuid',
                                'nim' => '202490001', // Digit 5 = 9 (Other/Default)
                                'nama_mahasiswa' => 'Student Other',
                                'id_prodi' => 'prodi-feeder-123',
                                'nama_program_studi' => 'Teknik Informatika',
                                'id_periode_masuk' => '20241',
                            ],
                        ],
                    ], 200);
                }

                if ($body['act'] === 'GetBiodataMahasiswa') {
                    return Http::response([
                        'error_code' => 0,
                        'error_desc' => '',
                        'data' => [
                            [
                                'id_mahasiswa' => 'stud-other-uuid',
                                'nik' => '789',
                                'nisn' => '789',
                                'email' => 'other@test.com',
                                'tanggal_lahir' => '2004-01-01',
                            ],
                        ],
                    ], 200);
                }

                return Http::response([], 404);
            },
        ]);

        $service = new FeederTokenService;
        $result = $service->pullMahasiswas('', 10, 0, true);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['stats']['created']);

        $mahasiswa = Mahasiswa::where('nim', '202490001')->first();
        $this->assertNotNull($mahasiswa);
        $this->assertEquals($kelasReguler->id, $mahasiswa->kelas_id);
    }
}
