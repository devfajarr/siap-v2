<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Matkul;
use App\Services\FeederTokenService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class KrsController extends Controller
{
    /**
     * Menampilkan daftar kelas yang dikelompokkan per semester dan prodi.
     */
    public function index()
    {
        $kelass = Kelas::with([
            'prodi' => fn ($q) => $q->withTrashed(),
            'semester' => fn ($q) => $q->withTrashed(),
        ])
            ->withCount('mahasiswa')
            ->get()
            ->map(function ($kelas) {
                return [
                    'id' => $kelas->id,
                    'nama_kelas' => $kelas->nama_kelas,
                    'prodi' => $kelas->prodi?->nama_prodi,
                    'prodi_singkatan' => $kelas->prodi?->singkatan,
                    'id_prodi' => $kelas->id_prodi,
                    'semester' => $kelas->semester?->semester,
                    'id_semester' => $kelas->id_semester,
                    'semester_status' => $kelas->semester?->status,  // 1=Aktif, 0=Non-Aktif
                    'jenis_kelas' => $kelas->jenis_kelas,
                    'mahasiswa_count' => $kelas->mahasiswa_count,
                ];
            });

        return Inertia::render('Admin/Krs/Index', [
            'kelass' => $kelass,
        ]);
    }

    /**
     * Menampilkan daftar mahasiswa dalam kelas tertentu.
     */
    public function show($id)
    {
        $kelas = Kelas::with(['prodi', 'semester'])->findOrFail($id);

        $mahasiswas = Mahasiswa::with(['kelas.semester', 'kelas.prodi', 'pembimbingAkademik'])
            ->where('kelas_id', $id)
            ->orderBy('nim', 'asc')
            ->get();

        return Inertia::render('Admin/Krs/Show', [
            'namaKelas' => $kelas,
            'mahasiswas' => $mahasiswas,
        ]);
    }

    /**
     * Menampilkan pratinjau cetak KRS untuk mahasiswa tertentu.
     */
    public function cetak(int|string $id): View
    {
        $mahasiswa = Mahasiswa::with(['kelas.prodi', 'kelas.semester', 'pembimbingAkademik'])->findOrFail($id);
        $semesterId = $mahasiswa->kelas?->id_semester;
        $prodiId = $mahasiswa->kelas?->id_prodi;

        $krs = Krs::with([
            'prodi',
            'semester',
            'mahasiswa.kelas',
            'mahasiswa.pembimbingAkademik',
        ])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterId)
            ->where('prodi_id', $prodiId)
            ->firstOrFail();

        $matkulKrs = Matkul::where('prodi_id', $prodiId)
            ->where('semester_id', $semesterId)
            ->get();

        return view('pages.krs.cetak', compact('krs', 'matkulKrs'));
    }

    /**
     * Sinkronisasi data KRS kelas rombel ke Neo Feeder PDDIKTI.
     */
    public function syncClass(int $id, FeederTokenService $feederService): RedirectResponse
    {
        $result = $feederService->syncKrsRombelToFeeder($id);

        if (! $result['success']) {
            return back()->with('error', 'Gagal sinkronisasi ke Feeder: '.$result['message']);
        }

        return back()->with('success', "Berhasil sinkronisasi KRS ke Feeder! Synced: {$result['synced_classes']} Kelas, {$result['synced_lecturers']} Dosen Pengajar, {$result['synced_krs_records']} KRS Peserta.");
    }
}
