<?php

namespace App\Http\Controllers\V2\Admin;

use App\Exports\PegawaiExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Admin\Pegawai\StorePegawaiRequest;
use App\Http\Requests\V2\Admin\Pegawai\UpdatePegawaiRequest;
use App\Imports\PegawaiImport;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $pegawais = Pegawai::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nuptk', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_telephone', 'like', "%{$search}%");
            });
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/DataPegawai/Index', [
            'pegawais' => $pegawais,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StorePegawaiRequest $request)
    {
        $validated = $request->validated();

        Pegawai::create([
            'nama' => $validated['nama'],
            'nuptk' => $validated['nuptk'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'no_telephone' => $validated['no_telephone'],
            'agama' => $validated['agama'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'email' => $validated['email'],
            'status' => 1,
            'password' => Hash::make($validated['password']),
            'is_first_login' => true,
        ]);

        return redirect()->back()->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function update(UpdatePegawaiRequest $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $validated = $request->validated();

        $updateData = collect($validated)->except('password')->toArray();

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $pegawai->update($updateData);

        return redirect()->back()->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->delete();

        return redirect()->back()->with('success', 'Data pegawai berhasil dihapus.');
    }

    /**
     * Download import template.
     */
    public function downloadFormat()
    {
        $filePath = public_path('format/import_pegawai.xlsx');
        if (! file_exists($filePath)) {
            return redirect()->back()->with('error', 'Template import tidak ditemukan.');
        }

        return response()->download($filePath, 'Format_Import_Pegawai.xlsx');
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
            Excel::import(new PegawaiImport, $request->file('file'));
            DB::commit();

            return redirect()->back()->with('success', 'Data pegawai berhasil diimpor.');
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
        return Excel::download(new PegawaiExport, 'pegawai.xlsx');
    }
}
