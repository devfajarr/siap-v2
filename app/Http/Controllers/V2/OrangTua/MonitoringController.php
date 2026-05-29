<?php

namespace App\Http\Controllers\V2\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V2\Mahasiswa\NilaiController;
use App\Models\Absen;
use App\Models\Aktif;
use App\Models\Etika;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Matkul;
use App\Models\NilaiHuruf;
use App\Models\Pembayaran;
use App\Models\Semester;
use App\Models\Tugas;
use App\Models\Uas;
use App\Models\Uts;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringController extends Controller
{
    /**
     * Helper to get parent's active child.
     */
    protected function getActiveChild(): Mahasiswa
    {
        $parent = auth()->guard('orang_tua')->user();
        $activeChildId = session('user.activeChildId');

        if (! $activeChildId) {
            abort(403, 'Tidak ada mahasiswa aktif yang dipilih.');
        }

        return Mahasiswa::with(['kelas.prodi', 'kelas.semester', 'pembimbingAkademik'])
            ->whereHas('orangTuas', function ($query) use ($parent) {
                $query->where('orang_tuas.id', $parent->id);
            })
            ->findOrFail($activeChildId);
    }

    /**
     * Menampilkan rekapitulasi dan riwayat absensi anak.
     */
    public function absen(): Response
    {
        $mahasiswa = $this->getActiveChild();
        $kelas = $mahasiswa->kelas;

        if (! $kelas) {
            return Inertia::render('OrangTua/Absensi', [
                'mahasiswa' => $mahasiswa,
                'rekapAbsensi' => [],
                'riwayatAbsensi' => [],
            ]);
        }

        // Ambil semua jadwal kelas
        $schedules = Jadwal::with(['matkul', 'dosen'])
            ->where('kelas_id', $kelas->id)
            ->get();

        // Ambil data absens anak
        $absens = Absen::with(['matkul', 'dosen'])
            ->where('mahasiswas_id', $mahasiswa->id)
            ->where('kelas_id', $kelas->id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('pertemuan', 'desc')
            ->get();

        // Hitung rekap absensi per mata kuliah
        $rekapAbsensi = $schedules->map(function ($schedule) use ($absens) {
            $matkulAbsens = $absens->where('matkuls_id', $schedule->matkuls_id);
            $totalPertemuan = $matkulAbsens->count();

            $hadir = $matkulAbsens->where('status', 'H')->count();
            $terlambat = $matkulAbsens->where('status', 'T')->count();
            $izin = $matkulAbsens->where('status', 'I')->count();
            $sakit = $matkulAbsens->where('status', 'S')->count();
            $alfa = $matkulAbsens->where('status', 'A')->count();

            $totalHadir = $hadir + $terlambat;
            $persentase = $totalPertemuan > 0 ? round(($totalHadir / $totalPertemuan) * 100, 2) : 100;

            return [
                'matkul_id' => $schedule->matkuls_id,
                'kode_matkul' => $schedule->matkul->kode ?? '-',
                'nama_matkul' => $schedule->matkul->nama_matkul ?? '-',
                'dosen' => $schedule->dosen->nama ?? '-',
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alfa' => $alfa,
                'total_pertemuan' => $totalPertemuan,
                'persentase_kehadiran' => $persentase,
            ];
        })->unique('matkul_id')->values()->toArray();

        // Format riwayat absensi terbaru
        $riwayatAbsensi = $absens->map(function ($absen) {
            $statusMap = [
                'H' => 'Hadir',
                'T' => 'Terlambat',
                'I' => 'Izin',
                'S' => 'Sakit',
                'A' => 'Alfa',
            ];

            return [
                'id' => $absen->id,
                'pertemuan' => $absen->pertemuan,
                'tanggal' => $absen->tanggal,
                'nama_matkul' => $absen->matkul->nama_matkul ?? '-',
                'dosen' => $absen->dosen->nama ?? '-',
                'status_code' => $absen->status,
                'status_label' => $statusMap[$absen->status] ?? $absen->status,
            ];
        })->toArray();

        return Inertia::render('OrangTua/Absensi', [
            'mahasiswa' => [
                'nama_lengkap' => $mahasiswa->nama_lengkap,
                'nim' => $mahasiswa->nim,
                'prodi' => $kelas->prodi->nama_prodi ?? '-',
                'semester' => $kelas->semester->semester ?? '-',
            ],
            'rekapAbsensi' => $rekapAbsensi,
            'riwayatAbsensi' => $riwayatAbsensi,
        ]);
    }

    /**
     * Menampilkan hasil nilai KHS semester aktif anak.
     */
    public function nilai(): Response
    {
        $mahasiswa = $this->getActiveChild();
        $kelas = $mahasiswa->kelas;

        if (! $kelas || ! $kelas->id_semester) {
            return Inertia::render('OrangTua/Nilai', [
                'mahasiswa' => [
                    'nama_lengkap' => $mahasiswa->nama_lengkap,
                    'nim' => $mahasiswa->nim,
                    'prodi' => $kelas->prodi->nama_prodi ?? '-',
                    'semester' => 'Belum Ada',
                ],
                'semesterRiwayat' => null,
                'isRiwayat' => false,
                'matkuls' => [],
                'pembayaran' => null,
                'summary' => [
                    'total_sks' => 0,
                    'ips' => '0.00',
                    'matkul_dinilai' => 0,
                    'total_matkul' => 0,
                ],
            ]);
        }

        return $this->renderNilaiView($mahasiswa, $kelas, $kelas->id_semester, false);
    }

    /**
     * Menampilkan riwayat nilai berdasarkan semester tertentu.
     */
    public function riwayatNilai(Request $request, $semester_id): Response
    {
        $mahasiswa = $this->getActiveChild();
        $kelas = $mahasiswa->kelas;

        if (! $kelas) {
            abort(404, 'Data kelas mahasiswa tidak ditemukan.');
        }

        // Security check: ensure they can only view past or current semesters
        $targetSemester = Semester::findOrFail($semester_id);
        if ($targetSemester->semester > ($kelas->semester->semester ?? 0)) {
            abort(403, 'Akses tidak sah untuk semester ini.');
        }

        return $this->renderNilaiView($mahasiswa, $kelas, $semester_id, true);
    }

    /**
     * Helper to render the grades view.
     */
    protected function renderNilaiView($mahasiswa, $kelas, $semester_id, $isRiwayat): Response
    {
        $targetSemester = Semester::findOrFail($semester_id);

        // Ambil data pembayaran untuk semester yang ditargetkan
        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semester_id)
            ->latest()
            ->first();

        $isLunas = $pembayaran && $pembayaran->status_pembayaran === 1 && $pembayaran->keterangan === 'Sudah';

        if (! $isLunas) {
            return Inertia::render('OrangTua/Nilai', [
                'mahasiswa' => [
                    'nama_lengkap' => $mahasiswa->nama_lengkap,
                    'nim' => $mahasiswa->nim,
                    'prodi' => $kelas->prodi->nama_prodi ?? '-',
                    'semester' => 'Semester '.$targetSemester->semester,
                ],
                'semesterRiwayat' => $targetSemester,
                'isRiwayat' => $isRiwayat,
                'matkuls' => [],
                'pembayaran' => $pembayaran ? [
                    'id' => $pembayaran->id,
                    'status_pembayaran' => (int) $pembayaran->status_pembayaran,
                    'keterangan' => $pembayaran->keterangan,
                    'created_at' => $pembayaran->created_at->translatedFormat('d F Y H:i'),
                ] : null,
                'summary' => [
                    'total_sks' => 0,
                    'ips' => '0.00',
                    'matkul_dinilai' => 0,
                    'total_matkul' => 0,
                ],
            ]);
        }

        // Jika lunas, ambil data matkul & nilai huruf
        $matkuls = Matkul::where('prodi_id', $kelas->id_prodi)
            ->where('semester_id', $semester_id)
            ->get();

        $nilais = NilaiHuruf::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semester_id)
            ->get();

        // Ambil detail nilai komponen
        $tugasNilai = Tugas::where('mahasiswa_id', $mahasiswa->id)->where('kelas_id', $kelas->id)->get();
        $utsNilai = Uts::where('mahasiswa_id', $mahasiswa->id)->where('kelas_id', $kelas->id)->get();
        $uasNilai = Uas::where('mahasiswa_id', $mahasiswa->id)->where('kelas_id', $kelas->id)->get();
        $etikaNilai = Etika::where('mahasiswa_id', $mahasiswa->id)->where('kelas_id', $kelas->id)->get();
        $aktifNilai = Aktif::where('mahasiswa_id', $mahasiswa->id)->where('kelas_id', $kelas->id)->get();

        $combinedData = $matkuls->map(function ($matkul) use ($nilais, $tugasNilai, $utsNilai, $uasNilai, $etikaNilai, $aktifNilai) {
            $nilai = $nilais->firstWhere('matkul_id', $matkul->id);
            $sks = ($matkul->praktek ?? 0) + ($matkul->teori ?? 0);
            $nilaiHuruf = $nilai ? $nilai->nilai_huruf : null;

            // Calculate GPA weight
            $kredit = NilaiController::calculateKredit($nilaiHuruf, $sks);

            // Fetch detailed component values
            $utsVal = $utsNilai->firstWhere('matkul_id', $matkul->id)->nilai ?? '-';
            $uasVal = $uasNilai->firstWhere('matkul_id', $matkul->id)->nilai ?? '-';
            $etikaVal = $etikaNilai->firstWhere('matkul_id', $matkul->id)->nilai ?? '-';
            $aktifVal = $aktifNilai->firstWhere('matkul_id', $matkul->id)->nilai ?? '-';

            // Tugas (tugas bisa lebih dari satu, hitung rata-rata)
            $matkulTugas = $tugasNilai->where('matkul_id', $matkul->id);
            $tugasVal = $matkulTugas->isNotEmpty() ? round($matkulTugas->avg('nilai'), 2) : '-';

            return [
                'id' => $matkul->id,
                'kode' => $matkul->kode,
                'nama_matkul' => $matkul->nama_matkul,
                'sks' => $sks,
                'nilai_total' => $nilai ? $nilai->nilai_total : null,
                'nilai_huruf' => $nilaiHuruf ?? 'Belum Dinilai',
                'kredit' => $kredit,
                'detail' => [
                    'tugas' => $tugasVal,
                    'uts' => $utsVal,
                    'uas' => $uasVal,
                    'etika' => $etikaVal,
                    'aktif' => $aktifVal,
                ],
            ];
        });

        $totalSks = $combinedData->sum('sks');
        $totalKredit = $combinedData->sum('kredit');
        $ips = $totalSks > 0 ? round($totalKredit / $totalSks, 2) : 0;
        $matkulDinilai = $combinedData->where('nilai_huruf', '!=', 'Belum Dinilai')->count();
        $totalMatkul = $combinedData->count();

        return Inertia::render('OrangTua/Nilai', [
            'mahasiswa' => [
                'nama_lengkap' => $mahasiswa->nama_lengkap,
                'nim' => $mahasiswa->nim,
                'prodi' => $kelas->prodi->nama_prodi ?? '-',
                'semester' => 'Semester '.$targetSemester->semester,
            ],
            'semesterRiwayat' => $targetSemester,
            'isRiwayat' => $isRiwayat,
            'matkuls' => $combinedData,
            'pembayaran' => $pembayaran ? [
                'id' => $pembayaran->id,
                'status_pembayaran' => (int) $pembayaran->status_pembayaran,
                'keterangan' => $pembayaran->keterangan,
                'created_at' => $pembayaran->created_at->translatedFormat('d F Y H:i'),
            ] : null,
            'summary' => [
                'total_sks' => $totalSks,
                'ips' => number_format($ips, 2),
                'matkul_dinilai' => $matkulDinilai,
                'total_matkul' => $totalMatkul,
            ],
        ]);
    }

    /**
     * Menampilkan status keuangan dan riwayat pembayaran kuliah.
     */
    public function keuangan(): Response
    {
        $mahasiswa = $this->getActiveChild();

        $pembayarans = Pembayaran::with('semester')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('semester_id', 'desc')
            ->get()
            ->map(function ($pembayaran) {
                return [
                    'id' => $pembayaran->id,
                    'semester' => 'Semester '.($pembayaran->semester->semester ?? '-'),
                    'status_pembayaran' => (int) $pembayaran->status_pembayaran,
                    'keterangan' => $pembayaran->keterangan,
                    'bukti_pembayaran' => $pembayaran->bukti_pembayaran,
                    'created_at' => $pembayaran->created_at->translatedFormat('d F Y H:i'),
                ];
            })->toArray();

        return Inertia::render('OrangTua/Keuangan', [
            'mahasiswa' => [
                'nama_lengkap' => $mahasiswa->nama_lengkap,
                'nim' => $mahasiswa->nim,
                'prodi' => $mahasiswa->kelas->prodi->nama_prodi ?? '-',
                'kelas' => $mahasiswa->kelas->nama_kelas ?? '-',
            ],
            'pembayarans' => $pembayarans,
        ]);
    }

    /**
     * Menampilkan Kartu Rencana Studi (KRS) anak.
     */
    public function krs(): Response
    {
        $mahasiswa = $this->getActiveChild();
        $kelas = $mahasiswa->kelas;

        if (! $kelas || ! $kelas->id_semester || ! $kelas->id_prodi) {
            return Inertia::render('OrangTua/Krs', [
                'mahasiswa' => $mahasiswa,
                'matkuls' => [],
                'krs' => null,
            ]);
        }

        // Ambil daftar matakuliah semester berjalan
        $matkulKrs = Matkul::where('semester_id', $kelas->id_semester)
            ->where('prodi_id', $kelas->id_prodi)
            ->get()
            ->map(function ($matkul) {
                return [
                    'id' => $matkul->id,
                    'kode' => $matkul->kode,
                    'nama_matkul' => $matkul->nama_matkul,
                    'teori' => (int) $matkul->teori,
                    'praktek' => (int) $matkul->praktek,
                    'sks' => (int) ($matkul->teori + $matkul->praktek),
                ];
            });

        // Ambil data KRS semester berjalan
        $krs = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $kelas->id_semester)
            ->first();

        return Inertia::render('OrangTua/Krs', [
            'mahasiswa' => [
                'nama_lengkap' => $mahasiswa->nama_lengkap,
                'nim' => $mahasiswa->nim,
                'prodi' => $kelas->prodi->nama_prodi ?? '-',
                'semester' => $kelas->semester->semester ?? '-',
                'pembimbing_akademik' => $mahasiswa->pembimbingAkademik->nama ?? '-',
            ],
            'matkuls' => $matkulKrs,
            'krs' => $krs ? [
                'id' => $krs->id,
                'status_krs' => (int) $krs->status_krs,
                'setuju_pa' => (int) $krs->setuju_pa,
                'setuju_mahasiswa' => (int) $krs->setuju_mahasiswa,
                'tahun_ajaran' => $krs->tahun_ajaran,
                'created_at' => $krs->created_at->translatedFormat('d F Y'),
            ] : null,
        ]);
    }
}
