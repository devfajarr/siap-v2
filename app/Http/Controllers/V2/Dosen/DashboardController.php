<?php

namespace App\Http\Controllers\V2\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the Dosen / structural dashboard.
     */
    public function index(): Response
    {
        Carbon::setLocale('id');

        $dosen = Auth::guard('dosen')->user();
        if (! $dosen && Auth::guard('jabatan')->check()) {
            $dosen = Auth::guard('jabatan')->user()->dosen;
        }

        if (! $dosen) {
            return Inertia::render('Dosen/Dashboard', [
                'stats' => [
                    'totalKelas' => 0,
                    'totalMatakuliah' => 0,
                    'totalPresensiHariIni' => 0,
                    'totalBimbinganUnread' => 0,
                ],
                'jadwalHariIni' => [],
                'bimbinganUnread' => [],
            ]);
        }

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
                'jam' => $mulai->format('H:i').' - '.$selesai->format('H:i'),
                'status' => $status,
                'status_variant' => $variant,
            ];
        });

        $students = Mahasiswa::with('kelas')->where('dosen_pembimbing_id', $dosen->id)->get();
        $bimbinganUnread = [];
        $totalBimbinganUnread = 0;

        foreach ($students as $student) {
            $unreadCount = Message::whereNull('jadwal_id')
                ->where('sender_id', $student->id)
                ->where('sender_type', 'App\Models\Mahasiswa')
                ->where('receiver_id', $dosen->id)
                ->where('receiver_type', 'App\Models\Dosen')
                ->where('read', false)
                ->count();

            if ($unreadCount > 0) {
                $bimbinganUnread[] = [
                    'id' => $student->id,
                    'nama' => $student->nama_lengkap,
                    'nim' => $student->nim,
                    'kelas' => $student->kelas->nama_kelas ?? '',
                    'unread_count' => $unreadCount,
                ];
                $totalBimbinganUnread += $unreadCount;
            }
        }

        return Inertia::render('Dosen/Dashboard', [
            'stats' => [
                'totalKelas' => $totalKelas,
                'totalMatakuliah' => $totalMatakuliah,
                'totalPresensiHariIni' => $totalPresensiHariIni,
                'totalBimbinganUnread' => $totalBimbinganUnread,
            ],
            'jadwalHariIni' => $formattedJadwal,
            'bimbinganUnread' => collect($bimbinganUnread)->sortByDesc('unread_count')->take(3)->values()->all(),
        ]);
    }
}
