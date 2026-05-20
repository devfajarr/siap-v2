<?php

namespace App\Http\Controllers\V2\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        $dosen = Auth::guard('dosen')->user();
        $nowDay = Carbon::now()->isoFormat('dddd');

        $jadwalsDosenHariIni = Jadwal::with(['kelas', 'matkul', 'ruangan'])
            ->where('dosens_id', $dosen->id)
            ->where('hari', $nowDay)
            ->get();

        $totalKelas = $jadwalsDosenHariIni->groupBy('kelas_id')->count();
        $totalMatakuliah = $jadwalsDosenHariIni->groupBy('matkuls_id')->count();
        $totalPresensiHariIni = Absen::whereDate('created_at', Carbon::today())
            ->where('dosens_id', $dosen->id)
            ->distinct('kelas_id')
            ->count('kelas_id');

        $formattedJadwal = $jadwalsDosenHariIni->map(function ($jadwal) {
            $now = Carbon::now();
            $mulai = Carbon::createFromFormat('H:i:s', $jadwal->waktu_mulai);
            $selesai = Carbon::createFromFormat('H:i:s', $jadwal->waktu_selesai);

            $status = 'Belum Mulai';
            $variant = 'warning';

            if ($now->between($mulai, $selesai)) {
                $status = 'Berlangsung';
                $variant = 'success';
            } elseif ($now->greaterThan($selesai)) {
                $status = 'Selesai';
                $variant = 'destructive';
            }

            return [
                'id' => $jadwal->id,
                'kelas' => $jadwal->kelas->nama_kelas,
                'matkul' => $jadwal->matkul->nama_matkul,
                'ruangan' => $jadwal->ruangan->nama,
                'jam' => $mulai->format('H:i') . ' - ' . $selesai->format('H:i'),
                'status' => $status,
                'status_variant' => $variant,
            ];
        });

        return Inertia::render('Dosen/Dashboard', [
            'stats' => [
                'totalKelas' => $totalKelas,
                'totalMatakuliah' => $totalMatakuliah,
                'totalPresensiHariIni' => $totalPresensiHariIni,
            ],
            'jadwalHariIni' => $formattedJadwal,
        ]);
    }
}
