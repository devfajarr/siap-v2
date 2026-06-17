<?php

namespace App\Http\Controllers\V2\Admin;

use App\Exports\AllMahasiswaExport;
use App\Exports\MahasiswaExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Admin\Mahasiswa\StoreMahasiswaRequest;
use App\Http\Requests\V2\Admin\Mahasiswa\UpdateMahasiswaRequest;
use App\Imports\MahasiswaImport;
use App\Models\Dosen;
use App\Models\FeederAgama;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource (Classes Grid).
     */
    public function index()
    {
        $kelass = Kelas::with([
            'prodi' => fn ($q) => $q->withTrashed(),
            'semester' => fn ($q) => $q->withTrashed(),
            'mahasiswa',
        ])
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
                    'mahasiswa_count' => $kelas->mahasiswa->where('status_mahasiswa', 'Aktif')->count(),
                ];
            });

        return Inertia::render('Admin/Mahasiswa/Index', [
            'kelass' => $kelass,
        ]);
    }

    /**
     * Display a directory of all students with global pagination and filters.
     */
    public function allStudents(Request $request)
    {
        $query = Mahasiswa::with(['kelas.semester', 'kelas.prodi', 'pembimbingAkademik', 'orangTuas', 'feederWilayah.parent.parent']);

        // Filter search (NIM or Name)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('mahasiswas.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('mahasiswas.nim', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status !== 'all') {
                $query->where('mahasiswas.status_mahasiswa', $status);
            }
        }

        // Filter prodi
        if ($request->filled('prodi_id')) {
            $prodiId = $request->input('prodi_id');
            $query->where(function ($q) use ($prodiId) {
                $q->whereHas('kelas', function ($sq) use ($prodiId) {
                    $sq->where('id_prodi', $prodiId);
                })->orWhereExists(function ($sq) {
                    // Fallback lookup if no class is set (alumni) but we know their entry year/feeder prodi mapping
                    // We can check if their NIM/registration prodi matches, or keep it class-based
                });
            });
        }

        // Filter angkatan
        if ($request->filled('angkatan')) {
            $query->where('mahasiswas.tahun_masuk', $request->input('angkatan'));
        }

        /**
         * Sorting multi-level:
         * 1. Mahasiswa berstatus 'Aktif' didahulukan.
         * 2. Semester aktif (status=1) yang bukan semester pendek (ganjil/genap) didahulukan.
         *    Logika pendek: nilai semester % 3 == 0 (mis. 3, 6, 9 dst).
         * 3. Nilai semester ganjil/genap terbesar (terbaru) diutamakan.
         * 4. NIM ascending sebagai tiebreaker akhir.
         */
        $query->select('mahasiswas.*')
            ->leftJoin('kelas', 'mahasiswas.kelas_id', '=', 'kelas.id')
            ->leftJoin('semesters', 'kelas.id_semester', '=', 'semesters.id')
            ->orderByRaw("CASE WHEN mahasiswas.status_mahasiswa = 'Aktif' THEN 0 ELSE 1 END ASC")
            ->orderByRaw('CASE WHEN semesters.status = 1 AND MOD(semesters.semester, 3) != 0 THEN 0 ELSE 1 END ASC')
            ->orderByRaw('CASE WHEN MOD(semesters.semester, 3) != 0 THEN semesters.semester ELSE NULL END DESC')
            ->orderBy('mahasiswas.nim', 'asc');

        // Pagination (standard 15 items per page)
        $mahasiswas = $query->paginate(15)->withQueryString();

        // Get filter options
        $prodis = Prodi::withTrashed()->orderBy('nama_prodi', 'asc')->get()->map(function ($prodi) {
            return [
                'id' => $prodi->id,
                'nama_prodi' => $prodi->nama_prodi,
            ];
        });

        $angkatanList = Mahasiswa::select('tahun_masuk')
            ->distinct()
            ->orderBy('tahun_masuk', 'desc')
            ->pluck('tahun_masuk')
            ->filter()
            ->values();

        // Stats for quick overview
        $stats = [
            'total' => Mahasiswa::count(),
            'active' => Mahasiswa::where('status_mahasiswa', 'Aktif')->count(),
            'graduated' => Mahasiswa::where('status_mahasiswa', 'like', '%lulus%')->count(),
            'other' => Mahasiswa::where('status_mahasiswa', '!=', 'Aktif')->where('status_mahasiswa', 'not like', '%lulus%')->count(),
        ];

        $allKelas = Kelas::with(['prodi', 'semester'])->get();
        $dosens = Dosen::where('status', 1)->orderBy('nama', 'asc')->get();

        return Inertia::render('Admin/Mahasiswa/All', [
            'mahasiswas' => $mahasiswas,
            'prodis' => $prodis,
            'angkatanList' => $angkatanList,
            'filters' => $request->only(['search', 'status', 'prodi_id', 'angkatan']),
            'stats' => $stats,
            'allKelas' => $allKelas,
            'dosens' => $dosens,
            'agamas' => FeederAgama::all(),
        ]);
    }

    /**
     * Display students in a specific class.
     */
    public function show($id)
    {
        $kelas = Kelas::with(['prodi', 'semester'])->findOrFail($id);

        $mahasiswas = Mahasiswa::with(['kelas.semester', 'kelas.prodi', 'pembimbingAkademik', 'orangTuas', 'feederWilayah.parent.parent'])
            ->where('kelas_id', $id)
            ->where('status_mahasiswa', 'Aktif')
            ->orderBy('nim', 'asc')
            ->get();

        $allKelas = Kelas::with(['prodi', 'semester'])->get();

        // Kelas seprodi & sejenis (pindah rombel / naik semester dalam jenis yang sama)
        $kelasSamProdi = Kelas::with(['prodi', 'semester'])
            ->where('id', '!=', $id)
            ->where('id_prodi', $kelas->id_prodi)
            ->where('jenis_kelas', $kelas->jenis_kelas)
            ->orderBy('id_semester')
            ->get();

        // Kelas seprodi & lintas jenis (Reguler ↔ Karyawan), bebas semua semester
        $kelasLintas = Kelas::with(['prodi', 'semester'])
            ->where('id', '!=', $id)
            ->where('id_prodi', $kelas->id_prodi)
            ->where('jenis_kelas', '!=', $kelas->jenis_kelas)
            ->orderBy('id_semester')
            ->get();

        $dosens = Dosen::where('status', 1)
            ->orderBy('nama', 'asc')
            ->get();

        return Inertia::render('Admin/Mahasiswa/Detail', [
            'namaKelas' => $kelas,
            'mahasiswas' => $mahasiswas,
            'allKelas' => $allKelas,
            'kelasSamProdi' => $kelasSamProdi,
            'kelasLintas' => $kelasLintas,
            'dosens' => $dosens,
            'agamas' => FeederAgama::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMahasiswaRequest $request)
    {
        $validated = $request->validated();

        Mahasiswa::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'is_first_login' => true,
        ]);

        return redirect()->back()->with('success', 'Mahasiswa berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMahasiswaRequest $request, $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $validated = $request->validated();

        $updateData = collect($validated)->except('password')->toArray();

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
            $updateData['is_first_login'] = true;
        }

        $mahasiswa->update($updateData);

        return redirect()->back()->with('success', 'Data mahasiswa berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        if ($mahasiswa->profile_picture && Storage::exists('public/profile_pictures/'.$mahasiswa->profile_picture)) {
            Storage::delete('public/profile_pictures/'.$mahasiswa->profile_picture);
        }

        $mahasiswa->delete();

        return redirect()->back()->with('success', 'Data mahasiswa berhasil dihapus');
    }

    /**
     * Bulk Delete.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        $mahasiswas = Mahasiswa::whereIn('id', $ids)->get();

        foreach ($mahasiswas as $mahasiswa) {
            if ($mahasiswa->profile_picture && Storage::exists('public/profile_pictures/'.$mahasiswa->profile_picture)) {
                Storage::delete('public/profile_pictures/'.$mahasiswa->profile_picture);
            }
            $mahasiswa->delete();
        }

        return redirect()->back()->with('success', count($ids).' data mahasiswa berhasil dihapus');
    }

    /**
     * Bulk move students to another class.
     */
    public function pindahKelas(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:mahasiswas,id',
            'kelas_id' => 'required|exists:kelas,id',
            'source_kelas_id' => 'required|exists:kelas,id',
        ]);

        $sourceKelas = Kelas::findOrFail($request->source_kelas_id);
        $targetKelas = Kelas::findOrFail($request->kelas_id);

        if ($targetKelas->id_prodi !== $sourceKelas->id_prodi) {
            return back()->withErrors([
                'kelas_id' => 'Kelas tujuan harus berada dalam program studi yang sama dengan kelas asal.',
            ]);
        }

        Mahasiswa::whereIn('id', $request->ids)->update([
            'kelas_id' => $request->kelas_id,
            'status_krs' => 0,
        ]);

        $isLintasJenis = $targetKelas->jenis_kelas !== $sourceKelas->jenis_kelas;
        $jumlah = count($request->ids);
        $message = $isLintasJenis
            ? "{$jumlah} mahasiswa berhasil dipindahkan ke kelas {$targetKelas->jenis_kelas} ({$targetKelas->nama_kelas})"
            : "{$jumlah} mahasiswa berhasil dipindahkan ke kelas {$targetKelas->nama_kelas}";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Import data from Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'kelas_id' => 'required|exists:kelas,id',
        ], ['file.mimes' => 'Format file tidak sesuai']);

        DB::beginTransaction();
        try {
            Excel::import(new MahasiswaImport($request->kelas_id), $request->file('file'));
            DB::commit();

            return redirect()->back()->with('success', 'Data mahasiswa berhasil diimpor');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal impor data: '.$e->getMessage());
        }
    }

    /**
     * Export students of a specific class.
     */
    public function export($id)
    {
        $kelas = Kelas::findOrFail($id);

        return Excel::download(new MahasiswaExport($id), 'mahasiswa_'.$kelas->nama_kelas.'.xlsx');
    }

    /**
     * Export all students.
     */
    public function exportAll()
    {
        return Excel::download(new AllMahasiswaExport, 'mahasiswa_all.xlsx');
    }
}
