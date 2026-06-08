<?php

namespace App\Console\Commands;

use App\Services\FeederTokenService;
use Illuminate\Console\Command;

/**
 * Command to query and display the dictionary/schema of a Neo Feeder function.
 */
class GetFeederDictionary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:dictionary {fungsi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query and display the request/response structure of a Neo Feeder function';

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
        $fungsi = (string) $this->argument('fungsi');

        $this->info("Fetching dictionary schema for function: {$fungsi}...");

        $result = $this->feederTokenService->getDictionary($fungsi);

        if (! $result['success']) {
            $this->error("Failed to query dictionary: {$result['message']}");

            return Command::FAILURE;
        }

        $this->info('Dictionary retrieved successfully!');

        $json = json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->line($json);

        return Command::SUCCESS;
    }
}
