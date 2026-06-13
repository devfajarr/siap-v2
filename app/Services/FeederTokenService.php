<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Matkul;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to handle Neo Feeder token operations.
 */
class FeederTokenService
{
    /**
     * Retrieve a valid authentication token.
     */
    public function getToken(bool $forceRefresh = false): ?string
    {
        $cacheKey = 'feeder_token';

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return (string) Cache::get($cacheKey);
        }

        try {
            $baseUrl = (string) config('services.feeder.base_url');
            $username = (string) config('services.feeder.username');
            $password = (string) config('services.feeder.password');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($baseUrl, [
                'act' => 'GetToken',
                'username' => $username,
                'password' => $password,
            ]);

            if (! $response->successful()) {
                Log::error('Feeder Token API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            if (! isset($data['data']['token'])) {
                Log::error('Feeder Token not found in API response', [
                    'response' => $data,
                ]);

                return null;
            }

            $token = (string) $data['data']['token'];

            $now = Carbon::now();
            $endOfDay = Carbon::now()->endOfDay();
            $ttl = max(1, $now->diffInSeconds($endOfDay));

            Cache::put($cacheKey, $token, $ttl);

            return $token;
        } catch (\Throwable $e) {
            Log::error('Error fetching Feeder Token: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Retrieve the dictionary of a specific Neo Feeder function.
     */
    public function getDictionary(string $fungsi): array
    {
        $token = $this->getToken();

        if (! $token) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve a valid API token.',
            ];
        }

        try {
            $baseUrl = (string) config('services.feeder.base_url');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($baseUrl, [
                'act' => 'GetDictionary',
                'token' => $token,
                'fungsi' => $fungsi,
            ]);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'API returned status '.$response->status(),
                ];
            }

            return [
                'success' => true,
                'data' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Error calling GetDictionary: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Pull a batch of mahasiswa records from the Feeder.
     */
    /**
     * Map a student to a class based on active period, entry period, and program study.
     */
    public function mapStudentToKelas(string $idProdiFeeder, string $idPeriodeMasuk, string $preferredJenisKelas = 'Reguler'): ?int
    {
        $prodi = Prodi::where('feeder_id_prodi', $idProdiFeeder)->first();
        if (! $prodi) {
            return null;
        }

        $activePeriodId = Cache::get('feeder_active_period_id');
        if (! $activePeriodId) {
            $syncRes = $this->syncActivePeriod();
            if ($syncRes['success']) {
                $activePeriodId = $syncRes['id_semester'];
            }
        }

        if (! $activePeriodId) {
            return null;
        }

        $yActive = (int) substr($activePeriodId, 0, 4);
        $sActive = (int) substr($activePeriodId, 4, 1);

        $yEntry = (int) substr($idPeriodeMasuk, 0, 4);
        $sEntry = (int) substr($idPeriodeMasuk, 4, 1);

        $semesterLevel = ($yActive - $yEntry) * 2 + ($sActive - $sEntry) + 1;

        if ($semesterLevel <= 0) {
            $semesterLevel = 1;
        }

        // Cap semester level based on program study degree type
        $maxSemester = match (strtoupper($prodi->jenjang ?? '')) {
            'D3' => 6,
            default => 8, // D4, S1, S2, etc.
        };

        if ($semesterLevel > $maxSemester) {
            return null;
        }

        $semester = Semester::where('semester', $semesterLevel)->first();
        if (! $semester) {
            return null;
        }

        $jenisKelas = stripos($preferredJenisKelas, 'Reguler') !== false ? 'Reguler' : 'Karyawan';

        $kelas = Kelas::where('id_prodi', $prodi->id)
            ->where('id_semester', $semester->id)
            ->where('jenis_kelas', $jenisKelas)
            ->first();

        return $kelas ? $kelas->id : null;
    }

    /**
     * Sync active period from Neo Feeder.
     */
    public function syncActivePeriod(): array
    {
        $token = $this->getToken();

        if (! $token) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve a valid API token.',
            ];
        }

        try {
            $baseUrl = (string) config('services.feeder.base_url');

            $response = Http::retry(3, 100)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($baseUrl, [
                    'act' => 'GetSemester',
                    'token' => $token,
                    'filter' => "a_periode_aktif = '1'",
                    'limit' => 5,
                    'offset' => 0,
                ]);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'API error: '.$response->status(),
                ];
            }

            $resData = $response->json();
            $records = $resData['data'] ?? [];

            if (empty($records)) {
                return [
                    'success' => false,
                    'message' => 'No active period found on Feeder.',
                ];
            }

            $activePeriod = null;
            foreach ($records as $record) {
                if (isset($record['semester']) && in_array((int) $record['semester'], [1, 2])) {
                    $activePeriod = $record;
                    break;
                }
            }

            if (! $activePeriod) {
                $activePeriod = $records[0];
            }

            $idSemester = $activePeriod['id_semester'];
            $namaSemester = $activePeriod['nama_semester'];

            $yearRange = '';
            if (preg_match('/(\d{4}\/\d{4})/', $namaSemester, $matches)) {
                $yearRange = $matches[1];
            } else {
                $year = $activePeriod['id_tahun_ajaran'];
                $yearRange = $year.'/'.($year + 1);
            }

            TahunAkademik::query()->update(['status' => 0]);

            $tahunAkademikObj = TahunAkademik::updateOrCreate(
                ['tahun_akademik' => $yearRange],
                ['status' => 1]
            );

            Cache::put('feeder_active_period_id', $idSemester, now()->addDays(7));

            return [
                'success' => true,
                'id_semester' => $idSemester,
                'tahun_akademik' => $yearRange,
                'model' => $tahunAkademikObj,
            ];
        } catch (\Throwable $e) {
            Log::error('Error in syncActivePeriod: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Pull a batch of mahasiswa records from the Feeder.
     */
    public function pullMahasiswas(string $filter = '', int $limit = 100, int $offset = 0, bool $createNew = false): array
    {
        $token = $this->getToken();

        if (! $token) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve a valid API token.',
            ];
        }

        try {
            $baseUrl = (string) config('services.feeder.base_url');
            $maxRetries = (int) config('services.feeder.sync.max_retries', 3);

            $response = Http::retry($maxRetries, 100)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($baseUrl, [
                    'act' => 'GetListRiwayatPendidikanMahasiswa',
                    'token' => $token,
                    'filter' => $filter,
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'API error: '.$response->status(),
                ];
            }

            $resData = $response->json();
            $records = $resData['data'] ?? [];

            if (empty($records)) {
                return [
                    'success' => true,
                    'records_count' => 0,
                    'stats' => ['matched' => 0, 'created' => 0, 'failed' => 0],
                ];
            }

            $matched = 0;
            $created = 0;
            $failed = 0;

            foreach ($records as $record) {
                if (empty($record['nim'])) {
                    $failed++;

                    continue;
                }

                $mahasiswa = Mahasiswa::where('nim', $record['nim'])->first();

                // Parse status kelulusan / keluar
                $statusMahasiswa = 'Aktif';
                if (! empty($record['id_jenis_keluar'])) {
                    $statusMahasiswa = $record['keterangan_keluar'] ?? 'Lulus';
                }

                // Format tanggal keluar YYYY-MM-DD
                $tanggalKeluar = null;
                if (! empty($record['tanggal_keluar'])) {
                    $rawDate = trim($record['tanggal_keluar']);
                    try {
                        if (preg_match('/^\d{2}[-\/]\d{2}[-\/]\d{4}$/', $rawDate)) {
                            $separator = strpos($rawDate, '-') !== false ? '-' : '/';
                            $tanggalKeluar = Carbon::createFromFormat('d'.$separator.'m'.$separator.'Y', $rawDate)->format('Y-m-d');
                        } else {
                            $tanggalKeluar = Carbon::parse($rawDate)->format('Y-m-d');
                        }
                    } catch (\Throwable $e) {
                        $tanggalKeluar = null;
                    }
                }

                // Tentukan preferred jenis kelas berdasarkan digit ke-5 NIM (index 4)
                $nim = $record['nim'] ?? '';
                $digit5 = strlen($nim) >= 5 ? substr($nim, 4, 1) : null;

                if ($digit5 === '2') {
                    $preferredJenisKelas = 'Karyawan';
                } elseif ($digit5 === '1') {
                    $preferredJenisKelas = 'Reguler';
                } else {
                    $preferredJenisKelas = ($mahasiswa && $mahasiswa->kelas)
                        ? $mahasiswa->kelas->jenis_kelas
                        : 'Reguler';
                }

                // Dynamic class mapping
                $kelasId = null;
                if (! empty($record['id_prodi']) && ! empty($record['id_periode_masuk'])) {
                    $kelasId = $this->mapStudentToKelas($record['id_prodi'], $record['id_periode_masuk'], $preferredJenisKelas);
                }

                if ($mahasiswa) {
                    $mahasiswa->update([
                        'feeder_id_mahasiswa' => $record['id_mahasiswa'],
                        'feeder_id_registrasi' => $record['id_registrasi_mahasiswa'],
                        'status_mahasiswa' => $statusMahasiswa,
                        'id_periode_masuk' => $record['id_periode_masuk'] ?? null,
                        'id_jenis_keluar' => $record['id_jenis_keluar'] ?? null,
                        'tanggal_keluar' => $tanggalKeluar,
                        'kelas_id' => $kelasId ?? $mahasiswa->kelas_id,
                    ]);
                    $matched++;
                } elseif ($createNew) {
                    try {
                        // Fetch complete biodata for new local student
                        $bioResponse = Http::retry($maxRetries, 100)
                            ->withHeaders(['Content-Type' => 'application/json'])
                            ->post($baseUrl, [
                                'act' => 'GetBiodataMahasiswa',
                                'token' => $token,
                                'filter' => "id_mahasiswa = '".$record['id_mahasiswa']."'",
                            ]);

                        $bioData = $bioResponse->json();
                        $bio = $bioData['data'][0] ?? [];

                        if (! $kelasId) {
                            $kelasId = null;
                        }

                        // Normalize tanggal_lahir
                        $tanggalLahir = $bio['tanggal_lahir'] ?? null;
                        if ($tanggalLahir) {
                            $tanggalLahir = trim($tanggalLahir);
                            if (preg_match('/^\d{2}[-\/]\d{2}[-\/]\d{4}$/', $tanggalLahir)) {
                                $separator = strpos($tanggalLahir, '-') !== false ? '-' : '/';
                                try {
                                    $tanggalLahir = Carbon::createFromFormat('d'.$separator.'m'.$separator.'Y', $tanggalLahir)->format('Y-m-d');
                                } catch (\Throwable $e) {
                                    $tanggalLahir = null;
                                }
                            }
                        }
                        if (empty($tanggalLahir)) {
                            $tanggalLahir = now()->format('Y-m-d');
                        }

                        Mahasiswa::create([
                            'nama_lengkap' => $record['nama_mahasiswa'] ?? 'Unknown Name',
                            'tahun_masuk' => isset($record['id_periode_masuk']) ? substr($record['id_periode_masuk'], 0, 4) : now()->format('Y'),
                            'nim' => $record['nim'],
                            'nisn' => $bio['nisn'] ?? '-',
                            'nik' => $bio['nik'] ?? '-',
                            'email' => $bio['email'] ?? ($record['nim'].'@test.com'),
                            'password' => Hash::make($record['nim']),
                            'alamat' => $bio['jalan'] ?? 'Alamat tidak diisi',
                            'no_telephone' => $bio['handphone'] ?? ($bio['telepon'] ?? '081234567890'),
                            'tanggal_lahir' => $tanggalLahir,
                            'tempat_lahir' => $bio['tempat_lahir'] ?? '-',
                            'nama_ibu' => $bio['nama_ibu_kandung'] ?? ($record['nama_ibu_kandung'] ?? '-'),
                            'jenis_kelamin' => ($bio['jenis_kelamin'] ?? 'L') === 'P' ? 'Perempuan' : 'Laki-Laki',
                            'kelas_id' => $kelasId,
                            'status_mahasiswa' => $statusMahasiswa,
                            'id_periode_masuk' => $record['id_periode_masuk'] ?? null,
                            'id_jenis_keluar' => $record['id_jenis_keluar'] ?? null,
                            'tanggal_keluar' => $tanggalKeluar,
                            'feeder_id_mahasiswa' => $record['id_mahasiswa'],
                            'feeder_id_registrasi' => $record['id_registrasi_mahasiswa'],
                        ]);

                        $created++;
                    } catch (\Throwable $e) {
                        Log::error("Failed to create student locally for NIM {$record['nim']}: ".$e->getMessage());
                        $failed++;
                    }
                } else {
                    $failed++;
                }
            }

            return [
                'success' => true,
                'records_count' => count($records),
                'stats' => [
                    'matched' => $matched,
                    'created' => $created,
                    'failed' => $failed,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Error pulling Mahasiswas batch: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Pull all mahasiswa records from the Feeder sequentially applying rate limit delays.
     */
    public function pullAllMahasiswas(bool $createNew = false, ?callable $progressCallback = null): array
    {
        $batchSize = (int) config('services.feeder.sync.batch_size', 100);
        $delayMs = (int) config('services.feeder.sync.delay_ms', 200);

        $offset = 0;
        $totalMatched = 0;
        $totalCreated = 0;
        $totalFailed = 0;
        $totalProcessed = 0;

        while (true) {
            $result = $this->pullMahasiswas('', $batchSize, $offset, $createNew);

            if (! $result['success']) {
                Log::error("Pull All stopped due to failure at offset {$offset}: ".($result['message'] ?? ''));
                break;
            }

            $count = $result['records_count'] ?? 0;
            if ($count === 0) {
                break;
            }

            $totalMatched += $result['stats']['matched'] ?? 0;
            $totalCreated += $result['stats']['created'] ?? 0;
            $totalFailed += $result['stats']['failed'] ?? 0;
            $totalProcessed += $count;

            if ($progressCallback) {
                $progressCallback($totalProcessed, $result['stats']);
            }

            if ($count < $batchSize) {
                break;
            }

            $offset += $batchSize;

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        return [
            'success' => true,
            'total_processed' => $totalProcessed,
            'stats' => [
                'matched' => $totalMatched,
                'created' => $totalCreated,
                'failed' => $totalFailed,
            ],
        ];
    }

    /**
     * Pull a batch of prodi records from the Feeder.
     */
    public function pullProdis(string $filter = '', int $limit = 100, int $offset = 0): array
    {
        $token = $this->getToken();

        if (! $token) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve a valid API token.',
            ];
        }

        try {
            $baseUrl = (string) config('services.feeder.base_url');
            $maxRetries = (int) config('services.feeder.sync.max_retries', 3);

            $response = Http::retry($maxRetries, 100)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($baseUrl, [
                    'act' => 'GetProdi',
                    'token' => $token,
                    'filter' => $filter,
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'API error: '.$response->status(),
                ];
            }

            $resData = $response->json();
            $records = $resData['data'] ?? [];

            if (empty($records)) {
                return [
                    'success' => true,
                    'records_count' => 0,
                    'stats' => ['matched' => 0, 'created' => 0, 'failed' => 0],
                ];
            }

            $matched = 0;
            $created = 0;
            $failed = 0;

            foreach ($records as $record) {
                if (empty($record['kode_program_studi'])) {
                    $failed++;

                    continue;
                }

                // Match by kode_prodi first, then fallback to name matching (for dummy seeded records)
                $prodi = Prodi::where('kode_prodi', $record['kode_program_studi'])->first();

                if (! $prodi && ! empty($record['nama_program_studi'])) {
                    $prodi = Prodi::where('nama_prodi', 'like', '%'.$record['nama_program_studi'].'%')->first();
                }

                if ($prodi) {
                    $prodi->update([
                        'feeder_id_prodi' => $record['id_prodi'],
                        'kode_prodi' => $record['kode_program_studi'] ?? $prodi->kode_prodi,
                        'nama_prodi' => $record['nama_program_studi'] ?? $prodi->nama_prodi,
                        'jenjang' => $record['nama_jenjang_pendidikan'] ?? $prodi->jenjang,
                    ]);
                    $matched++;
                } else {
                    // Create new prodi locally with generator fallbacks
                    try {
                        $namaProdi = $record['nama_program_studi'] ?? 'Unknown Prodi';

                        // Fallback 1: singkatan
                        // Generate from uppercase initials
                        $words = explode(' ', preg_replace('/\s+/', ' ', trim($namaProdi)));
                        $singkatan = '';
                        foreach ($words as $w) {
                            $char = substr($w, 0, 1);
                            if (ctype_upper($char) || is_numeric($char)) {
                                $singkatan .= $char;
                            } else {
                                $singkatan .= strtoupper($char);
                            }
                        }
                        if (empty($singkatan)) {
                            $singkatan = strtoupper(substr($namaProdi, 0, 3));
                        }

                        // Fallback 2: alias_nama
                        $aliasNama = $namaProdi; // Use name as default fallback

                        // Fallback 3: alias_jenjang
                        $jenjang = $record['nama_jenjang_pendidikan'] ?? 'D3';
                        $aliasJenjang = 'Diploma';
                        if (stripos($jenjang, 'D3') !== false) {
                            $aliasJenjang = 'Diploma Three';
                        } elseif (stripos($jenjang, 'D4') !== false) {
                            $aliasJenjang = 'Diploma Four';
                        } elseif (stripos($jenjang, 'S1') !== false) {
                            $aliasJenjang = 'Bachelor Degree';
                        } elseif (stripos($jenjang, 'S2') !== false) {
                            $aliasJenjang = 'Master Degree';
                        }

                        Prodi::create([
                            'feeder_id_prodi' => $record['id_prodi'],
                            'nama_prodi' => $namaProdi,
                            'singkatan' => $singkatan,
                            'kode_prodi' => $record['kode_program_studi'],
                            'jenjang' => $jenjang,
                            'alias_nama' => $aliasNama,
                            'alias_jenjang' => $aliasJenjang,
                        ]);

                        $created++;
                    } catch (\Throwable $e) {
                        Log::error("Failed to create prodi locally for Kode {$record['kode_program_studi']}: ".$e->getMessage());
                        $failed++;
                    }
                }
            }

            return [
                'success' => true,
                'records_count' => count($records),
                'stats' => [
                    'matched' => $matched,
                    'created' => $created,
                    'failed' => $failed,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Error pulling Prodis batch: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Pull all prodi records from the Feeder sequentially applying rate limit delays.
     */
    public function pullAllProdis(?callable $progressCallback = null): array
    {
        $batchSize = (int) config('services.feeder.sync.batch_size', 100);
        $delayMs = (int) config('services.feeder.sync.delay_ms', 200);

        $offset = 0;
        $totalMatched = 0;
        $totalCreated = 0;
        $totalFailed = 0;
        $totalProcessed = 0;

        while (true) {
            $result = $this->pullProdis('', $batchSize, $offset);

            if (! $result['success']) {
                Log::error("Pull All Prodis stopped due to failure at offset {$offset}: ".($result['message'] ?? ''));

                break;
            }

            $count = $result['records_count'] ?? 0;
            if ($count === 0) {
                break;
            }

            $totalMatched += $result['stats']['matched'] ?? 0;
            $totalCreated += $result['stats']['created'] ?? 0;
            $totalFailed += $result['stats']['failed'] ?? 0;
            $totalProcessed += $count;

            if ($progressCallback) {
                $progressCallback($totalProcessed, $result['stats']);
            }

            if ($count < $batchSize) {
                break;
            }

            $offset += $batchSize;

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        return [
            'success' => true,
            'total_processed' => $totalProcessed,
            'stats' => [
                'matched' => $totalMatched,
                'created' => $totalCreated,
                'failed' => $totalFailed,
            ],
        ];
    }

    /**
     * Call the Neo Feeder API.
     */
    public function feederRequest(string $act, array $params = []): array
    {
        try {
            $baseUrl = (string) config('services.feeder.base_url');
            $token = $this->getToken();

            if (! $token) {
                return [
                    'success' => false,
                    'message' => 'Token not available.',
                ];
            }

            $requestBody = array_merge([
                'act' => $act,
                'token' => $token,
            ], $params);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($baseUrl, $requestBody);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'HTTP Status: '.$response->status(),
                ];
            }

            $json = $response->json();
            if (isset($json['error_code']) && (int) $json['error_code'] !== 0) {
                return [
                    'success' => false,
                    'message' => $json['error_desc'] ?? 'Feeder error '.$json['error_code'],
                    'data' => $json,
                ];
            }

            return [
                'success' => true,
                'data' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve the first record from a given action.
     */
    public function fetchFirstRecord(string $act, string $filter = ''): ?array
    {
        $params = [
            'limit' => 1,
            'offset' => 0,
        ];

        if (! empty($filter)) {
            $params['filter'] = $filter;
        }

        $res = $this->feederRequest($act, $params);
        if ($res['success'] && ! empty($res['data']['data'])) {
            return $res['data']['data'][0];
        }

        return null;
    }

    /**
     * Pull a batch of dosen penugasan records from the Feeder.
     */
    public function pullDosens(string $filter = '', int $limit = 100, int $offset = 0, bool $createNew = false): array
    {
        $res = $this->feederRequest('GetListPenugasanDosen', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        if (! $res['success']) {
            return $res;
        }

        $records = $res['data']['data'] ?? [];

        if (empty($records)) {
            return [
                'success' => true,
                'records_count' => 0,
                'stats' => ['matched' => 0, 'created' => 0, 'failed' => 0],
            ];
        }

        $matched = 0;
        $created = 0;
        $failed = 0;

        $idDosens = array_filter(array_unique(array_column($records, 'id_dosen')));
        $dosenStatusMap = [];

        if (! empty($idDosens)) {
            $filterParts = array_map(function (string $id): string {
                return "id_dosen = '{$id}'";
            }, $idDosens);
            $dosenFilter = implode(' or ', $filterParts);

            $dosenRes = $this->feederRequest('GetListDosen', [
                'filter' => $dosenFilter,
                'limit' => count($idDosens),
                'offset' => 0,
            ]);

            if ($dosenRes['success'] && ! empty($dosenRes['data']['data'])) {
                foreach ($dosenRes['data']['data'] as $profile) {
                    $dosenStatusMap[$profile['id_dosen']] = $profile;
                }
            }
        }

        foreach ($records as $record) {
            if (empty($record['nidn'])) {
                $failed++;

                continue;
            }

            $dosen = Dosen::where('nidn', $record['nidn'])->first();

            $dosenBio = $dosenStatusMap[$record['id_dosen']] ?? null;

            if (! $dosenBio) {
                $dosenBio = $this->fetchFirstRecord('GetListDosen', "id_dosen = '{$record['id_dosen']}'");
                if ($dosenBio) {
                    $dosenStatusMap[$record['id_dosen']] = $dosenBio;
                }
            }

            $statusValue = 0;
            if ($dosenBio && ! empty($dosenBio['nama_status_aktif'])) {
                $statusLower = strtolower(trim($dosenBio['nama_status_aktif']));
                if ($statusLower === 'aktif' || $statusLower === 'tugas belajar') {
                    $statusValue = 1;
                }
            }

            if ($dosen) {
                $dosen->update([
                    'feeder_id_dosen' => $record['id_dosen'] ?? null,
                    'feeder_id_registrasi' => $record['id_registrasi_dosen'] ?? null,
                    'status' => $statusValue,
                ]);
                $matched++;
            } elseif ($createNew) {
                try {
                    $jk = ($dosenBio && isset($dosenBio['jenis_kelamin']) && $dosenBio['jenis_kelamin'] === 'P')
                        ? 'Perempuan'
                        : 'Laki-Laki';

                    $tanggalLahir = ($dosenBio && ! empty($dosenBio['tanggal_lahir']))
                        ? trim($dosenBio['tanggal_lahir'])
                        : '1980-01-01';

                    if ($tanggalLahir !== '1980-01-01') {
                        try {
                            $tanggalLahir = Carbon::parse($tanggalLahir)->format('Y-m-d');
                        } catch (\Throwable $e) {
                            $tanggalLahir = '1980-01-01';
                        }
                    }

                    Dosen::create([
                        'nama' => $record['nama_dosen'] ?? 'Dosen Baru',
                        'nidn' => $record['nidn'],
                        'pembimbing_akademik' => 0,
                        'jenis_kelamin' => $jk,
                        'no_telephone' => '081234567890',
                        'agama' => $dosenBio['nama_agama'] ?? 'Islam',
                        'status' => $statusValue,
                        'tanggal_lahir' => $tanggalLahir,
                        'tempat_lahir' => '-',
                        'email' => 'dosen_'.$record['nidn'].'@sawunggalih.ac.id',
                        'password' => Hash::make($record['nidn']),
                        'is_first_login' => true,
                        'feeder_id_dosen' => $record['id_dosen'],
                        'feeder_id_registrasi' => $record['id_registrasi_dosen'],
                    ]);

                    $created++;
                } catch (\Throwable $e) {
                    Log::error("Failed to create dosen locally for NIDN {$record['nidn']}: ".$e->getMessage());
                    $failed++;
                }
            } else {
                $failed++;
            }
        }

        return [
            'success' => true,
            'records_count' => count($records),
            'stats' => [
                'matched' => $matched,
                'created' => $created,
                'failed' => $failed,
            ],
        ];
    }

    /**
     * Pull all dosen penugasan records sequentially applying rate limits.
     */
    public function pullAllDosens(?string $tahunAjaran = null, bool $createNew = false, ?callable $progressCallback = null): array
    {
        $batchSize = (int) config('services.feeder.sync.batch_size', 100);
        $delayMs = (int) config('services.feeder.sync.delay_ms', 200);

        $filter = '';
        if ($tahunAjaran) {
            $filter = "id_tahun_ajaran = '{$tahunAjaran}'";
        }

        $offset = 0;
        $totalMatched = 0;
        $totalCreated = 0;
        $totalFailed = 0;
        $totalProcessed = 0;

        while (true) {
            $result = $this->pullDosens($filter, $batchSize, $offset, $createNew);

            if (! $result['success']) {
                Log::error("Pull All Dosens stopped due to failure at offset {$offset}: ".($result['message'] ?? ''));
                break;
            }

            $count = $result['records_count'] ?? 0;
            if ($count === 0) {
                break;
            }

            $totalMatched += $result['stats']['matched'] ?? 0;
            $totalCreated += $result['stats']['created'] ?? 0;
            $totalFailed += $result['stats']['failed'] ?? 0;
            $totalProcessed += $count;

            if ($progressCallback) {
                $progressCallback($totalProcessed, $result['stats']);
            }

            if ($count < $batchSize) {
                break;
            }

            $offset += $batchSize;

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        return [
            'success' => true,
            'total_processed' => $totalProcessed,
            'stats' => [
                'matched' => $totalMatched,
                'created' => $totalCreated,
                'failed' => $totalFailed,
            ],
        ];
    }

    /**
     * Pull a batch of mata kuliah records from the Feeder.
     */
    public function pullMataKuliahs(string $filter = '', int $limit = 100, int $offset = 0): array
    {
        $res = $this->feederRequest('GetListMataKuliah', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        if (! $res['success']) {
            return $res;
        }

        $records = $res['data']['data'] ?? [];

        if (empty($records)) {
            return [
                'success' => true,
                'records_count' => 0,
                'stats' => ['matched' => 0, 'failed' => 0],
            ];
        }

        $matched = 0;
        $failed = 0;

        foreach ($records as $record) {
            if (empty($record['kode_mata_kuliah'])) {
                $failed++;

                continue;
            }

            $matkul = Matkul::where('kode', $record['kode_mata_kuliah'])->first();

            if ($matkul) {
                $matkul->update([
                    'feeder_id_matkul' => $record['id_matkul'] ?? null,
                ]);
                $matched++;
            } else {
                $failed++;
            }
        }

        return [
            'success' => true,
            'records_count' => count($records),
            'stats' => [
                'matched' => $matched,
                'failed' => $failed,
            ],
        ];
    }

    /**
     * Pull all mata kuliah records sequentially applying rate limits.
     */
    public function pullAllMataKuliahs(?callable $progressCallback = null): array
    {
        $batchSize = (int) config('services.feeder.sync.batch_size', 100);
        $delayMs = (int) config('services.feeder.sync.delay_ms', 200);

        $offset = 0;
        $totalMatched = 0;
        $totalFailed = 0;
        $totalProcessed = 0;

        while (true) {
            $result = $this->pullMataKuliahs('', $batchSize, $offset);

            if (! $result['success']) {
                Log::error("Pull All Mata Kuliahs stopped due to failure at offset {$offset}: ".($result['message'] ?? ''));
                break;
            }

            $count = $result['records_count'] ?? 0;
            if ($count === 0) {
                break;
            }

            $totalMatched += $result['stats']['matched'] ?? 0;
            $totalFailed += $result['stats']['failed'] ?? 0;
            $totalProcessed += $count;

            if ($progressCallback) {
                $progressCallback($totalProcessed, $result['stats']);
            }

            if ($count < $batchSize) {
                break;
            }

            $offset += $batchSize;

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        return [
            'success' => true,
            'total_processed' => $totalProcessed,
            'stats' => [
                'matched' => $totalMatched,
                'failed' => $totalFailed,
            ],
        ];
    }

    /**
     * Synchronize a local rombel class KRS data to Neo Feeder.
     */
    public function syncKrsRombelToFeeder(int $kelasRombelId): array
    {
        $kelas = Kelas::with(['prodi', 'semester'])->find($kelasRombelId);
        if (! $kelas) {
            return [
                'success' => false,
                'message' => "Kelas Rombel with ID {$kelasRombelId} not found.",
            ];
        }

        $prodi = $kelas->prodi;
        if (! $prodi || ! $prodi->feeder_id_prodi) {
            return [
                'success' => false,
                'message' => 'Program Studi associated with this class does not have a feeder_id_prodi mapping. Please sync Prodis first.',
            ];
        }

        $activePeriodId = Cache::get('feeder_active_period_id');
        if (! $activePeriodId) {
            $syncRes = $this->syncActivePeriod();
            if ($syncRes['success']) {
                $activePeriodId = $syncRes['id_semester'];
            }
        }

        if (! $activePeriodId) {
            return [
                'success' => false,
                'message' => 'Active semester period not found or synced from Feeder.',
            ];
        }

        $schedules = Jadwal::where('kelas_id', $kelas->id)->with(['matkul', 'dosen'])->get();

        if ($schedules->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No schedules found for this class.',
            ];
        }

        $syncedClasses = 0;
        $syncedLecturers = 0;
        $syncedKrsRecords = 0;

        foreach ($schedules as $schedule) {
            $matkul = $schedule->matkul;
            if (! $matkul || ! $matkul->feeder_id_matkul) {
                continue;
            }

            // Step 1: Create Kelas Kuliah
            if (empty($schedule->feeder_id_kelas)) {
                $namaKelasFeeder = substr($kelas->nama_kelas, -5);
                if (empty($namaKelasFeeder)) {
                    $namaKelasFeeder = 'A';
                }

                $kelasData = [
                    'id_prodi' => $prodi->feeder_id_prodi,
                    'id_semester' => $activePeriodId,
                    'id_matkul' => $matkul->feeder_id_matkul,
                    'nama_kelas_kuliah' => $namaKelasFeeder,
                    'bahasan' => 'Kelas Rombel '.$kelas->nama_kelas,
                    'tanggal_mulai_efektif' => now()->format('Y-m-d'),
                    'apa_untuk_pditt' => 0,
                    'kapasitas' => 40,
                    'lingkup' => 1,
                    'mode' => 'F',
                ];

                $response = $this->feederRequest('InsertKelasKuliah', ['record' => $kelasData]);

                if ($response['success'] && ! empty($response['data']['data']['id_kelas_kuliah'])) {
                    $schedule->feeder_id_kelas = $response['data']['data']['id_kelas_kuliah'];
                    $schedule->save();
                    $syncedClasses++;
                } else {
                    $existingClass = $this->fetchFirstRecord('GetListKelasKuliah', "id_prodi = '{$prodi->feeder_id_prodi}' and id_semester = '{$activePeriodId}' and id_matkul = '{$matkul->feeder_id_matkul}' and nama_kelas_kuliah = '{$namaKelasFeeder}'");
                    if ($existingClass) {
                        $schedule->feeder_id_kelas = $existingClass['id_kelas_kuliah'];
                        $schedule->save();
                        $syncedClasses++;
                    } else {
                        Log::error("Failed to sync Kelas Kuliah for schedule ID {$schedule->id}: ".($response['message'] ?? ''));

                        continue;
                    }
                }
            } else {
                $syncedClasses++;
            }

            // Step 2: Dosen Pengajar mapping and injection
            $feederIdRegistrasiDosen = null;
            $dosen = $schedule->dosen;

            if ($dosen) {
                if ($dosen->feeder_id_registrasi) {
                    $feederIdRegistrasiDosen = $dosen->feeder_id_registrasi;
                } elseif ($dosen->feeder_dosen_placeholder_id) {
                    $placeholder = Dosen::find($dosen->feeder_dosen_placeholder_id);
                    if ($placeholder && $placeholder->feeder_id_registrasi) {
                        $feederIdRegistrasiDosen = $placeholder->feeder_id_registrasi;
                    }
                }
            }

            if (! $feederIdRegistrasiDosen && $schedule->feeder_override_dosen_id) {
                $overrideDosen = Dosen::find($schedule->feeder_override_dosen_id);
                if ($overrideDosen && $overrideDosen->feeder_id_registrasi) {
                    $feederIdRegistrasiDosen = $overrideDosen->feeder_id_registrasi;
                }
            }

            if (! $feederIdRegistrasiDosen) {
                $fallbackDosen = Dosen::whereNotNull('feeder_id_registrasi')->first();
                if ($fallbackDosen) {
                    $feederIdRegistrasiDosen = $fallbackDosen->feeder_id_registrasi;
                }
            }

            if ($feederIdRegistrasiDosen) {
                $existingDosenPengajar = $this->fetchFirstRecord('GetDosenPengajarKelasKuliah', "id_kelas_kuliah = '{$schedule->feeder_id_kelas}' and id_registrasi_dosen = '{$feederIdRegistrasiDosen}'");
                if (! $existingDosenPengajar) {
                    $dosenPengajarData = [
                        'id_registrasi_dosen' => $feederIdRegistrasiDosen,
                        'id_kelas_kuliah' => $schedule->feeder_id_kelas,
                        'sks_substansi_total' => (float) ((($matkul->teori ?? 0) + ($matkul->praktek ?? 0)) ?: 3.0),
                        'rencana_minggu_pertemuan' => 16,
                        'realisasi_minggu_pertemuan' => 16,
                        'id_jenis_evaluasi' => 1,
                    ];

                    $responseDosen = $this->feederRequest('InsertDosenPengajarKelasKuliah', ['record' => $dosenPengajarData]);
                    if ($responseDosen['success']) {
                        $syncedLecturers++;
                    } else {
                        Log::warning("Failed to insert Dosen Pengajar for schedule ID {$schedule->id}: ".($responseDosen['message'] ?? ''));
                    }
                } else {
                    $syncedLecturers++;
                }
            }

            // Step 3: Register students in rombel to this class
            $students = Mahasiswa::where('kelas_id', $kelas->id)->whereNotNull('feeder_id_registrasi')->get();
            foreach ($students as $student) {
                $existingPeserta = $this->fetchFirstRecord('GetPesertaKelasKuliah', "id_kelas_kuliah = '{$schedule->feeder_id_kelas}' and id_registrasi_mahasiswa = '{$student->feeder_id_registrasi}'");
                if (! $existingPeserta) {
                    $pesertaData = [
                        'id_kelas_kuliah' => $schedule->feeder_id_kelas,
                        'id_registrasi_mahasiswa' => $student->feeder_id_registrasi,
                    ];

                    $responsePeserta = $this->feederRequest('InsertPesertaKelasKuliah', ['record' => $pesertaData]);
                    if ($responsePeserta['success']) {
                        $syncedKrsRecords++;
                    } else {
                        Log::error("Failed to insert student NIM {$student->nim} to Kelas Feeder: ".($responsePeserta['message'] ?? ''));
                    }
                } else {
                    $syncedKrsRecords++;
                }
            }
        }

        return [
            'success' => true,
            'synced_classes' => $syncedClasses,
            'synced_lecturers' => $syncedLecturers,
            'synced_krs_records' => $syncedKrsRecords,
        ];
    }
}
