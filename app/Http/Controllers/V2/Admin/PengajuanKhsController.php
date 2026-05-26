<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kaprodi;
use App\Models\Mahasiswa;
use App\Models\Matkul;
use App\Models\NilaiHuruf;
use App\Models\PengajuanCetakKhs;
use App\Models\Semester;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengajuanKhsController extends Controller
{
    /**
     * Menampilkan daftar pengajuan cetak KHS mahasiswa.
     */
    public function index()
    {
        $pengajuans = PengajuanCetakKhs::with([
            'mahasiswa.kelas.prodi',
            'mahasiswa.kelas.semester',
            'semester',
        ])
            ->latest()
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'mahasiswa_id' => $p->mahasiswa_id,
                    'nim' => $p->mahasiswa->nim,
                    'nama_lengkap' => $p->mahasiswa->nama_lengkap,
                    'prodi' => $p->mahasiswa->kelas->prodi->nama_prodi ?? '-',
                    'kelas' => $p->mahasiswa->kelas->nama_kelas ?? '-',
                    'semester_id' => $p->semester_id,
                    'semester' => $p->semester->semester,
                    'semester_nama' => $p->semester->nama_semester,
                    'status' => (int) $p->status,
                    'keterangan' => $p->keterangan,
                    'created_at' => $p->created_at->translatedFormat('d F Y H:i'),
                ];
            });

        return Inertia::render('Admin/PengajuanKhs/Index', [
            'pengajuans' => $pengajuans,
        ]);
    }

    /**
     * Memperbarui status pengajuan cetak KHS (Setuju/Tolak/Selesai).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:1,2',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $pengajuan = PengajuanCetakKhs::findOrFail($id);

        $pengajuan->update([
            'status' => (int) $request->status,
            'keterangan' => $request->keterangan,
            'petugas_id' => auth()->guard('admin')->user()->id,
        ]);

        $message = $request->status == 1
            ? 'Status pengajuan berhasil diubah menjadi selesai/siap diambil.'
            : 'Pengajuan cetak KHS berhasil ditolak.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Membuka halaman pratinjau cetak KHS resmi untuk mahasiswa.
     * Mengubah status pengajuan secara otomatis menjadi 'Selesai' (1) saat dibuka.
     */
    public function cetak(Request $request, $mahasiswa_id, $semester_id)
    {
        $mahasiswa = Mahasiswa::with(['kelas.prodi', 'kelas.semester', 'pembimbingAkademik'])->findOrFail($mahasiswa_id);
        $kelas = $mahasiswa->kelas;
        $prodi = $kelas->prodi;
        $targetSemester = Semester::findOrFail($semester_id);

        // Cari pengajuan pending untuk semester ini, lalu otomatis set ke Selesai (1)
        $pengajuan = PengajuanCetakKhs::where('mahasiswa_id', $mahasiswa_id)
            ->where('semester_id', $semester_id)
            ->first();
        if ($pengajuan && $pengajuan->status == 0) {
            $pengajuan->update([
                'status' => 1,
                'petugas_id' => auth()->guard('admin')->user()->id,
            ]);
        }

        $ipks = NilaiHuruf::with(['matkul', 'kelas.prodi'])
            ->where('mahasiswa_id', $mahasiswa_id)
            ->whereHas('kelas.prodi', function ($query) use ($prodi) {
                $query->where('id', $prodi->id);
            })
            ->whereHas('kelas.semester', function ($query) use ($targetSemester) {
                $query->where('semester', '<=', $targetSemester->semester);
            })
            ->get();

        $ipss = NilaiHuruf::with(['matkul', 'kelas.prodi'])
            ->where('mahasiswa_id', $mahasiswa_id)
            ->whereHas('kelas.prodi', function ($query) use ($prodi) {
                $query->where('id', $prodi->id);
            })
            ->whereHas('kelas', function ($query) use ($targetSemester) {
                $query->where('id_semester', $targetSemester->id);
            })
            ->get();

        // Jika data IPS kosong (untuk keperluan testing/bypassing), buat dummy record agar tidak error di view blade
        if ($ipss->isEmpty()) {
            $dummyNilai = new NilaiHuruf;
            $dummyNilai->mahasiswa_id = $mahasiswa->id;

            $dummyMatkul = new Matkul;
            $dummyMatkul->kode = '-';
            $dummyMatkul->nama_matkul = 'Belum Ada Nilai / Matakuliah';
            $dummyMatkul->praktek = 0;
            $dummyMatkul->teori = 0;

            $dummyNilai->setRelation('mahasiswa', $mahasiswa);
            $dummyNilai->setRelation('matkul', $dummyMatkul);
            $dummyNilai->nilai_huruf = '-';

            $ipss->push($dummyNilai);
        }

        $tahunAkademik = TahunAkademik::where('status', 1)->first();
        $tahunAkademikFormatted = $tahunAkademik ? str_replace('/', '-', $tahunAkademik->tahun_akademik) : date('Y');

        $kaprodi = Kaprodi::where('status', 1)
            ->whereHas('prodis', function ($q) use ($kelas) {
                $q->where('prodi.id', $kelas->id_prodi ?? 1);
            })
            ->first();

        return view('pages.mahasiswa.nilai.khs', compact('ipks', 'ipss', 'tahunAkademikFormatted', 'kaprodi'));
    }
}
