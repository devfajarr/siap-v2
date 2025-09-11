<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Wadir;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WadirController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wadirs = Wadir::latest()->get();
        $dosens = Dosen::all();
        $kelasAll = Jadwal::all();
        return view('pages.data-master.data-wadir', compact('wadirs', 'dosens', 'kelasAll'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dosens_id' => 'required|unique:wadirs',
            'password' => 'required',
            'no' => 'required'
        ], [
            'dosens_id.required' => 'Wakil Direktur harus diisi',
            'dosens_id.unique' => 'Dosen sudah menjadi wakil Direktur',
            'password.required' => 'Password harus diisi',
            'no.required' => 'Posisi harus diisi'
        ]);

        if (in_array($request->no, [1, 2]) && Wadir::where('no', $request->no)->exists()) {
            return response()->json([
                'errors' => [
                    'no' => ['Wadir dengan nomor ' . $request->no . ' sudah ada, tidak bisa menambah lagi']
                ]
            ], 400);
        }

        $dosen = Dosen::where('id', $request->dosens_id)->first();
        Wadir::create([
            'dosens_id' => $request->dosens_id,
            'nama' => $dosen->nama,
            'email' => $dosen->email,
            'no_telephone' => $dosen->no_telephone,
            'status' => 1,
            'no' => $request->no,
            'password' => Hash::make($request->password),
            'is_first_login' => true

        ]);

        return response()->json(['success' => 'Wakil direktur berhasil ditambahkan']);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $wadir = Wadir::findOrFail($id);

        $request->validate([
            'dosens_id' => 'required|exists:dosens,id',
            'status' => 'required|boolean',
            'no' => 'required',
        ], [
            'dosens_id.required' => 'Dosen wajib dipilih',
            'dosens_id.exists' => 'Dosen tidak ditemukan',
            'status.required' => 'Status wajib dipilih',
            'status.boolean' => 'Status harus berupa nilai boolean',
            'no.required' => 'Posisi wajib diisi',
        ]);


        $dosen = Dosen::where('id', $request->dosens_id)->first();

        $wadir->nama = $dosen->nama;
        $wadir->dosens_id = $request->dosens_id;
        $wadir->email = $dosen->email;
        $wadir->no_telephone = $dosen->no_telephone;
        $wadir->status = $request->status;
        $wadir->no = $request->no;


        if ($request->filled('password')) {
            $wadir->password = Hash::make($request->password);
		$wadir->is_first_login = true;
        }


        $wadir->save();

        return response()->json(['success' => 'Data Wadir berhasil diperbarui']);
    }





    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $wadir = Wadir::findOrFail($id);
        $wadir->delete();
        return response()->json(['success' => 'Wadir berhasil dihapus.']);
    }
}
