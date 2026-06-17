<?php

namespace App\Console\Commands;

use App\Services\FeederTokenService;
use Illuminate\Console\Command;

class PullFeederWilayah extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:pull-wilayah';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull administrative regions (levels 1, 2, and 3) from Neo Feeder to the local database';

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
        $this->info('Starting Neo Feeder administrative regions (wilayah) synchronization...');

        // Pull Level 1 (Provinsi)
        $this->info('1/3 Pulling Provinces (Level 1)...');
        $res1 = $this->feederTokenService->pullWilayahs(1);
        if (! $res1['success']) {
            $this->error('Failed: '.($res1['message'] ?? ''));

            return Command::FAILURE;
        }
        $this->line("Created/Updated: {$res1['total_processed']} provinces.");

        // Pull Level 2 (Kabupaten/Kota)
        $this->info('2/3 Pulling Cities/Regencies (Level 2)...');
        $res2 = $this->feederTokenService->pullWilayahs(2);
        if (! $res2['success']) {
            $this->error('Failed: '.($res2['message'] ?? ''));

            return Command::FAILURE;
        }
        $this->line("Created/Updated: {$res2['total_processed']} cities.");

        // Pull Level 3 (Kecamatan)
        $this->info('3/3 Pulling Districts (Level 3)...');
        $res3 = $this->feederTokenService->pullWilayahs(3);
        if (! $res3['success']) {
            $this->error('Failed: '.($res3['message'] ?? ''));

            return Command::FAILURE;
        }
        $this->line("Created/Updated: {$res3['total_processed']} subdistricts.");

        $this->info('Completed Neo Feeder wilayah synchronization successfully!');

        return Command::SUCCESS;
    }
}
