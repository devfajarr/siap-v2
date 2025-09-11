<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\Jadwal;
use App\Models\Kaprodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Unique;

class KaprodiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kelasAll = Jadwal::all();
        $kaprodis = Kaprodi::with('prodi')->latest()->get();
        $dosens = Dosen::all();
        $prodis = Prodi::all();
        return view('pages.data-master.data-kaprodi', compact('dosens', 'prodis', 'kaprodis', 'kelasAll'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dosens_id' => 'required|unique:kaprodi,dosens_id',
            'prodis_id' => 'required|unique:kaprodi,prodis_id',
            'password' => 'required'
        ], [
            'dosens_id.required' => 'Kaprodi harus diisi',
            'dosens_id.unique' => 'Dosen ini sudah terdaftar sebagai kaprodi',
            'prodis_id.required' => 'Prodi harus dipilih',
            'prodis_id.unique' => 'Prodi ini sudah memiliki kaprodi',
            'password.required' => 'Password harus diisi',
        ]);

        $dosen = Dosen::where('id', $request->dosens_id)->first();

        Kaprodi::create([
            'nama' => $dosen->nama,
            'dosens_id' => $request->dosens_id,
            'prodis_id' => $request->prodis_id,
            'no_telephone' => $dosen->no_telephone,
            'email' => $dosen->email,
            'status' => 1,
            'password' => Hash::make($request->password),
            'is_first_login' => true
        ]);

        return response()->json([
            'success' => 'Kaprodi berhasil ditambahkan!',
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $kaprodi = Kaprodi::findOrFail($id);

        $request->validate([
            'dosens_id' => 'required|exists:dosens,id',
            'prodis_id' => 'required|unique:kaprodi,prodis_id,' . $kaprodi->id,
            'status' => 'required|in:0,1',
        ], [
            'dosens_id.required' => 'Dosen wajib dipilih',
            'dosens_id.exists' => 'Dosen tidak ditemukan',
            'prodis_id.required' => 'Program studi harus diisi',
            'prodis_id.unique' => 'Program studi ini sudah memiliki kaprodi',
            'status.required' => 'Status harus diisi',
            'status.in' => 'Status harus 0 atau 1',
        ]);

        $dosen = Dosen::where('id', $request->dosens_id)->first();
        $kaprodi->nama = $dosen->nama;
        $kaprodi->dosens_id = $request->dosens_id;
        $kaprodi->email = $dosen->email;
        $kaprodi->no_telephone = $dosen->no_telephone;
        $kaprodi->status = $request->status;
        $kaprodi->prodis_id = $request->prodis_id;

        if ($request->filled('password')) {
            $kaprodi->password = Hash::make($request->password);
		$kaprodi->is_first_login = true;
        }


        $kaprodi->save();

        return response()->json(['success' => 'Kaprodi berhasil diupdate!']);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kaprodi = Kaprodi::findOrFail($id);
        $kaprodi->delete();
        return response()->json(['success' => 'Kaprodi berhasil dihapus']);
    }
}
