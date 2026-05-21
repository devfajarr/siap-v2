<?php

namespace App\Http\Controllers\V2\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\PengajuanRekapPresensi;
use App\Models\PengajuanRekapkontrak;
use App\Models\PengajuanRekapBerita;
use App\Models\PengajuanRekapNilai;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;
        $prodi = Prodi::find($prodiId);

        $totalMahasiswa = Mahasiswa::whereHas('kelas', function ($query) use ($prodiId) {
            $query->where('id_prodi', $prodiId);
        })->count();

        $hariIni = Carbon::now()->isoFormat('dddd');
        $jadwalHariIni = Jadwal::with(['kelas', 'matkul', 'ruangan', 'dosen'])
            ->whereHas('kelas', function ($query) use ($prodiId) {
                $query->where('id_prodi', $prodiId);
            })
            ->where('hari', $hariIni)
            ->get();

        $pendingPresensi = PengajuanRekapPresensi::where('status', 0)
            ->whereHas('kelas', function ($query) use ($prodiId) {
                $query->where('id_prodi', $prodiId);
            })->count();

        $pendingKontrak = PengajuanRekapkontrak::where('status', 0)
            ->whereHas('kelas', function ($query) use ($prodiId) {
                $query->where('id_prodi', $prodiId);
            })->count();

        $pendingResume = PengajuanRekapBerita::where('status', 0)
            ->whereHas('kelas', function ($query) use ($prodiId) {
                $query->where('id_prodi', $prodiId);
            })->count();

        $pendingNilai = PengajuanRekapNilai::where('status', 0)
            ->whereHas('kelas', function ($query) use ($prodiId) {
                $query->where('id_prodi', $prodiId);
            })->count();

        $formattedJadwal = $jadwalHariIni->map(function ($jadwal) {
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
                'dosen' => $jadwal->dosen->nama,
                'ruangan' => $jadwal->ruangan->nama,
                'jam' => $mulai->format('H:i') . ' - ' . $selesai->format('H:i'),
                'status' => $status,
                'status_variant' => $variant,
            ];
        });

        return Inertia::render('Kaprodi/Dashboard', [
            'prodi' => $prodi ? $prodi->nama_prodi : 'N/A',
            'stats' => [
                'totalMahasiswa' => $totalMahasiswa,
                'pendingPresensi' => $pendingPresensi,
                'pendingKontrak' => $pendingKontrak,
                'pendingResume' => $pendingResume,
                'pendingNilai' => $pendingNilai,
            ],
            'jadwalHariIni' => $formattedJadwal,
        ]);
    }
}
