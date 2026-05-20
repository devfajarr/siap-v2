<?php

namespace App\Http\Controllers\V2\Mahasiswa;

use App\Models\Kelas;
use App\Models\Matkul;
use App\Models\Kaprodi;
use App\Models\Semester;
use App\Models\Mahasiswa;
use App\Models\NilaiHuruf;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class NilaiController extends Controller
{
    /**
     * Menampilkan daftar nilai KHS semester aktif saat ini.
     */
    public function index(Request $request)
    {
        $user = auth()->guard('mahasiswa')->user();
        $mahasiswa = Mahasiswa::with(['kelas.prodi', 'kelas.semester'])->findOrFail($user->id);
        $kelas = $mahasiswa->kelas;

        if (!$kelas || !$kelas->id_semester) {
            return Inertia::render('Mahasiswa/Nilai/Index', [
                'mahasiswa' => [
                    'nama_lengkap' => $mahasiswa->nama_lengkap,
                    'nim' => $mahasiswa->nim,
                    'prodi' => $kelas->prodi->nama_prodi ?? '-',
                    'semester' => 'Belum Ada',
                ],
                'semesterRiwayat' => null,
                'isRiwayat' => false,
                'matkuls' => [],
                'summary' => [
                    'total_sks' => 0,
                    'ips' => 0,
                    'matkul_dinilai' => 0,
                    'total_matkul' => 0,
                    'can_print' => false,
                    'semester_id' => null,
                ]
            ]);
        }

        return $this->renderNilaiView($mahasiswa, $kelas, $kelas->id_semester, false);
    }

    /**
     * Menampilkan riwayat nilai berdasarkan semester tertentu.
     */
    public function riwayat(Request $request, $semester_id)
    {
        $user = auth()->guard('mahasiswa')->user();
        $mahasiswa = Mahasiswa::with(['kelas.prodi', 'kelas.semester'])->findOrFail($user->id);
        $kelas = $mahasiswa->kelas;

        if (!$kelas) {
            return redirect()->route('v2.mahasiswa.nilai.index');
        }

        // Security check: ensure student can only access semesters they have reached
        $targetSemester = Semester::findOrFail($semester_id);
        if ($targetSemester->semester > ($kelas->semester->semester ?? 0)) {
            return redirect()->route('v2.mahasiswa.nilai.index')->with('error', 'Anda belum mencapai semester ini.');
        }

        return $this->renderNilaiView($mahasiswa, $kelas, $semester_id, true);
    }

    /**
     * Helper untuk me-render halaman nilai baik semester aktif maupun riwayat.
     */
    protected function renderNilaiView($mahasiswa, $kelas, $semester_id, $isRiwayat)
    {
        $targetSemester = Semester::findOrFail($semester_id);

        $matkuls = Matkul::where('prodi_id', $kelas->id_prodi)
            ->where('semester_id', $semester_id)
            ->get();

        $nilais = NilaiHuruf::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semester_id)
            ->get();

        $combinedData = $matkuls->map(function ($matkul) use ($nilais) {
            $nilai = $nilais->firstWhere('matkul_id', $matkul->id);
            $sks = ($matkul->praktek ?? 0) + ($matkul->teori ?? 0);
            $nilaiHuruf = $nilai ? $nilai->nilai_huruf : null;
            $kredit = self::calculateKredit($nilaiHuruf, $sks);

            return [
                'id' => $matkul->id,
                'kode' => $matkul->kode,
                'nama_matkul' => $matkul->nama_matkul,
                'sks' => $sks,
                'nilai_total' => $nilai ? $nilai->nilai_total : null,
                'nilai_huruf' => $nilaiHuruf ?? 'Belum Dinilai',
                'kredit' => $kredit,
            ];
        });

        $totalSks = $combinedData->sum('sks');
        $totalKredit = $combinedData->sum('kredit');
        $ips = $totalSks > 0 ? round($totalKredit / $totalSks, 2) : 0;
        $matkulDinilai = $combinedData->where('nilai_huruf', '!=', 'Belum Dinilai')->count();
        $totalMatkul = $combinedData->count();

        // Bisa cetak jika ada minimal 1 matkul yang sudah dinilai
        $canPrint = $matkulDinilai > 0;

        return Inertia::render('Mahasiswa/Nilai/Index', [
            'mahasiswa' => [
                'nama_lengkap' => $mahasiswa->nama_lengkap,
                'nim' => $mahasiswa->nim,
                'prodi' => $kelas->prodi->nama_prodi ?? '-',
                'semester' => 'Semester ' . $targetSemester->semester,
            ],
            'semesterRiwayat' => $targetSemester,
            'isRiwayat' => $isRiwayat,
            'matkuls' => $combinedData,
            'summary' => [
                'total_sks' => $totalSks,
                'ips' => number_format($ips, 2),
                'matkul_dinilai' => $matkulDinilai,
                'total_matkul' => $totalMatkul,
                'can_print' => $canPrint,
                'semester_id' => $targetSemester->id,
            ]
        ]);
    }

    /**
     * Menampilkan halaman cetak KHS formal.
     */
    public function khs(Request $request, $semester_id)
    {
        $user = auth()->guard('mahasiswa')->user();
        $mahasiswa = Mahasiswa::with(['kelas.prodi', 'kelas.semester', 'pembimbingAkademik'])->findOrFail($user->id);
        $kelas = $mahasiswa->kelas;
        $targetSemester = Semester::findOrFail($semester_id);

        // Security check: ensure student can only access semesters they have reached
        if ($targetSemester->semester > ($kelas->semester->semester ?? 0)) {
            return redirect()->route('v2.mahasiswa.nilai.index')->with('error', 'Anda belum mencapai semester ini.');
        }

        $ipss = NilaiHuruf::with('matkul')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semester_id)
            ->get();

        $ipks = NilaiHuruf::with('matkul')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('semester', function ($query) use ($targetSemester) {
                $query->where('semester', '<=', $targetSemester->semester);
            })
            ->get();

        $formattedIpss = $ipss->map(function ($item) {
            $sks = ($item->matkul->praktek ?? 0) + ($item->matkul->teori ?? 0);
            $kredit = self::calculateKredit($item->nilai_huruf, $sks);
            return [
                'kode' => $item->matkul->kode ?? '-',
                'nama_matkul' => $item->matkul->nama_matkul ?? '-',
                'alias' => $item->matkul->alias ?? '-',
                'sks' => $sks,
                'nilai_huruf' => $item->nilai_huruf ?? '-',
                'kredit' => $kredit,
            ];
        });

        $sksIps = $formattedIpss->sum('sks');
        $kreditIps = $formattedIpss->sum('kredit');
        $ips = $sksIps > 0 ? round($kreditIps / $sksIps, 2) : 0;

        $sksIpk = $ipks->sum(function ($item) {
            return ($item->matkul->praktek ?? 0) + ($item->matkul->teori ?? 0);
        });
        $kreditIpk = $ipks->sum(function ($item) {
            $sks = ($item->matkul->praktek ?? 0) + ($item->matkul->teori ?? 0);
            return self::calculateKredit($item->nilai_huruf, $sks);
        });
        $ipk = $sksIpk > 0 ? round($kreditIpk / $sksIpk, 2) : 0;

        $tahunAkademik = TahunAkademik::where('status', 1)->first();
        $tahunAkademikFormatted = $tahunAkademik ? str_replace('/', '-', $tahunAkademik->tahun_akademik) : date('Y');

        // Mengatasi bug hardcode Kaprodi dengan menggunakan id_prodi aktual mahasiswa
        $kaprodi = Kaprodi::where('prodis_id', $kelas->id_prodi ?? 1)->where('status', 1)->first();

        return Inertia::render('Mahasiswa/Nilai/Khs', [
            'mahasiswa' => [
                'nama_lengkap' => $mahasiswa->nama_lengkap,
                'nim' => $mahasiswa->nim,
                'prodi_nama' => $kelas->prodi->nama_prodi ?? '-',
                'prodi_alias' => $kelas->prodi->alias_nama ?? '-',
                'jenjang' => $kelas->prodi->jenjang ?? '-',
                'alias_jenjang' => $kelas->prodi->alias_jenjang ?? '-',
                'semester_angka' => $targetSemester->semester,
                'semester_romawi' => self::toRoman($targetSemester->semester),
                'pembimbing_akademik' => $mahasiswa->pembimbingAkademik->nama ?? '................................',
            ],
            'tahunAkademik' => $tahunAkademikFormatted,
            'kaprodi' => $kaprodi->nama ?? '................................',
            'items' => $formattedIpss,
            'rekap' => [
                'sks_ips' => $sksIps,
                'kredit_ips' => $kreditIps,
                'ips' => number_format($ips, 2),
                'sks_ipk' => $sksIpk,
                'kredit_ipk' => $kreditIpk,
                'ipk' => number_format($ipk, 2),
            ]
        ]);
    }

    public static function calculateKredit($nilai, $sks)
    {
        $nilaiToKredit = [
            'A' => 4,
            'A-' => 3.7,
            'B+' => 3.4,
            'B' => 3,
            'B-' => 2.7,
            'C+' => 2.4,
            'C' => 2,
            'C-' => 1.7,
            'D' => 1,
            'E' => 0,
        ];

        $bobot = $nilaiToKredit[$nilai] ?? 0;
        return $bobot * $sks;
    }

    public static function toRoman($num)
    {
        $n = intval($num);
        $result = '';

        $romanNumerals = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I',
        ];

        foreach ($romanNumerals as $value => $symbol) {
            while ($n >= $value) {
                $result .= $symbol;
                $n -= $value;
            }
        }

        return $result;
    }
}
