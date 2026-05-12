<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Matkul;
use App\Models\Prodi;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MatkulController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $prodiId = $request->input('prodi_id');
        $semesterId = $request->input('semester_id');

        $matkuls = Matkul::with(['prodi', 'semester'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_matkul', 'like', "%{$search}%")
                        ->orWhere('kode', 'like', "%{$search}%");
                });
            })
            ->when($prodiId, function ($query, $prodiId) {
                $query->where('prodi_id', $prodiId);
            })
            ->when($semesterId, function ($query, $semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/DataMatkul/Index', [
            'matkuls' => $matkuls,
            'filters' => $request->only(['search', 'prodi_id', 'semester_id']),
            'prodis' => Prodi::orderBy('nama_prodi')->get()->unique('nama_prodi')->values(),
            'semesters' => Semester::orderBy('semester')->get()->unique('semester')->values(),

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_matkul' => [
                'required', 'string', 'max:255',
                Rule::unique('matkuls')->where(function ($query) use ($request) {
                    return $query->where('semester_id', $request->semester_id)
                        ->where('prodi_id', $request->prodi_id);
                })
            ],
            'alias' => 'required|string|max:255',
            'kode' => [
                'required', 'string', 'max:50',
                Rule::unique('matkuls')->where(function ($query) use ($request) {
                    return $query->where('semester_id', $request->semester_id)
                        ->where('prodi_id', $request->prodi_id);
                })
            ],
            'prodi_id' => 'required|exists:prodi,id',
            'semester_id' => 'required|exists:semesters,id',
            'teori' => 'required|integer|min:0',
            'praktek' => 'required|integer|min:0',
        ], [
            'nama_matkul.unique' => 'Nama mata kuliah sudah terdaftar untuk kombinasi semester dan prodi ini.',
            'kode.unique' => 'Kode mata kuliah sudah terdaftar untuk kombinasi semester dan prodi ini.',
        ]);

        Matkul::create($validated);

        return redirect()->back()->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $matkul = Matkul::findOrFail($id);
        
        $validated = $request->validate([
            'nama_matkul' => [
                'required', 'string', 'max:255',
                Rule::unique('matkuls')->ignore($matkul->id)->where(function ($query) use ($request) {
                    return $query->where('semester_id', $request->semester_id)
                        ->where('prodi_id', $request->prodi_id);
                })
            ],
            'alias' => 'required|string|max:255',
            'kode' => [
                'required', 'string', 'max:50',
                Rule::unique('matkuls')->ignore($matkul->id)->where(function ($query) use ($request) {
                    return $query->where('semester_id', $request->semester_id)
                        ->where('prodi_id', $request->prodi_id);
                })
            ],
            'prodi_id' => 'required|exists:prodi,id',
            'semester_id' => 'required|exists:semesters,id',
            'teori' => 'required|integer|min:0',
            'praktek' => 'required|integer|min:0',
        ], [
            'nama_matkul.unique' => 'Nama mata kuliah sudah terdaftar untuk kombinasi semester dan prodi ini.',
            'kode.unique' => 'Kode mata kuliah sudah terdaftar untuk kombinasi semester dan prodi ini.',
        ]);

        $matkul->update($validated);

        return redirect()->back()->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $matkul = Matkul::findOrFail($id);
        
        // Force delete schedules to maintain consistency with legacy behavior
        $matkul->jadwal()->forceDelete();
        $matkul->delete();

        return redirect()->back()->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
