<?php

namespace App\Console;

use App\Jobs\KirimJadwalDosen;
use App\Jobs\KirimJadwalPegawai;
use App\Jobs\KirimNotifikasiJadwal;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\JadwalUjian;
use App\Models\Settings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {

        $daily = Settings::where('key', 'daily_schedule')->value('value');
        if ($daily) {
            Log::info('Daily Schedule Aktif');
            $hariIni = now()->locale('id_ID')->dayName;
            $schedule->call(function () use ($hariIni) {
                $waktuTarget = now()->addMinutes(15)->format('H:i:00');

                $jadwals = Jadwal::whereRaw('TIME(waktu_mulai) = ?', [$waktuTarget])
                    ->whereIn('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])
                    ->where('hari', $hariIni)
                    ->get();

                foreach ($jadwals as $jadwal) {
                    dispatch(new KirimNotifikasiJadwal($jadwal))->onQueue('whatsapp');
                }
            })->everyMinute();

            // SEMENTARA PAGI SEMUA
            $schedule->call(function () use ($hariIni) {
                if (! in_array($hariIni, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])) {
                    return;
                }
                $dosens = Dosen::whereHas('jadwal', function ($query) use ($hariIni) {
                    $query->where('hari', $hariIni);
                })->get();

                foreach ($dosens as $dosen) {
                    $jadwals = Jadwal::where('hari', $hariIni)
                        ->where('dosens_id', $dosen->id)
                        ->get();
                    dispatch(new KirimJadwalDosen($jadwals, $dosen))->onQueue('whatsapp');
                }
            })->timezone('Asia/Jakarta')->dailyAt('06:00');
        }
        if (! $daily) {
            Log::info('Daily Schedule Tidak Aktif');
        }

        $schedule->call(function () {
            $tanggalHariIni = now()->format('Y-m-d');
            $waktuTarget = now()->addMinutes(15)->format('H:i:00');

            $jadwalsMendatang = JadwalUjian::with('pengawas')
                ->where('tanggal', $tanggalHariIni)
                ->whereTime('waktu_mulai', $waktuTarget)
                ->get();

            if ($jadwalsMendatang->isNotEmpty()) {
                $jadwalsPerPengawas = $jadwalsMendatang->groupBy(function ($j) {
                    return $j->pengawas_type.'_'.$j->pengawas_id;
                });

                foreach ($jadwalsPerPengawas as $key => $jadwalsPegawai) {
                    $pengawas = $jadwalsPegawai->first()->pengawas;

                    if ($pengawas) {
                        dispatch(new KirimJadwalPegawai($jadwalsPegawai, $pengawas))->onQueue('whatsapp');
                    } else {
                        Log::warning("Pengawas tidak ditemukan untuk kunci jadwal {$key}.");
                    }
                }
            } else {
                Log::info("Tidak ada jadwal ujian untuk dikirim pada $tanggalHariIni jam $waktuTarget.");
            }
        })->everyMinute();

        $schedule->command('feeder:renew-token')->daily();

        // PAGI DAN SIANG
        // $schedule->call(function () use ($hariIni) {
        //     if (!in_array($hariIni, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])) {
        //         return;
        //     }

        //     $dosens = \App\Models\Dosen::whereHas('jadwal', function ($query) use ($hariIni) {
        //         $query->where('hari', $hariIni)
        //             ->whereHas('kelas', function ($q) {
        //                 $q->where('jenis_kelas', 'Reguler');
        //             });
        //     })->get();

        //     foreach ($dosens as $dosen) {
        //         $jadwals = \App\Models\Jadwal::where('hari', $hariIni)
        //             ->where('dosens_id', $dosen->id)
        //             ->whereHas('kelas', function ($q) {
        //                 $q->where('jenis_kelas', 'Reguler');
        //             })->get();

        //         dispatch(new \App\Jobs\KirimJadwalDosen($jadwals, $dosen))->onQueue('default');
        //     }
        // })->timezone('Asia/Jakarta')->dailyAt('06:00');

        // $schedule->call(function () use ($hariIni) {
        //     if (!in_array($hariIni, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])) {
        //         return;
        //     }

        //     $dosens = \App\Models\Dosen::whereHas('jadwal', function ($query) use ($hariIni) {
        //         $query->where('hari', $hariIni)
        //             ->whereHas('kelas', function ($q) {
        //                 $q->where('jenis_kelas', 'Karyawan');
        //             });
        //     })->get();

        //     foreach ($dosens as $dosen) {
        //         $jadwals = \App\Models\Jadwal::where('hari', $hariIni)
        //             ->where('dosens_id', $dosen->id)
        //             ->whereHas('kelas', function ($q) {
        //                 $q->where('jenis_kelas', 'Karyawan');
        //             })->get();

        //         dispatch(new \App\Jobs\KirimJadwalDosen($jadwals, $dosen))->onQueue('default');
        //     }
        // })->timezone('Asia/Jakarta')->dailyAt('12:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
