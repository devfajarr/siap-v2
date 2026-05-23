<?php

namespace App\Http\Controllers\V2\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\Matkul;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman utama dashboard mahasiswa V2.
     */
    public function index(Request $request): Response
    {
        $user = auth()->guard('mahasiswa')->user();

        $mahasiswa = Mahasiswa::with(['kelas.prodi', 'kelas.semester', 'pembimbingAkademik'])->findOrFail($user->id);
        $kelas = $mahasiswa->kelas;

        $mahasiswaData = [
            'nama_lengkap' => $mahasiswa->nama_lengkap,
            'nim' => $mahasiswa->nim,
            'prodi' => $kelas->prodi->nama_prodi ?? '-',
            'semester' => $kelas->semester->semester ?? '-',
            'nama_kelas' => $kelas->nama_kelas ?? '-',
            'dpa_name' => $mahasiswa->pembimbingAkademik->nama ?? '-',
        ];

        if (! $kelas) {
            return Inertia::render('Mahasiswa/Dashboard/Index', [
                'mahasiswa' => $mahasiswaData,
                'totalKehadiran' => 0,
                'totalMatakuliah' => 0,
                'statusKrs' => $mahasiswa->status_krs == 1 ? 'Sudah' : 'Belum',
                'jadwals' => [],
            ]);
        }

        // Total Kehadiran
        $totalKehadiran = Absen::where('mahasiswas_id', $mahasiswa->id)
            ->where('kelas_id', $kelas->id)
            ->whereIn('status', ['H', 'T'])
            ->count();

        // Total Matakuliah
        $totalMatakuliah = Matkul::where('prodi_id', $kelas->id_prodi)
            ->where('semester_id', $kelas->id_semester)
            ->count();

        // Jadwal Hari Ini
        $tanggal = Carbon::now();
        $hariIni = $tanggal->isoFormat('dddd');

        $jadwalsMahasiswa = Jadwal::with(['matkul', 'dosen', 'ruangan', 'kelas'])
            ->where('kelas_id', $kelas->id)
            ->where('hari', $hariIni)
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        $absensHariIni = Absen::whereIn('jadwals_id', $jadwalsMahasiswa->pluck('id'))
            ->whereDate('created_at', $tanggal->toDateString())
            ->where('mahasiswas_id', $mahasiswa->id)
            ->get();

        $jadwalsFormatted = $jadwalsMahasiswa->map(function ($jadwal) use ($absensHariIni) {
            $absen = $absensHariIni->where('jadwals_id', $jadwal->id)->first();

            return [
                'id' => $jadwal->id,
                'kelas' => $jadwal->kelas->nama_kelas ?? '-',
                'matkul' => $jadwal->matkul->nama_matkul ?? '-',
                'dosen' => $jadwal->dosen->nama ?? '-',
                'ruangan' => $jadwal->ruangan->nama ?? '-',
                'waktu_mulai' => $jadwal->waktu_mulai,
                'waktu_selesai' => $jadwal->waktu_selesai,
                'sudah_presensi' => $absen ? true : false,
                'status_kehadiran' => $absen ? $absen->status : null,
            ];
        });

        return Inertia::render('Mahasiswa/Dashboard/Index', [
            'mahasiswa' => $mahasiswaData,
            'totalKehadiran' => $totalKehadiran,
            'totalMatakuliah' => $totalMatakuliah,
            'statusKrs' => $mahasiswa->status_krs == 1 ? 'Sudah' : 'Belum',
            'jadwals' => $jadwalsFormatted,
        ]);
    }
}
