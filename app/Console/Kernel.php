<?php

namespace App\Console;

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
            log::info('Daily Schedule Aktif');
            $hariIni = now()->locale('id_ID')->dayName;
            $schedule->call(function () use ($hariIni) {
                $waktuTarget = now()->addMinutes(15)->format('H:i:00');

                $jadwals = \App\Models\Jadwal::whereRaw("TIME(waktu_mulai) = ?", [$waktuTarget])
                    ->whereIn('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])
                    ->where('hari', $hariIni)
                    ->get();

                foreach ($jadwals as $jadwal) {
                    dispatch(new \App\Jobs\KirimNotifikasiJadwal($jadwal))->onQueue('default');
                }
            })->everyMinute();


            // SEMENTARA PAGI SEMUA
            $schedule->call(function () use ($hariIni) {
                if (!in_array($hariIni, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])) {
                    return;
                }
                $dosens = \App\Models\Dosen::whereHas('jadwal', function ($query) use ($hariIni) {
                    $query->where('hari', $hariIni);
                })->get();

                foreach ($dosens as $dosen) {
                    $jadwals = \App\Models\Jadwal::where('hari', $hariIni)
                        ->where('dosens_id', $dosen->id)
                        ->get();
                    dispatch(new \App\Jobs\KirimJadwalDosen($jadwals, $dosen))->onQueue('default');
                }
            })->timezone('Asia/Jakarta')->dailyAt('06:00');
        }
        if (!$daily) {
            log::info('Daily Schedule Tidak Aktif');
        }

	$schedule->call(function () {
            $tanggalHariIni = now()->format('Y-m-d');
            $waktuTarget = now()->addMinutes(15)->format('H:i:00');
        
            $jadwalsMendatang = \App\Models\JadwalUjian::with('pegawai')
                ->where('tanggal', $tanggalHariIni)
                ->whereTime('waktu_mulai', $waktuTarget)
                ->get();
        
            if ($jadwalsMendatang->isNotEmpty()) {
                $jadwalsPerPegawai = $jadwalsMendatang->groupBy('pegawais_id');
        
                foreach ($jadwalsPerPegawai as $pegawaiId => $jadwalsPegawai) {
                    $pegawai = $jadwalsPegawai->first()->pegawai;
        
                    if ($pegawai) {
                        dispatch(new \App\Jobs\KirimJadwalPegawai($jadwalsPegawai, $pegawai))->onQueue('default');
                    } else {
                        Log::warning("Pegawai dengan ID {$pegawaiId} tidak ditemukan untuk jadwal mendatang.");
                    }
                }
            } else {
                Log::info("Tidak ada jadwal ujian untuk dikirim pada $tanggalHariIni jam $waktuTarget.");
            }
        })->everyMinute();



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
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
