<?php

namespace App\Console\Commands;

use App\Services\FeederTokenService;
use Illuminate\Console\Command;

class PullFeederAgama extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:pull-agama';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull religion records from Neo Feeder to the local database';

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
        $this->info('Starting Neo Feeder religion (agama) synchronization...');
        $result = $this->feederTokenService->pullAgamas();

        if (! $result['success']) {
            $this->error('Sync failed: '.($result['message'] ?? ''));

            return Command::FAILURE;
        }

        $this->info("Completed! Total Religions Cached: {$result['total']}");

        return Command::SUCCESS;
    }
}
