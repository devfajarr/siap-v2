<?php

namespace App\Console\Commands;

use App\Services\FeederTokenService;
use Illuminate\Console\Command;

/**
 * Command to pull and synchronize lecturer reference IDs from Neo Feeder.
 */
class PullFeederDosens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:pull-dosens 
                            {--tahun-ajaran= : Specific Year of Penugasan to pull}
                            {--create-new : Create new local lecturer record if NIDN does not exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull and synchronize lecturer reference IDs from Neo Feeder';

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
        $tahunAjaran = $this->option('tahun-ajaran');
        $createNew = (bool) $this->option('create-new');

        $this->info('Starting Neo Feeder lecturer synchronization...');

        $result = $this->feederTokenService->pullAllDosens($tahunAjaran, $createNew, function (int $totalProcessed, array $stats): void {
            $this->line("Processed: {$totalProcessed} | Matched: {$stats['matched']} | Created: ".($stats['created'] ?? 0)." | Failed: {$stats['failed']}");
        });

        if (! $result['success']) {
            $this->error('Lecturer synchronization failed: '.($result['message'] ?? ''));

            return Command::FAILURE;
        }

        $stats = $result['stats'];
        $this->info("Completed! Total Processed: {$result['total_processed']} | Matched: {$stats['matched']} | Created: ".($stats['created'] ?? 0)." | Failed: {$stats['failed']}");

        return Command::SUCCESS;
    }
}
