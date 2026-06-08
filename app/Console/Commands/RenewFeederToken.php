<?php

namespace App\Console\Commands;

use App\Services\FeederTokenService;
use Illuminate\Console\Command;

/**
 * Command to force refresh and cache the Neo Feeder token.
 */
class RenewFeederToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:renew-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force refresh and cache the Neo Feeder API token';

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
        $this->info('Starting Neo Feeder token renewal...');

        $token = $this->feederTokenService->getToken(true);

        if ($token) {
            $this->info('Successfully renewed and cached Neo Feeder token.');

            return Command::SUCCESS;
        }

        $this->error('Failed to renew Neo Feeder token. Please check laravel.log for errors.');

        return Command::FAILURE;
    }
}
