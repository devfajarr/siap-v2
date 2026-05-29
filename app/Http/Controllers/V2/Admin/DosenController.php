<?php

namespace App\Http\Controllers\V2\Admin;

use App\Exports\DosenExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Admin\Dosen\StoreDosenRequest;
use App\Http\Requests\V2\Admin\Dosen\UpdateDosenRequest;
use App\Imports\DosenImport;
use App\Models\Direktur;
use App\Models\Dosen;
use App\Models\Kaprodi;
use App\Models\Wadir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $dosens = Dosen::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nidn', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_telephone', 'like', "%{$search}%");
            });
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/DataDosen/Index', [
            'dosens' => $dosens,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreDosenRequest $request)
    {
        $validated = $request->validated();

        Dosen::create([
            'nama' => $validated['nama'],
            'nidn' => $validated['nidn'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'no_telephone' => $validated['no_telephone'],
            'agama' => $validated['agama'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'email' => $validated['email'],
            'status' => 1,
            'password' => Hash::make($validated['password']),
            'pembimbing_akademik' => $validated['pembimbing_akademik'],
            'is_first_login' => true,
        ]);

        return redirect()->back()->with('success', 'Data dosen berhasil ditambahkan.');
    }

    public function update(UpdateDosenRequest $request, $id)
    {
        $dosen = Dosen::findOrFail($id);

        $validated = $request->validated();

        $updateData = collect($validated)->except('password')->toArray();

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
            $updateData['is_first_login'] = true;
        }

        $dosen->update($updateData);

        // Sync related models (Jabatan)
        $syncData = [
            'no_telephone' => $request->no_telephone,
            'email' => $request->email,
        ];

        Kaprodi::where('dosens_id', $id)->update($syncData);
        Direktur::where('dosens_id', $id)->update($syncData);
        Wadir::where('dosens_id', $id)->update($syncData);

        return redirect()->back()->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $dosen = Dosen::findOrFail($id);

        // Cascading delete for related jabatan
        Wadir::where('dosens_id', $dosen->id)->delete();
        Direktur::where('dosens_id', $dosen->id)->delete();
        Kaprodi::where('dosens_id', $dosen->id)->delete();

        $dosen->delete();

        return redirect()->back()->with('success', 'Data dosen berhasil dihapus.');
    }

    /**
     * Download import template.
     */
    public function downloadFormat()
    {
        $filePath = public_path('format/import_dosen.xlsx');
        if (! file_exists($filePath)) {
            return redirect()->back()->with('error', 'Template import tidak ditemukan.');
        }

        return response()->download($filePath, 'Format_Import_Dosen.xlsx');
    }

    /**
     * Import data from Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], ['file.mimes' => 'Format file tidak sesuai']);

        DB::beginTransaction();
        try {
            Excel::import(new DosenImport, $request->file('file'));
            DB::commit();

            return redirect()->back()->with('success', 'Data dosen berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal mengimpor data: '.$e->getMessage());
        }
    }

    /**
     * Export data to Excel.
     */
    public function export()
    {
        return Excel::download(new DosenExport, 'dosen.xlsx');
    }
}
