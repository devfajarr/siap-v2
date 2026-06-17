<?php

namespace App\Console\Commands;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Services\FeederTokenService;
use Illuminate\Console\Command;

/**
 * Command to push local student records to Neo Feeder.
 */
class PushFeederMahasiswa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:push-mahasiswa 
                            {nim? : The NIM of a specific student to push} 
                            {--kelas= : The ID of the local Kelas Rombel to push} 
                            {--all : Push all active students that are not yet synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push student records and education history registration to Neo Feeder';

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
        $nim = $this->argument('nim');
        $kelasId = $this->option('kelas');
        $all = (bool) $this->option('all');

        if (! $nim && ! $kelasId && ! $all) {
            $this->error('Please specify a student NIM, a --kelas=ID, or use the --all option.');

            return Command::FAILURE;
        }

        $this->info('Starting Neo Feeder student push operation...');

        if ($nim) {
            $mahasiswa = Mahasiswa::where('nim', $nim)->first();
            if (! $mahasiswa) {
                $this->error("Student with NIM '{$nim}' not found in local database.");

                return Command::FAILURE;
            }

            $this->info("Pushing student: {$mahasiswa->nama_lengkap} (NIM: {$mahasiswa->nim})...");
            $result = $this->feederTokenService->pushMahasiswaToFeeder($mahasiswa);

            if (! $result['success']) {
                $this->error('Failed to push student: '.($result['message'] ?? 'Unknown error'));

                return Command::FAILURE;
            }

            $this->info('Successfully pushed student to Feeder!');
            $this->line("id_mahasiswa: {$result['id_mahasiswa']}");
            $this->line("id_registrasi_mahasiswa: {$result['id_registrasi_mahasiswa']}");

            return Command::SUCCESS;
        }

        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            if (! $kelas) {
                $this->error("Class roombel with ID '{$kelasId}' not found.");

                return Command::FAILURE;
            }

            $this->info("Pushing all active students in class: {$kelas->nama_kelas}...");
            $result = $this->feederTokenService->pushMahasiswasByKelas($kelas->id);

            if (! $result['success']) {
                $this->error('Failed: '.($result['message'] ?? ''));

                return Command::FAILURE;
            }

            $this->info('Class sync completed!');
            $this->line("Total Processed: {$result['total']}");
            $this->line("Success count: {$result['success_count']}");
            $this->line("Failed count: {$result['failed_count']}");

            return Command::SUCCESS;
        }

        if ($all) {
            $this->info('Pushing all active unsynced students...');
            $students = Mahasiswa::where('status_mahasiswa', 'Aktif')
                ->whereNull('feeder_id_registrasi')
                ->get();

            if ($students->isEmpty()) {
                $this->info('All active students are already synchronized.');

                return Command::SUCCESS;
            }

            $this->info("Found {$students->count()} unsynced active students. Starting batch push...");
            $success = 0;
            $failed = 0;

            foreach ($students as $student) {
                $this->line("Pushing NIM {$student->nim}...");
                $res = $this->feederTokenService->pushMahasiswaToFeeder($student);
                if ($res['success']) {
                    $success++;
                } else {
                    $failed++;
                    $this->warn("Failed for NIM {$student->nim}: ".($res['message'] ?? ''));
                }
            }

            $this->info('Batch sync completed!');
            $this->line('Total Processed: '.$students->count());
            $this->line("Success: {$success}");
            $this->line("Failed: {$failed}");

            return Command::SUCCESS;
        }

        return Command::SUCCESS;
    }
}
