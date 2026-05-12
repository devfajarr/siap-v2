<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use App\Models\Kelas;
use App\Models\Kaprodi;
use App\Http\Requests\V2\Admin\Prodi\StoreProdiRequest;
use App\Http\Requests\V2\Admin\Prodi\UpdateProdiRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProdiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $prodis = Prodi::when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_prodi', 'like', "%{$search}%")
                        ->orWhere('kode_prodi', 'like', "%{$search}%")
                        ->orWhere('singkatan', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/DataProdi/Index', [
            'prodis' => $prodis,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProdiRequest $request)
    {
        Prodi::create($request->validated());

        return redirect()->back()->with('success', 'Program studi berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProdiRequest $request, $id)
    {
        $prodi = Prodi::findOrFail($id);

        $prodi->update($request->validated());

        return redirect()->back()->with('success', 'Program studi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $prodi = Prodi::findOrFail($id);
        
        // Follow legacy behavior: cleanup related records
        Kelas::where('id_prodi', $prodi->id)->delete();
        Kaprodi::where('prodis_id', $prodi->id)->delete();
        
        $prodi->delete();

        return redirect()->back()->with('success', 'Program studi berhasil dihapus.');
    }
}
