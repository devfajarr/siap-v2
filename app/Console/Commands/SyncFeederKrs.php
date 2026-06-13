<?php

namespace App\Console\Commands;

use App\Services\FeederTokenService;
use Illuminate\Console\Command;

/**
 * Command to synchronize local rombel class KRS data to Neo Feeder.
 */
class SyncFeederKrs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:sync-krs {kelas_id : The local ID of the Kelas Rombel}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize local Kelas Rombel KRS data (Kelas, Dosen Pengajar, and Peserta) to Neo Feeder';

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
        $kelasId = (int) $this->argument('kelas_id');

        $this->info("Starting Feeder KRS synchronization for Class ID: {$kelasId}...");

        $result = $this->feederTokenService->syncKrsRombelToFeeder($kelasId);

        if (! $result['success']) {
            $this->error('KRS synchronization failed: '.($result['message'] ?? ''));

            return Command::FAILURE;
        }

        $this->info('Completed KRS Synchronization!');
        $this->line("Synced Classes (Kelas Kuliah): {$result['synced_classes']}");
        $this->line("Synced Lecturers (Dosen Pengajar): {$result['synced_lecturers']}");
        $this->line("Synced Student KRS Records (Peserta Kelas): {$result['synced_krs_records']}");

        return Command::SUCCESS;
    }
}
