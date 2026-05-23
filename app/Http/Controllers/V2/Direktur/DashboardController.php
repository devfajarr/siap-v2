<?php

namespace App\Http\Controllers\V2\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PengajuanRekapPresensi;
use App\Models\PengajuanRekapkontrak;
use App\Models\PengajuanRekapBerita;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        $today = Carbon::today();

        $totalMahasiswa = Mahasiswa::count();
        $totalDosen = Dosen::where('status', 1)->count();

        $totalMahasiswaHariIni = Absen::whereDate('created_at', $today)->count();
        $totalHadirHariIni = Absen::whereDate('created_at', $today)
            ->whereIn('status', ['H', 'T'])
            ->count();

        $persentaseKehadiran = $totalMahasiswaHariIni > 0
            ? round(($totalHadirHariIni / $totalMahasiswaHariIni) * 100, 1)
            : 0;

        // Pending rekap counts where setuju_wadir is 0
        $presensisCount = PengajuanRekapPresensi::where('status', 0)->count();

        $kontraksCount = PengajuanRekapkontrak::where('status', 0)->count();

        $beritasCount = PengajuanRekapBerita::where('status', 0)->count();

        return Inertia::render('Direktur/Dashboard', [
            'stats' => [
                'totalMahasiswa' => $totalMahasiswa,
                'totalDosen' => $totalDosen,
                'totalHadirHariIni' => $totalHadirHariIni,
                'persentaseKehadiran' => $persentaseKehadiran,
                'pendingPresensi' => $presensisCount,
                'pendingKontrak' => $kontraksCount,
                'pendingBerita' => $beritasCount,
            ]
        ]);
    }
}
