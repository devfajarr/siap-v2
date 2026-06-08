<?php

namespace App\Console\Commands;

use App\Services\FeederTokenService;
use Illuminate\Console\Command;

/**
 * Command to pull and synchronize program study (prodi) records from Neo Feeder to the local database.
 */
class PullFeederProdis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:pull-prodis 
                            {--all : Pull all records sequentially} 
                            {--limit=100 : Number of records to pull in a single batch} 
                            {--offset=0 : Offset of records to pull in a single batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull program study (prodi) records from Neo Feeder to the local database with rate limiting';

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

        $this->info('Starting Neo Feeder program study synchronization...');

        if ($all) {
            $this->info('Running pull for all records sequentially...');

            $result = $this->feederTokenService->pullAllProdis(function (int $totalProcessed, array $stats): void {
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

            $result = $this->feederTokenService->pullProdis('', $limit, $offset);

            if (! $result['success']) {
                $this->error('Sync batch failed: '.($result['message'] ?? ''));

                return Command::FAILURE;
            }

            $stats = $result['stats'];
            $this->info("Completed batch! Matched: {$stats['matched']} | Created: {$stats['created']} | Failed: {$stats['failed']}");
        }

        return Command::SUCCESS;
    }
}
