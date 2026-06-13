<?php

namespace App\Console\Commands;

use App\Services\FeederTokenService;
use Illuminate\Console\Command;

/**
 * Command to pull and synchronize mata kuliah reference IDs from Neo Feeder.
 */
class PullFeederMatkuls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:pull-matkuls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull and synchronize mata kuliah reference IDs from Neo Feeder';

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
        $this->info('Starting Neo Feeder mata kuliah synchronization...');

        $result = $this->feederTokenService->pullAllMataKuliahs(function (int $totalProcessed, array $stats): void {
            $this->line("Processed: {$totalProcessed} | Matched: {$stats['matched']} | Failed: {$stats['failed']}");
        });

        if (! $result['success']) {
            $this->error('Mata kuliah synchronization failed: '.($result['message'] ?? ''));

            return Command::FAILURE;
        }

        $this->info("Completed! Total Processed: {$result['total_processed']} | Matched: {$result['stats']['matched']} | Failed: {$result['stats']['failed']}");

        return Command::SUCCESS;
    }
}
