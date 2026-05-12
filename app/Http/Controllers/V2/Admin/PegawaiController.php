<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use App\Exports\PegawaiExport;
use App\Imports\PegawaiImport;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nuptk' => [
                'nullable',
                'numeric',
                'digits:12',
                Rule::unique('pegawais')->whereNull('deleted_at'),
            ],
            'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan',
            'no_telephone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('pegawais')->whereNull('deleted_at'),
            ],
            'agama' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('pegawais')->whereNull('deleted_at'),
            ],
            'password' => 'required|min:6'
        ], [
            'nuptk.digits' => 'NUPTK harus terdiri dari 12 digit',
            'nuptk.unique' => 'NUPTK sudah terdaftar',
            'no_telephone.unique' => 'Nomor WhatsApp sudah terdaftar',
            'email.unique' => 'Email sudah terdaftar',
        ]);

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
            'is_first_login' => true
        ]);

        return redirect()->back()->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nuptk' => [
                'nullable',
                'numeric',
                'digits:12',
                Rule::unique('pegawais')->ignore($pegawai->id)->whereNull('deleted_at'),
            ],
            'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan',
            'no_telephone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('pegawais')->ignore($pegawai->id)->whereNull('deleted_at'),
            ],
            'agama' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:255',
            'status' => 'required|in:0,1',
            'email' => [
                'required',
                'email',
                Rule::unique('pegawais')->ignore($pegawai->id)->whereNull('deleted_at'),
            ],
            'password' => 'nullable|min:6'
        ], [
            'nuptk.digits' => 'NUPTK harus terdiri dari 12 digit',
            'nuptk.unique' => 'NUPTK sudah terdaftar',
            'no_telephone.unique' => 'Nomor WhatsApp sudah terdaftar',
            'email.unique' => 'Email sudah terdaftar',
        ]);

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
        if (!file_exists($filePath)) {
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
            Excel::import(new PegawaiImport(), $request->file('file'));
            DB::commit();
            return redirect()->back()->with('success', 'Data pegawai berhasil diimpor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
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
