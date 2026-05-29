<?php

namespace App\Console\Commands;

use App\Models\Mahasiswa;
use App\Models\OrangTua;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MigrateParentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siap:migrate-parents {--password=password : Default password for generated accounts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data nama_ibu dari tabel mahasiswa menjadi akun orang tua formal di tabel orang_tuas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $defaultPasswordInput = $this->option('password');
        $hashedPassword = Hash::make($defaultPasswordInput);

        // Fetch students who have a non-empty name of mother (nama_ibu)
        $students = Mahasiswa::whereNotNull('nama_ibu')
            ->where('nama_ibu', '!=', '')
            ->get();

        if ($students->isEmpty()) {
            $this->info('Tidak ada data mahasiswa dengan kolom nama_ibu untuk dimigrasi.');

            return self::SUCCESS;
        }

        $this->info("Menemukan {$students->count()} data mahasiswa untuk diproses.");
        $bar = $this->output->createProgressBar($students->count());
        $bar->start();

        $createdCount = 0;
        $linkedCount = 0;

        DB::transaction(function () use ($students, $hashedPassword, $bar, &$createdCount, &$linkedCount) {
            foreach ($students as $student) {
                // Generate username based on NIM to ensure uniqueness
                $username = 'ortu.'.$student->nim;

                // Find or create parent account
                $parent = OrangTua::where('username', $username)->first();

                if (! $parent) {
                    $parent = OrangTua::create([
                        'nama' => $student->nama_ibu,
                        'username' => $username,
                        'password' => $hashedPassword,
                        'no_telephone' => null,
                        'alamat' => $student->alamat ?? null,
                    ]);
                    $createdCount++;
                }

                // Check if already linked
                $isLinked = $parent->mahasiswas()->where('mahasiswas.id', $student->id)->exists();

                if (! $isLinked) {
                    $parent->mahasiswas()->attach($student->id, [
                        'relationship_type' => 'Ibu',
                    ]);
                    $linkedCount++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info('Migrasi selesai!');
        $this->line("- Akun orang tua baru dibuat: <info>{$createdCount}</info>");
        $this->line("- Hubungan/tautan pivot terbuat: <info>{$linkedCount}</info>");

        return self::SUCCESS;
    }
}
