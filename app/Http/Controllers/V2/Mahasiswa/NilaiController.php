<?php

namespace App\Http\Controllers\V2\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Matkul;
use App\Models\NilaiHuruf;
use App\Models\Pembayaran;
use App\Models\PengajuanCetakKhs;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        if (! $kelas || ! $kelas->id_semester) {
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
                'pembayaran' => null,
                'summary' => [
                    'total_sks' => 0,
                    'ips' => '0.00',
                    'matkul_dinilai' => 0,
                    'total_matkul' => 0,
                    'can_print' => false,
                    'can_view' => false,
                    'semester_id' => null,
                    'pengajuan' => null,
                ],
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

        if (! $kelas) {
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

        // Ambil data pembayaran untuk semester yang ditargetkan
        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semester_id)
            ->latest()
            ->first();

        $isLunas = $pembayaran && $pembayaran->status_pembayaran === 1 && $pembayaran->keterangan === 'Sudah';

        if (! $isLunas) {
            return Inertia::render('Mahasiswa/Nilai/Index', [
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
                    'bukti_pembayaran' => $pembayaran->bukti_pembayaran,
                    'created_at' => $pembayaran->created_at->translatedFormat('d F Y H:i'),
                ] : null,
                'summary' => [
                    'total_sks' => 0,
                    'ips' => '0.00',
                    'matkul_dinilai' => 0,
                    'total_matkul' => 0,
                    'can_print' => false,
                    'can_view' => false,
                    'semester_id' => $targetSemester->id,
                    'pengajuan' => null,
                ],
            ]);
        }

        // Jika Lunas, ambil data nilai KHS secara normal
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

        // Ambil data pengajuan cetak KHS terakhir
        $pengajuan = PengajuanCetakKhs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semester_id)
            ->first();

        return Inertia::render('Mahasiswa/Nilai/Index', [
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
                'bukti_pembayaran' => $pembayaran->bukti_pembayaran,
                'created_at' => $pembayaran->created_at->translatedFormat('d F Y H:i'),
            ] : null,
            'summary' => [
                'total_sks' => $totalSks,
                'ips' => number_format($ips, 2),
                'matkul_dinilai' => $matkulDinilai,
                'total_matkul' => $totalMatkul,
                'can_print' => $canPrint,
                'can_view' => true,
                'semester_id' => $targetSemester->id,
                'pengajuan' => $pengajuan ? [
                    'id' => $pengajuan->id,
                    'status' => (int) $pengajuan->status,
                    'keterangan' => $pengajuan->keterangan,
                    'created_at' => $pengajuan->created_at->translatedFormat('d F Y H:i'),
                ] : null,
            ],
        ]);
    }

    /**
     * Memproses pengajuan cetak KHS fisik oleh mahasiswa.
     */
    public function ajukanCetak(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $user = auth()->guard('mahasiswa')->user();
        $mahasiswa = Mahasiswa::findOrFail($user->id);
        $semesterId = $request->semester_id;

        // Validasi Pembayaran
        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterId)
            ->first();

        if (! $pembayaran || $pembayaran->status_pembayaran !== 1 || $pembayaran->keterangan !== 'Sudah') {
            return redirect()->back()->with('error', 'Pengajuan cetak KHS hanya dapat dilakukan setelah pembayaran semester terverifikasi lunas.');
        }

        // Cek double pengajuan aktif
        $pengajuan = PengajuanCetakKhs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterId)
            ->first();

        if ($pengajuan && $pengajuan->status == 0) {
            return redirect()->back()->with('error', 'Anda sudah mengajukan cetak KHS untuk semester ini.');
        }

        if ($pengajuan) {
            $pengajuan->update([
                'status' => 0,
                'keterangan' => null,
            ]);
        } else {
            PengajuanCetakKhs::create([
                'mahasiswa_id' => $mahasiswa->id,
                'semester_id' => $semesterId,
                'status' => 0,
                'keterangan' => null,
            ]);
        }

        return redirect()->back()->with('success', 'Permohonan cetak KHS berhasil dikirim ke bagian akademik.');
    }

    /**
     * Mengunggah bukti pembayaran via halaman KHS.
     */
    public function uploadPembayaran(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $user = auth()->guard('mahasiswa')->user();
        $mahasiswa = Mahasiswa::findOrFail($user->id);
        $semesterId = $request->semester_id;

        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterId)
            ->first();

        $path = $request->file('file')->store('bukti_pembayaran', 'public');

        if ($pembayaran) {
            if ($pembayaran->bukti_pembayaran && Storage::disk('public')->exists($pembayaran->bukti_pembayaran)) {
                Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
            }
            $pembayaran->update([
                'bukti_pembayaran' => $path,
                'status_pembayaran' => 0,
                'keterangan' => 'Belum',
            ]);
        } else {
            Pembayaran::create([
                'mahasiswa_id' => $mahasiswa->id,
                'semester_id' => $semesterId,
                'jumlah_pembayaran' => 0,
                'bukti_pembayaran' => $path,
                'status_pembayaran' => 0,
                'keterangan' => 'Belum',
            ]);
        }

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah dan sedang menunggu verifikasi administrasi.');
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
