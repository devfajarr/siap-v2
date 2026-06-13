<?php

namespace App\Console\Commands;

use App\Services\FeederTokenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Command to analyze and test KRS inputs via Neo Feeder.
 */
class TestKrsFeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:test-krs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze and test KRS input data: InsertKelasKuliah, InsertDosenPengajarKelasKuliah, and InsertPesertaKelasKuliah';

    /**
     * Create a new command instance.
     */
    public function __construct(protected FeederTokenService $feederTokenService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting KRS Feeders Test and Analysis...');

        $token = $this->feederTokenService->getToken();
        if (! $token) {
            $this->error('Failed to retrieve a valid Feeder token.');

            return Command::FAILURE;
        }

        $this->info('Feeder token retrieved successfully.');

        // Step 1: Query necessary references
        $this->comment('Retrieving reference data from Feeder...');

        // 1.1 Program Studi
        $prodi = $this->fetchFirstRecord('GetProdi');
        if (! $prodi) {
            $this->error('No Program Studi found on Feeder.');

            return Command::FAILURE;
        }
        $this->line('Reference Prodi: '.json_encode($prodi));

        // 1.2 Semester / Periode Aktif
        $semester = $this->fetchFirstRecord('GetSemester', "a_periode_aktif = '1'");
        if (! $semester) {
            $semester = $this->fetchFirstRecord('GetSemester');
        }
        if (! $semester) {
            $this->error('No Semester found on Feeder.');

            return Command::FAILURE;
        }
        $this->line('Reference Semester: '.json_encode($semester));

        // 1.3 Mata Kuliah
        $matkul = $this->fetchFirstRecord('GetListMataKuliah', "id_prodi = '{$prodi['id_prodi']}'");
        if (! $matkul) {
            $matkul = $this->fetchFirstRecord('GetListMataKuliah');
        }
        if (! $matkul) {
            $this->error('No Mata Kuliah found on Feeder.');

            return Command::FAILURE;
        }
        $this->line('Reference Mata Kuliah: '.json_encode($matkul));

        // 1.4 Dosen
        $dosen = $this->fetchFirstRecord('GetListPenugasanDosen', "id_prodi = '{$prodi['id_prodi']}'");
        if (! $dosen) {
            $dosen = $this->fetchFirstRecord('GetListPenugasanDosen');
        }
        if (! $dosen) {
            $this->error('No Dosen Penugasan found on Feeder.');

            return Command::FAILURE;
        }
        $this->line('Reference Dosen: '.json_encode($dosen));

        // 1.5 Mahasiswa (Riwayat Pendidikan)
        $mahasiswa = $this->fetchFirstRecord('GetListRiwayatPendidikanMahasiswa', "id_prodi = '{$prodi['id_prodi']}'");
        if (! $mahasiswa) {
            $mahasiswa = $this->fetchFirstRecord('GetListRiwayatPendidikanMahasiswa');
        }
        if (! $mahasiswa) {
            $this->error('No Mahasiswa Riwayat Pendidikan found on Feeder.');

            return Command::FAILURE;
        }
        $this->line('Reference Mahasiswa: '.json_encode($mahasiswa));

        // 1.6 Jenis Evaluasi
        $jenisEvaluasi = $this->fetchFirstRecord('GetJenisEvaluasi');
        if (! $jenisEvaluasi) {
            $this->error('No Jenis Evaluasi found on Feeder.');

            return Command::FAILURE;
        }
        $this->line('Reference Jenis Evaluasi: '.json_encode($jenisEvaluasi));

        $this->info('--- All references successfully retrieved. Starting Insertion test ---');

        // Step 2: InsertKelasKuliah
        $this->comment('Step 2: Testing InsertKelasKuliah...');
        $namaKelasTest = 'Tst'.rand(10, 99);
        $kelasData = [
            'id_prodi' => $prodi['id_prodi'],
            'id_semester' => $semester['id_semester'],
            'id_matkul' => $matkul['id_matkul'],
            'nama_kelas_kuliah' => $namaKelasTest,
            'bahasan' => 'Analisa KRS Test Bahasan',
            'tanggal_mulai_efektif' => now()->format('Y-m-d'),
            'tanggal_akhir_efektif' => now()->addMonths(6)->format('Y-m-d'),
            'apa_untuk_pditt' => 0,
            'kapasitas' => 40,
            'lingkup' => 1,
            'mode' => 'F',
        ];

        $resKelas = $this->feederRequest('InsertKelasKuliah', ['record' => $kelasData]);
        if (! $resKelas['success'] || empty($resKelas['data']['data']['id_kelas_kuliah'])) {
            $this->error('InsertKelasKuliah Failed: '.json_encode($resKelas));

            return Command::FAILURE;
        }

        $idKelasKuliah = $resKelas['data']['data']['id_kelas_kuliah'];
        $this->info("InsertKelasKuliah Success! New id_kelas_kuliah: {$idKelasKuliah}");

        // Step 3: InsertDosenPengajarKelasKuliah
        $this->comment('Step 3: Testing InsertDosenPengajarKelasKuliah...');
        $dosenData = [
            'id_registrasi_dosen' => $dosen['id_registrasi_dosen'],
            'id_kelas_kuliah' => $idKelasKuliah,
            'sks_substansi_total' => (float) ($matkul['sks_mata_kuliah'] ?? 3.0),
            'rencana_minggu_pertemuan' => 16,
            'realisasi_minggu_pertemuan' => 16,
            'id_jenis_evaluasi' => $jenisEvaluasi['id_jenis_evaluasi'],
        ];

        $resDosen = $this->feederRequest('InsertDosenPengajarKelasKuliah', ['record' => $dosenData]);
        if (! $resDosen['success'] || empty($resDosen['data']['data']['id_aktivitas_mengajar'])) {
            $this->error('InsertDosenPengajarKelasKuliah Failed: '.json_encode($resDosen));
            // Let's not abort here if we still want to test Peserta (though Peserta usually requires a class only)
        } else {
            $idAktivitasMengajar = $resDosen['data']['data']['id_aktivitas_mengajar'];
            $this->info("InsertDosenPengajarKelasKuliah Success! New id_aktivitas_mengajar: {$idAktivitasMengajar}");
        }

        // Step 4: InsertPesertaKelasKuliah
        $this->comment('Step 4: Testing InsertPesertaKelasKuliah...');
        $pesertaData = [
            'id_kelas_kuliah' => $idKelasKuliah,
            'id_registrasi_mahasiswa' => $mahasiswa['id_registrasi_mahasiswa'],
        ];

        $resPeserta = $this->feederRequest('InsertPesertaKelasKuliah', ['record' => $pesertaData]);
        if (! $resPeserta['success']) {
            $this->error('InsertPesertaKelasKuliah Failed: '.json_encode($resPeserta));

            return Command::FAILURE;
        }

        $this->info('InsertPesertaKelasKuliah Success!');
        $this->info('Response data: '.json_encode($resPeserta['data']));

        $this->info('--- KRS Input Testing Completed Successfully! ---');

        return Command::SUCCESS;
    }

    /**
     * Call the Neo Feeder API.
     */
    protected function feederRequest(string $act, array $params = []): array
    {
        try {
            $baseUrl = (string) config('services.feeder.base_url');
            $token = $this->feederTokenService->getToken();

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
                    'message' => $json['error_desc'] ?? 'Feeder returned error code '.$json['error_code'],
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
    protected function fetchFirstRecord(string $act, string $filter = ''): ?array
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
}
