<?php

namespace App\Http\Controllers;

use App\Exports\PegawaiExport;
use App\Imports\PegawaiImport;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pegawais = Pegawai::latest()->get();

        return view('pages.data-master.data-pegawai', compact('pegawais'));
    }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'nama' => 'required',
            'nuptk' => [
                'nullable',
                'numeric',
                'digits:12',
                Rule::unique('pegawais')->whereNull('deleted_at'),
            ],
            'jenis_kelamin' => 'required',
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
            'password' => 'required',
        ], [
            'nama.required' => 'Nama Dosen harus diisi',
            'nuptk.numeric' => 'NUPTK harus berupa angka',
            'nuptk.digits' => 'NUPTK harus terdiri dari 12 digit',
            'nuptk.unique' => 'NUPTK sudah terdaftar',
            'jenis_kelamin.required' => 'Jenis kelamin harus dipilih',
            'no_telephone.required' => 'Nomor WhatsApp harus diisi',
            'no_telephone.unique' => 'Nomor WhatsApp sudah terdaftar',
            'agama.required' => 'Agama harus dipilih',
            'tanggal_lahir.required' => 'Tanggal lahir harus diisi',
            'tempat_lahir.required' => 'Tempat lahir harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password harus diisi',
        ]);

        Pegawai::create([
            'nama' => $validateData['nama'],
            'nuptk' => $validateData['nuptk'],
            'jenis_kelamin' => $validateData['jenis_kelamin'],
            'no_telephone' => $validateData['no_telephone'],
            'agama' => $validateData['agama'],
            'tanggal_lahir' => $validateData['tanggal_lahir'],
            'tempat_lahir' => $validateData['tempat_lahir'],
            'email' => $validateData['email'],
            'status' => 1,
            'password' => Hash::make($validateData['password']),
            'is_first_login' => true,
        ]);

        return response()->json(['success' => 'Data dosen berhasil ditambahkan!'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pegawai $pegawai)
    {
        //
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(Pegawai $pegawai)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'nuptk' => [
                'nullable',
                'numeric',
                'digits:12',
                Rule::unique('pegawais')->ignore($pegawai->id)->whereNull('deleted_at'),
            ],
            'jenis_kelamin' => 'required|string',
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
        ]);

        $updateData = $request->except('password');

        if ($request->has('nuptk') && $request->nuptk === null) {
            $updateData['nuptk'] = null;
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $pegawai->update($updateData);

        return response()->json(['success' => 'Data dosen berhasil diperbarui']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->delete();

        return response()->json(['success' => 'Dosen berhasil dihapus.']);
    }

    public function downloadFormat()
    {
        $filePath = public_path('format/import_pegawai.xlsx');

        return response()->download($filePath, 'Format_Import_pegawai.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], ['file.mimes' => 'Format file tidak sesuai']);

        DB::beginTransaction();

        Excel::import(new PegawaiImport, $request->file('file'));

        DB::commit();

        return response()->json([
            'success' => 'Data Dosen berhasil diimpor',
        ]);
    }

    public function export()
    {

        return Excel::download(new PegawaiExport, 'pegawai.xlsx');
    }
}
