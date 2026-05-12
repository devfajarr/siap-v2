<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\Mahasiswa;
use App\Models\Jadwal;
use App\Http\Requests\V2\Admin\Kelas\StoreKelasRequest;
use App\Http\Requests\V2\Admin\Kelas\UpdateKelasRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $kelass = Kelas::with(['semester' => function ($query) {
                $query->withTrashed();
            }, 'prodi' => function ($query) {
                $query->withTrashed();
            }])
            ->when($search, function ($query, $search) {
                $query->where('nama_kelas', 'like', "%{$search}%")
                      ->orWhere('kode_kelas', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $prodis = Prodi::all();
        $semesters = Semester::orderBy('semester', 'asc')->get();

        return Inertia::render('Admin/DataKelas/Index', [
            'kelass' => $kelass,
            'prodis' => $prodis,
            'semesters' => $semesters,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreKelasRequest $request)
    {

        $prodi = Prodi::findOrFail($request->id_prodi);
        $semester = Semester::findOrFail($request->id_semester);
        
        // Auto-generate nama_kelas: [Singkatan Prodi] [Semester][Suffix]
        // Suffix: Reguler -> A, Non Reguler -> B
        $suffix = $request->jenis_kelas === 'Reguler' ? 'A' : 'B';
        $namaKelas = "{$prodi->singkatan} {$semester->semester}{$suffix}";

        Kelas::create([
            'id_prodi' => $request->id_prodi,
            'id_semester' => $request->id_semester,
            'jenis_kelas' => $request->jenis_kelas,
            'nama_kelas' => $namaKelas,
            'kode_kelas' => $request->kode_kelas
        ]);

        return redirect()->back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(UpdateKelasRequest $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $prodi = Prodi::findOrFail($request->id_prodi);
        $semester = Semester::findOrFail($request->id_semester);
        
        $suffix = $request->jenis_kelas === 'Reguler' ? 'A' : 'B';
        $namaKelas = "{$prodi->singkatan} {$semester->semester}{$suffix}";

        $kelas->update([
            'id_prodi' => $request->id_prodi,
            'id_semester' => $request->id_semester,
            'jenis_kelas' => $request->jenis_kelas,
            'nama_kelas' => $namaKelas,
            'kode_kelas' => $request->kode_kelas
        ]);

        return redirect()->back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        
        // Cleanup relations as per legacy behavior
        Mahasiswa::where('kelas_id', $id)->forceDelete();
        Jadwal::where('kelas_id', $id)->forceDelete();
        
        $kelas->delete();

        return redirect()->back()->with('success', 'Kelas berhasil dihapus.');
    }
}
