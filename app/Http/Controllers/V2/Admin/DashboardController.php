<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Settings;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        $today = Carbon::today();

        // Statistik Utama
        $totalMahasiswa = Mahasiswa::count();
        $totalKelas = Kelas::whereHas('semester', function ($query) {
            $query->where('status', 1);
        })->count();
        $totalDosen = Dosen::where('status', 1)->count();

        // Kehadiran Hari Ini
        $totalHadir = Absen::whereDate('created_at', $today)
            ->whereIn('status', ['H', 'T'])
            ->count();

        $totalTidakHadir = Absen::whereDate('created_at', $today)
            ->whereIn('status', ['A', 'C', 'S', 'I'])
            ->count();

        // Notifikasi Jadwal Harian Status
        $dailyScheduleStatus = Settings::where('key', 'daily_schedule')->value('value') ?? 0;

        // Rekap Per Prodi
        $programStudi = Prodi::all();
        $rekapProdi = $programStudi->map(function ($prodi) use ($today) {
            $hadir = Absen::where('prodis_id', $prodi->id)
                ->whereDate('created_at', $today)
                ->whereIn('status', ['H', 'T'])
                ->count();

            $tidakHadir = Absen::where('prodis_id', $prodi->id)
                ->whereDate('created_at', $today)
                ->whereIn('status', ['A', 'C', 'S', 'I'])
                ->count();

            return [
                'nama_prodi' => $prodi->nama_prodi,
                'total_hadir' => $hadir,
                'total_tidak_hadir' => $tidakHadir,
            ];
        });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalMahasiswa' => $totalMahasiswa,
                'totalKelas' => $totalKelas,
                'totalDosen' => $totalDosen,
                'totalHadir' => $totalHadir,
                'totalTidakHadir' => $totalTidakHadir,
            ],
            'dailyScheduleStatus' => (int) $dailyScheduleStatus,
            'rekapProdi' => $rekapProdi,
        ]);
    }
}
