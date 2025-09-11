<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Wadir;
use App\Models\Direktur;
use App\Models\Kaprodi;
use App\Models\Jadwal;
use App\Exports\DosenExport;
use App\Imports\DosenImport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;


class DosenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kelasAll = Jadwal::all();
        $dosens = Dosen::latest()->get();
        return view('pages.data-master.data-dosen', compact('dosens', 'kelasAll'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validateData = $request->validate([
            'nama' => 'required',
            'nidn' => [
                'nullable',
                'numeric',
                'digits:12',
                Rule::unique('dosens')->whereNull('deleted_at'),
            ],
            'jenis_kelamin' => 'required',
            'pembimbing_akademik' => 'required',
            'no_telephone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('dosens')->whereNull('deleted_at'),
            ],
            'agama' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('dosens')->whereNull('deleted_at'),
            ],
            'password' => 'required'
        ], [
            'nama.required' => 'Nama Dosen harus diisi',
            'nidn.numeric' => 'NUPTK harus berupa angka',
            'nidn.digits' => 'NUPTK harus terdiri dari 12 digit',
            'nidn.unique' => 'NUPTK sudah terdaftar',
            'jenis_kelamin.required' => 'Jenis kelamin harus dipilih',
            'pembimbing_akademik.required' => 'Status pembimbing akademik harus dipilih',
            'no_telephone.required' => 'Nomor WhatsApp harus diisi',
            'no_telephone.unique' => 'Nomor WhatsApp sudah terdaftar',
            'agama.required' => 'Agama harus dipilih',
            'tanggal_lahir.required' => 'Tanggal lahir harus diisi',
            'tempat_lahir.required' => 'Tempat lahir harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password harus diisi'
        ]);

        Dosen::create([
            'nama' => $validateData['nama'],
            'nidn' => $validateData['nidn'],
            'jenis_kelamin' => $validateData['jenis_kelamin'],
            'no_telephone' => $validateData['no_telephone'],
            'agama' => $validateData['agama'],
            'tanggal_lahir' => $validateData['tanggal_lahir'],
            'tempat_lahir' => $validateData['tempat_lahir'],
            'email' => $validateData['email'],
            'status' => 1,
            'password' => Hash::make($validateData['password']),
            'pembimbing_akademik' => $validateData['pembimbing_akademik'],
            'is_first_login' => true
        ]);

        return response()->json(['success' => 'Data dosen berhasil ditambahkan!'], 200);
    }


    public function update(Request $request, $id)
    {
        $dosen = Dosen::findOrFail($id);
        $kaprodi = Kaprodi::where('dosens_id', $id)->get();
        $direktur = Direktur::where('dosens_id', $id)->get();
        $wadir = Wadir::where('dosens_id', $id)->get();

        $request->validate([
            'nama' => 'required|string|max:255',
            'nidn' => [
                'nullable',
                'numeric',
                'digits:12',
                Rule::unique('dosens')->ignore($dosen->id)->whereNull('deleted_at'),
            ],
            'jenis_kelamin' => 'required|string',
            'pembimbing_akademik' => 'required',
            'no_telephone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('dosens')->ignore($dosen->id)->whereNull('deleted_at'),
            ],
            'agama' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:255',
            'status' => 'required|in:0,1',
            'email' => [
                'required',
                'email',
                Rule::unique('dosens')->ignore($dosen->id)->whereNull('deleted_at'),
            ],
        ]);

        $updateData = $request->except('password');

        if ($request->has('nidn') && $request->nidn === null) {
            $updateData['nidn'] = null;
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
$updateData['is_first_login'] = true;
        }

        $dosen->update($updateData);

        foreach ($kaprodi as $item) {
            $item->update([
                'no_telephone' => $request->no_telephone,
                'email' => $request->email,
            ]);
        }

        foreach ($direktur as $item) {
            $item->update([
                'no_telephone' => $request->no_telephone,
                'email' => $request->email,
            ]);
        }

        foreach ($wadir as $item) {
            $item->update([
                'no_telephone' => $request->no_telephone,
                'email' => $request->email,
            ]);
        }

        return response()->json(['success' => 'Data dosen berhasil diperbarui']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $dosen = Dosen::findOrFail($id);
        $dosen->delete();
        Wadir::where('dosens_id', $dosen->id)->delete();
        Direktur::where('dosens_id', $dosen->id)->delete();
        Kaprodi::where('dosens_id', $dosen->id)->delete();
        return response()->json(['success' => 'Dosen berhasil dihapus.']);
    }

    public function import(Request $request) {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], ['file.mimes' => 'Format file tidak sesuai']);

        DB::beginTransaction();

        Excel::import(new DosenImport(), $request->file('file'));

        DB::commit();

        return response()->json([
            'success' => 'Data Dosen berhasil diimpor'
        ]);
    }

    public function export(){

        return Excel::download(new DosenExport, 'dosen.xlsx');
    }

	public function downloadFormat() {
        $filePath = public_path('format/import_dosen.xlsx');
        return response()->download($filePath, 'Format_Import_dosen.xlsx');
    	}
}
