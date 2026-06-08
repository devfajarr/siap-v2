<?php

namespace App\Console\Commands;

use App\Services\FeederTokenService;
use Illuminate\Console\Command;

/**
 * Command to pull and synchronize student records from Neo Feeder to the local database.
 */
class PullFeederMahasiswas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:pull-mahasiswas 
                            {--all : Pull all records sequentially} 
                            {--limit=100 : Number of records to pull in a single batch} 
                            {--offset=0 : Offset of records to pull in a single batch} 
                            {--create-new : Create new student records locally if not found}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull student records from Neo Feeder to the local database with rate limiting';

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
        $all = (bool) $this->option('all');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $createNew = (bool) $this->option('create-new');

        $this->info('Starting Neo Feeder student synchronization...');

        // Synchronize active period from Feeder first
        $this->info('Syncing active academic period from Feeder...');
        $periodResult = $this->feederTokenService->syncActivePeriod();
        if ($periodResult['success']) {
            $this->info("Active academic period synchronized successfully: Semester ID {$periodResult['id_semester']} ({$periodResult['tahun_akademik']}).");
        } else {
            $this->warn('Warning: Failed to sync active academic period: '.($periodResult['message'] ?? 'Unknown error').'. Falling back to local cache.');
        }

        if ($all) {
            $this->info('Running pull for all records sequentially...');

            $result = $this->feederTokenService->pullAllMahasiswas($createNew, function (int $totalProcessed, array $stats): void {
                $this->line("Processed: {$totalProcessed} | Matched: {$stats['matched']} | Created: {$stats['created']} | Failed: {$stats['failed']}");
            });

            if (! $result['success']) {
                $this->error('Sync failed: '.($result['message'] ?? ''));

                return Command::FAILURE;
            }

            $stats = $result['stats'];
            $this->info("Completed! Total Processed: {$result['total_processed']} | Matched: {$stats['matched']} | Created: {$stats['created']} | Failed: {$stats['failed']}");
        } else {
            $this->info("Running pull for single batch (Limit: {$limit}, Offset: {$offset})...");

            $result = $this->pullMahasiswasBatch($limit, $offset, $createNew);

            if (! $result['success']) {
                $this->error('Sync batch failed: '.($result['message'] ?? ''));

                return Command::FAILURE;
            }

            $stats = $result['stats'];
            $this->info("Completed batch! Matched: {$stats['matched']} | Created: {$stats['created']} | Failed: {$stats['failed']}");
        }

        return Command::SUCCESS;
    }

    /**
     * Helper method to call pull batch.
     */
    protected function pullMahasiswasBatch(int $limit, int $offset, bool $createNew): array
    {
        return $this->feederTokenService->pullMahasiswas('', $limit, $offset, $createNew);
    }
}
