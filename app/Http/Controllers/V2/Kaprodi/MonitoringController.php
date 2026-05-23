<?php

namespace App\Http\Controllers\V2\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Matkul;
use App\Models\Semester;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Absen;
use App\Models\NilaiHuruf;
use App\Models\Tugas;
use App\Models\Aktif;
use App\Models\Etika;
use App\Models\Uts;
use App\Models\Uas;
use App\Models\Mahasiswa;
use App\Models\Wadir;
use App\Models\Kaprodi;
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
        $user = Auth::guard('kaprodi')->user();
        $kelas = Kelas::with('semester', 'prodi')->findOrFail($kelas_id);
        
        // Security check
        if ($kelas->id_prodi !== $user->prodis_id) {
            abort(403, 'Unauthorized action.');
        }

        // Optimize query by using withMax('absen', 'pertemuan') to avoid N+1
        $jadwals = Jadwal::with(['matkul', 'dosen'])
            ->withMax('absen', 'pertemuan')
            ->where('kelas_id', $kelas_id)
            ->get();

        return Inertia::render('Kaprodi/Monitoring/Nilai/Detail', [
            'kelas' => $kelas,
            'jadwals' => $jadwals
        ]);
    }

    public function nilaiCek($matkul_id, $kelas_id, $jadwal_id)
    {
        $user = Auth::guard('kaprodi')->user();
        
        // 1. Secure & load class with relations
        $kelas = Kelas::with('semester', 'prodi')->findOrFail($kelas_id);
        if ($kelas->id_prodi !== $user->prodis_id) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Load schedule and verify matkul/kelas matching
        $jadwal = Jadwal::with(['matkul', 'dosen'])
            ->where('id', $jadwal_id)
            ->where('kelas_id', $kelas_id)
            ->where('matkuls_id', $matkul_id)
            ->firstOrFail();

        // 3. Single query to load all active students in the class
        $mahasiswas = Mahasiswa::withTrashed()
            ->where('kelas_id', $kelas_id)
            ->orderBy('nim', 'asc')
            ->get();

        $mahasiswa_ids = $mahasiswas->pluck('id');

        // 4. Bulk queries with keys/groups to eliminate N+1 queries
        $tugass = Tugas::where('jadwal_id', $jadwal_id)
            ->where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->whereIn('mahasiswa_id', $mahasiswa_ids)
            ->get();

        $groupedTugas = $tugass->groupBy('mahasiswa_id');
        $jumlahTugas = max(1, $tugass->pluck('tugas_ke')->unique()->count());

        $dataAktif = Aktif::where('jadwal_id', $jadwal_id)
            ->where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->whereIn('mahasiswa_id', $mahasiswa_ids)
            ->get()
            ->keyBy('mahasiswa_id');

        $dataEtika = Etika::where('jadwal_id', $jadwal_id)
            ->where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->whereIn('mahasiswa_id', $mahasiswa_ids)
            ->get()
            ->keyBy('mahasiswa_id');

        $absens = Absen::where('jadwals_id', $jadwal_id)
            ->where('kelas_id', $kelas_id)
            ->where('matkuls_id', $matkul_id)
            ->whereIn('mahasiswas_id', $mahasiswa_ids)
            ->get();

        $dataAbsensi = $absens->groupBy('mahasiswas_id');
        
        $totalPertemuan = Absen::where('jadwals_id', $jadwal_id)
            ->where('kelas_id', $kelas_id)
            ->where('matkuls_id', $matkul_id)
            ->max('pertemuan') ?? 0;

        $utss = Uts::where('jadwal_id', $jadwal_id)
            ->where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->whereIn('mahasiswa_id', $mahasiswa_ids)
            ->get()
            ->keyBy('mahasiswa_id');

        $uass = Uas::where('jadwal_id', $jadwal_id)
            ->where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->whereIn('mahasiswa_id', $mahasiswa_ids)
            ->get()
            ->keyBy('mahasiswa_id');

        $wadir = Wadir::where('no', 1)->first();
        $kaprodi = Kaprodi::where('prodis_id', $kelas->id_prodi)->first();

        // 5. Pre-calculate values on backend for student lists to ensure clean view rendering
        $totalKehadiranSemuaMahasiswa = 0;
        $formattedMahasiswas = $mahasiswas->map(function ($mahasiswa) use (
            $groupedTugas,
            $jumlahTugas,
            $dataAktif,
            $dataEtika,
            $dataAbsensi,
            $totalPertemuan,
            $utss,
            $uass,
            &$totalKehadiranSemuaMahasiswa
        ) {
            $tugasGroup = $groupedTugas->get($mahasiswa->id, collect());
            
            $totalNilaiTugas = 0;
            $jumlahTugasDikumpulkan = 0;
            $nilaiTugasList = [];

            for ($i = 1; $i <= $jumlahTugas; $i++) {
                $tugas = $tugasGroup->firstWhere('tugas_ke', $i);
                $nilaiVal = 0;
                if ($tugas) {
                    $nilai = $tugas->nilai;
                    if ($nilai !== null && $nilai !== '-') {
                        $nilaiVal = (float)$nilai;
                    }
                    $totalNilaiTugas += $nilaiVal;
                    $jumlahTugasDikumpulkan++;
                }
                $nilaiTugasList[] = $nilaiVal;
            }

            $persentaseTugas = $jumlahTugas > 0 ? ($totalNilaiTugas / ($jumlahTugas * 100)) * 25 : 0;

            $nilaiKeaktifan = isset($dataAktif[$mahasiswa->id]) ? (float)$dataAktif[$mahasiswa->id]->nilai : 0;
            $persentaseKeaktifan = ($nilaiKeaktifan / 100) * 5;

            $nilaiEtika = isset($dataEtika[$mahasiswa->id]) ? (float)$dataEtika[$mahasiswa->id]->nilai : 0;
            $persentaseEtika = ($nilaiEtika / 100) * 5;

            $absensiGroup = $dataAbsensi->get($mahasiswa->id, collect());
            $totalKehadiran = $absensiGroup->whereIn('status', ['H', 'T'])->count();
            $totalKehadiranSemuaMahasiswa += $totalKehadiran;
            
            $persentaseKehadiran = $totalPertemuan > 0 ? ($totalKehadiran / $totalPertemuan) * 15 : 0;

            $nilaiUts = isset($utss[$mahasiswa->id]) ? (float)$utss[$mahasiswa->id]->nilai : 0;
            $persentaseUts = ($nilaiUts / 100) * 25;

            $nilaiUas = isset($uass[$mahasiswa->id]) ? (float)$uass[$mahasiswa->id]->nilai : 0;
            $persentaseUas = ($nilaiUas / 100) * 25;

            $jumlahTotal = $persentaseTugas + $persentaseKeaktifan + $persentaseEtika + $persentaseKehadiran + $persentaseUts + $persentaseUas;
            
            $nilaiHuruf = $this->getKeteranganNilai($jumlahTotal);

            return [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama_lengkap,
                'tugas_list' => $nilaiTugasList,
                'persentase_tugas' => round($persentaseTugas, 2),
                'nilai_keaktifan' => $nilaiKeaktifan,
                'persentase_keaktifan' => round($persentaseKeaktifan, 2),
                'nilai_etika' => $nilaiEtika,
                'persentase_etika' => round($persentaseEtika, 2),
                'total_kehadiran' => $totalKehadiran,
                'persentase_kehadiran' => round($persentaseKehadiran, 2),
                'nilai_uts' => $nilaiUts,
                'persentase_uts' => round($persentaseUts, 2),
                'nilai_uas' => $nilaiUas,
                'persentase_uas' => round($persentaseUas, 2),
                'jumlah_total' => round($jumlahTotal, 2),
                'nilai_huruf' => $nilaiHuruf
            ];
        });

        $jumlahMahasiswa = count($mahasiswas);
        $rataRataKehadiran = $jumlahMahasiswa > 0 ? $totalKehadiranSemuaMahasiswa / $jumlahMahasiswa : 0;

        return Inertia::render('Kaprodi/Monitoring/Nilai/Cek', [
            'kelas' => $kelas,
            'jadwal' => $jadwal,
            'mahasiswas' => $formattedMahasiswas,
            'jumlahTugas' => $jumlahTugas,
            'totalPertemuan' => $totalPertemuan,
            'rataRataKehadiran' => round($rataRataKehadiran, 2),
            'wadir' => $wadir,
            'kaprodi' => $kaprodi,
            'tanggal_sekarang' => \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y')
        ]);
    }

    private function getKeteranganNilai($jumlah)
    {
        if ($jumlah >= 85 && $jumlah <= 100) {
            return 'A';
        } elseif ($jumlah >= 80 && $jumlah < 85) {
            return 'A-';
        } elseif ($jumlah >= 75 && $jumlah < 80) {
            return 'B+';
        } elseif ($jumlah >= 70 && $jumlah < 75) {
            return 'B';
        } elseif ($jumlah >= 65 && $jumlah < 70) {
            return 'B-';
        } elseif ($jumlah >= 60 && $jumlah < 65) {
            return 'C+';
        } elseif ($jumlah >= 55 && $jumlah < 60) {
            return 'C';
        } elseif ($jumlah >= 50 && $jumlah < 55) {
            return 'C-';
        } elseif ($jumlah >= 40 && $jumlah < 50) {
            return 'D';
        } elseif ($jumlah >= 0 && $jumlah < 40) {
            return 'E';
        } else {
            return '-';
        }
    }
}
