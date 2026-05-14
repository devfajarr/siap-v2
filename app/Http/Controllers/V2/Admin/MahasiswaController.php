<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\Dosen;
use App\Http\Requests\V2\Admin\Mahasiswa\StoreMahasiswaRequest;
use App\Http\Requests\V2\Admin\Mahasiswa\UpdateMahasiswaRequest;
use App\Exports\MahasiswaExport;
use App\Exports\AllMahasiswaExport;
use App\Imports\MahasiswaImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource (Classes Grid).
     */
    public function index()
    {
        $kelass = Kelas::with([
            'prodi' => fn($q) => $q->withTrashed(),
            'semester' => fn($q) => $q->withTrashed(),
            'mahasiswa'
        ])
        ->get()
        ->map(function ($kelas) {
            return [
                'id'              => $kelas->id,
                'nama_kelas'      => $kelas->nama_kelas,
                'prodi'           => $kelas->prodi?->nama_prodi,
                'prodi_singkatan' => $kelas->prodi?->singkatan,
                'id_prodi'        => $kelas->id_prodi,
                'semester'        => $kelas->semester?->semester,
                'id_semester'     => $kelas->id_semester,
                'semester_status' => $kelas->semester?->status,  // 1=Aktif, 0=Non-Aktif
                'jenis_kelas'     => $kelas->jenis_kelas,
                'mahasiswa_count' => $kelas->mahasiswa->count(),
            ];
        });

        return Inertia::render('Admin/Mahasiswa/Index', [
            'kelass' => $kelass,
        ]);
    }


    /**
     * Display students in a specific class.
     */
    public function show($id)
    {
        $kelas = Kelas::with(['prodi', 'semester'])->findOrFail($id);
        
        $mahasiswas = Mahasiswa::with(['kelas.semester', 'kelas.prodi', 'pembimbingAkademik'])
            ->where('kelas_id', $id)
            ->orderBy('nim', 'asc')
            ->get();

        $allKelas = Kelas::with(['prodi', 'semester'])->get();
        
        // Kelas satu prodi & jenis kelas sama (untuk "naik semester / pindah rombel")
        $kelasSamProdi = Kelas::with(['prodi', 'semester'])
            ->where('id', '!=', $id)
            ->where('id_prodi', $kelas->id_prodi)
            ->where('jenis_kelas', $kelas->jenis_kelas)
            ->get();

        $dosens = Dosen::where('pembimbing_akademik', 1)
            ->where('status', 1)
            ->get();

        return Inertia::render('Admin/Mahasiswa/Detail', [
            'namaKelas'    => $kelas,
            'mahasiswas'   => $mahasiswas,
            'allKelas'     => $allKelas,
            'kelasSamProdi'=> $kelasSamProdi,
            'dosens'       => $dosens,
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

        if ($mahasiswa->profile_picture && Storage::exists('public/profile_pictures/' . $mahasiswa->profile_picture)) {
            Storage::delete('public/profile_pictures/' . $mahasiswa->profile_picture);
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
            if ($mahasiswa->profile_picture && Storage::exists('public/profile_pictures/' . $mahasiswa->profile_picture)) {
                Storage::delete('public/profile_pictures/' . $mahasiswa->profile_picture);
            }
            $mahasiswa->delete();
        }

        return redirect()->back()->with('success', count($ids) . ' data mahasiswa berhasil dihapus');
    }

    /**
     * Bulk move students to another class.
     */
    public function pindahKelas(Request $request)
    {
        $request->validate([
            'ids'             => 'required|array',
            'kelas_id'        => 'required|exists:kelas,id',
            'source_kelas_id' => 'required|exists:kelas,id',
        ]);

        $sourceKelas = Kelas::findOrFail($request->source_kelas_id);
        $targetKelas = Kelas::findOrFail($request->kelas_id);

        if (
            $targetKelas->id_prodi   !== $sourceKelas->id_prodi ||
            $targetKelas->jenis_kelas !== $sourceKelas->jenis_kelas
        ) {
            return back()->withErrors([
                'kelas_id' => 'Kelas tujuan harus memiliki program studi dan jenis kelas yang sama dengan kelas asal.',
            ]);
        }

        Mahasiswa::whereIn('id', $request->ids)->update([
            'kelas_id'   => $request->kelas_id,
            'status_krs' => 0,
        ]);

        return redirect()->back()->with('success', count($request->ids) . ' mahasiswa berhasil dipindahkan kelas');
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
            return redirect()->back()->with('error', 'Gagal impor data: ' . $e->getMessage());
        }
    }

    /**
     * Export students of a specific class.
     */
    public function export($id)
    {
        $kelas = Kelas::findOrFail($id);
        return Excel::download(new MahasiswaExport($id), 'mahasiswa_' . $kelas->nama_kelas . '.xlsx');
    }

    /**
     * Export all students.
     */
    public function exportAll()
    {
        return Excel::download(new AllMahasiswaExport, 'mahasiswa_all.xlsx');
    }
}
