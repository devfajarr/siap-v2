<?php

namespace App\Jobs;

use App\Events\FeederSyncProgress;
use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Matkul;
use App\Models\Prodi;
use App\Services\FeederTokenService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FeederSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $type,
        public array $params = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FeederTokenService $feederService): void
    {
        Log::info("Starting FeederSyncJob: {$this->type}");

        $this->broadcastProgress('running', 0, 0, 0, 'Menginisialisasi sinkronisasi...', []);

        try {
            switch ($this->type) {
                case 'pull-prodis':
                    $this->handlePullProdis($feederService);
                    break;
                case 'pull-dosens':
                    $this->handlePullDosens($feederService);
                    break;
                case 'pull-matkuls':
                    $this->handlePullMatkuls($feederService);
                    break;
                case 'pull-mahasiswas':
                    $this->handlePullMahasiswas($feederService);
                    break;
                case 'push-mahasiswa':
                    $this->handlePushMahasiswa($feederService);
                    break;
                case 'push-mahasiswa-kelas':
                    $this->handlePushMahasiswaKelas($feederService);
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported sync type: {$this->type}");
            }
        } catch (\Throwable $e) {
            Log::error('FeederSyncJob failed: '.$e->getMessage(), [
                'type' => $this->type,
                'exception' => $e,
            ]);

            $this->broadcastProgress(
                'failed',
                0,
                0,
                0,
                'Sinkronisasi gagal: '.$e->getMessage(),
                []
            );

            throw $e;
        }
    }

    protected function handlePullProdis(FeederTokenService $feederService): void
    {
        $total = $this->getFeederCount($feederService, 'GetCountProdi', Prodi::class);

        $this->broadcastProgress('running', 0, 0, $total, 'Menarik data Program Studi...', [
            'matched' => 0,
            'created' => 0,
            'failed' => 0,
        ]);

        $result = $feederService->pullAllProdis(function (int $processed, array $stats) use ($total): void {
            $progress = $total > 0 ? (int) (($processed / $total) * 100) : 0;
            $progress = min(99, $progress);
            $this->broadcastProgress(
                'running',
                $progress,
                $processed,
                $total,
                "Menarik data Program Studi... ({$processed}/{$total})",
                $stats
            );
        });

        if ($result['success']) {
            $stats = $result['stats'] ?? [];
            $this->broadcastProgress(
                'completed',
                100,
                $result['total_processed'] ?? 0,
                $total,
                'Sinkronisasi Program Studi selesai!',
                $stats
            );
        } else {
            throw new \RuntimeException($result['message'] ?? 'Gagal menarik data prodi');
        }
    }

    protected function handlePullDosens(FeederTokenService $feederService): void
    {
        $total = $this->getFeederCount($feederService, 'GetCountPenugasanDosen', Dosen::class);

        $this->broadcastProgress('running', 0, 0, $total, 'Menarik data penugasan Dosen...', [
            'matched' => 0,
            'created' => 0,
            'failed' => 0,
        ]);

        $result = $feederService->pullAllDosens(null, true, function (int $processed, array $stats) use ($total): void {
            $progress = $total > 0 ? (int) (($processed / $total) * 100) : 0;
            $progress = min(99, $progress);
            $this->broadcastProgress(
                'running',
                $progress,
                $processed,
                $total,
                "Menarik data penugasan Dosen... ({$processed}/{$total})",
                $stats
            );
        });

        if ($result['success']) {
            $stats = $result['stats'] ?? [];
            $this->broadcastProgress(
                'completed',
                100,
                $result['total_processed'] ?? 0,
                $total,
                'Sinkronisasi Dosen selesai!',
                $stats
            );
        } else {
            throw new \RuntimeException($result['message'] ?? 'Gagal menarik data dosen');
        }
    }

    protected function handlePullMatkuls(FeederTokenService $feederService): void
    {
        $total = $this->getFeederCount($feederService, 'GetCountMataKuliah', Matkul::class);

        $this->broadcastProgress('running', 0, 0, $total, 'Menarik data Mata Kuliah...', [
            'matched' => 0,
            'created' => 0,
            'failed' => 0,
        ]);

        $result = $feederService->pullAllMataKuliahs(function (int $processed, array $stats) use ($total): void {
            $progress = $total > 0 ? (int) (($processed / $total) * 100) : 0;
            $progress = min(99, $progress);
            $this->broadcastProgress(
                'running',
                $progress,
                $processed,
                $total,
                "Menarik data Mata Kuliah... ({$processed}/{$total})",
                $stats
            );
        });

        if ($result['success']) {
            $stats = $result['stats'] ?? [];
            $this->broadcastProgress(
                'completed',
                100,
                $result['total_processed'] ?? 0,
                $total,
                'Sinkronisasi Mata Kuliah selesai!',
                $stats
            );
        } else {
            throw new \RuntimeException($result['message'] ?? 'Gagal menarik data mata kuliah');
        }
    }

    protected function handlePullMahasiswas(FeederTokenService $feederService): void
    {
        $total = $this->getFeederCount($feederService, 'GetCountRiwayatPendidikanMahasiswa', Mahasiswa::class);

        $this->broadcastProgress('running', 0, 0, $total, 'Menarik data Mahasiswa...', [
            'matched' => 0,
            'created' => 0,
            'failed' => 0,
        ]);

        $result = $feederService->pullAllMahasiswas(true, function (int $processed, array $stats) use ($total): void {
            $progress = $total > 0 ? (int) (($processed / $total) * 100) : 0;
            $progress = min(99, $progress);
            $this->broadcastProgress(
                'running',
                $progress,
                $processed,
                $total,
                "Menarik data Mahasiswa... ({$processed}/{$total})",
                $stats
            );
        });

        if ($result['success']) {
            $stats = $result['stats'] ?? [];
            $this->broadcastProgress(
                'completed',
                100,
                $result['total_processed'] ?? 0,
                $total,
                'Sinkronisasi Mahasiswa selesai!',
                $stats
            );
        } else {
            throw new \RuntimeException($result['message'] ?? 'Gagal menarik data mahasiswa');
        }
    }

    protected function handlePushMahasiswa(FeederTokenService $feederService): void
    {
        $mahasiswaId = $this->params['mahasiswa_id'] ?? null;
        if (! $mahasiswaId) {
            throw new \InvalidArgumentException('Parameter mahasiswa_id diperlukan');
        }

        $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);

        $this->broadcastProgress('running', 10, 0, 1, "Mengirim data {$mahasiswa->nama_lengkap} ke Feeder...", [
            'matched' => 0,
            'created' => 0,
            'failed' => 0,
        ]);

        $result = $feederService->pushMahasiswaToFeeder($mahasiswa);

        if ($result['success']) {
            $this->broadcastProgress(
                'completed',
                100,
                1,
                1,
                "Push data {$mahasiswa->nama_lengkap} berhasil!",
                ['matched' => 1, 'created' => 0, 'failed' => 0]
            );
        } else {
            throw new \RuntimeException($result['message'] ?? 'Gagal push data mahasiswa ke Feeder');
        }
    }

    protected function handlePushMahasiswaKelas(FeederTokenService $feederService): void
    {
        $kelasId = $this->params['kelas_id'] ?? null;
        if (! $kelasId) {
            throw new \InvalidArgumentException('Parameter kelas_id diperlukan');
        }

        $kelas = Kelas::findOrFail($kelasId);
        $students = Mahasiswa::where('kelas_id', $kelas->id)
            ->where('status_mahasiswa', 'Aktif')
            ->get();

        $total = $students->count();

        if ($total === 0) {
            $this->broadcastProgress(
                'completed',
                100,
                0,
                0,
                "Kelas {$kelas->nama_kelas} tidak memiliki mahasiswa aktif untuk di-push.",
                ['matched' => 0, 'created' => 0, 'failed' => 0]
            );

            return;
        }

        $this->broadcastProgress('running', 0, 0, $total, "Mengevaluasi mahasiswa di kelas {$kelas->nama_kelas}...", [
            'matched' => 0,
            'created' => 0,
            'failed' => 0,
        ]);

        $successCount = 0;
        $failedCount = 0;
        $processed = 0;

        foreach ($students as $student) {
            $res = $feederService->pushMahasiswaToFeeder($student);
            $processed++;

            if ($res['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }

            $progress = (int) (($processed / $total) * 100);
            $progress = min(99, $progress);

            $this->broadcastProgress(
                'running',
                $progress,
                $processed,
                $total,
                "Mengirim mahasiswa {$student->nama_lengkap} ({$processed}/{$total})...",
                [
                    'matched' => $successCount,
                    'created' => 0,
                    'failed' => $failedCount,
                ]
            );
        }

        $this->broadcastProgress(
            'completed',
            100,
            $processed,
            $total,
            "Selesai memproses kelas {$kelas->nama_kelas}.",
            [
                'matched' => $successCount,
                'created' => 0,
                'failed' => $failedCount,
            ]
        );
    }

    protected function getFeederCount(FeederTokenService $feederService, string $act, string $modelClass): int
    {
        try {
            $response = $feederService->feederRequest($act);
            if ($response['success'] && isset($response['data']['data'])) {
                return (int) $response['data']['data'];
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to fetch count via Feeder action {$act}: ".$e->getMessage());
        }

        return (int) $modelClass::count();
    }

    protected function broadcastProgress(
        string $status,
        int $progress,
        int $processed,
        int $total,
        string $message,
        array $stats
    ): void {
        broadcast(new FeederSyncProgress(
            type: $this->type,
            status: $status,
            progress: $progress,
            processed: $processed,
            total: $total,
            message: $message,
            stats: $stats
        ));
    }
}
