<?php

namespace App\Http\Controllers\V2\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Matkul;
use App\Models\Semester;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Absen;
use App\Models\NilaiHuruf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    public function matkul()
    {
        $semesters = Semester::orderBy('semester', 'asc')->get();
        return Inertia::render('Kaprodi/Monitoring/Matkul/Index', [
            'semesters' => $semesters
        ]);
    }

    public function matkulDetail($semester_id)
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;
        $semester = Semester::findOrFail($semester_id);
        
        $matkuls = Matkul::with(['prodi', 'semester'])
            ->where('semester_id', $semester_id)
            ->where('prodi_id', $prodiId)
            ->get();

        return Inertia::render('Kaprodi/Monitoring/Matkul/Detail', [
            'semester' => $semester,
            'matkuls' => $matkuls
        ]);
    }

    public function perkuliahan()
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;

        $kelas = Kelas::where('id_prodi', $prodiId)->with('semester')->get();

        return Inertia::render('Kaprodi/Monitoring/Perkuliahan/Index', [
            'kelas' => $kelas
        ]);
    }

    public function perkuliahanDetail($kelas_id)
    {
        $kelas = Kelas::with('semester', 'prodi')->findOrFail($kelas_id);
        $jadwals = Jadwal::with(['matkul', 'dosen', 'ruangan'])
            ->where('kelas_id', $kelas_id)
            ->get();

        return Inertia::render('Kaprodi/Monitoring/Perkuliahan/Detail', [
            'kelas' => $kelas,
            'jadwals' => $jadwals
        ]);
    }

    public function presensiCek($matkul_id, $kelas_id, $jadwal_id, $rentang)
    {
        $range = $rentang === '1-7' ? range(1, 7) : range(8, 14);
        
        $jadwal = Jadwal::with(['matkul', 'kelas', 'dosen'])->findOrFail($jadwal_id);
        
        $presensi = Absen::with('mahasiswa')
            ->where('matkuls_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->where('jadwals_id', $jadwal_id)
            ->whereIn('pertemuan', $range)
            ->get()
            ->groupBy('mahasiswas_id');

        return Inertia::render('Kaprodi/Monitoring/Perkuliahan/PresensiCek', [
            'jadwal' => $jadwal,
            'rentang' => $rentang,
            'presensi' => $presensi
        ]);
    }

    public function nilai()
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;

        $kelas = Kelas::where('id_prodi', $prodiId)->with('semester')->get();

        return Inertia::render('Kaprodi/Monitoring/Nilai/Index', [
            'kelas' => $kelas
        ]);
    }

    public function nilaiDetail($kelas_id)
    {
        $kelas = Kelas::with('semester', 'prodi')->findOrFail($kelas_id);
        $jadwals = Jadwal::with(['matkul', 'dosen'])
            ->where('kelas_id', $kelas_id)
            ->get();

        return Inertia::render('Kaprodi/Monitoring/Nilai/Detail', [
            'kelas' => $kelas,
            'jadwals' => $jadwals
        ]);
    }
}
