<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Direktur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DirekturController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kelasAll = Jadwal::all();
        $direkturs = Direktur::latest()->get();
        $dosens = Dosen::all();
        return view('pages.data-master.data-direktur', compact('direkturs', 'dosens', 'kelasAll'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dosens_id' => 'required|unique:direkturs',
            'password' => 'required',
        ], [
            'dosens_id.required' => 'Direktur harus diisi',
            'dosens_id.unique' => 'Dosen sudah menjadi direktur',
            'password.required' => 'Password harus diisi',
        ]);

        $dosen = Dosen::where('id', $request->dosens_id)->first();
        Direktur::create([
            'nama' => $dosen->nama,
            'email' => $dosen->email,
            'status' => 1,
            'no_telephone' => $dosen->no_telephone,
            'dosens_id' => $request->dosens_id,
            'password' => Hash::make($request->password),
            'is_first_login' => true
        ]);

        return response()->json(['success' => 'Direktur berhasil ditambahkan']);
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function update(Request $request, $id)
    {
        $direktur = Direktur::findOrFail($id);

        $request->validate([
            'dosens_id' => 'required|exists:dosens,id|unique:direktur,dosens_id,' . $direktur->id,
            'status' => 'required|boolean',
        ], [
            'dosens_id.required' => 'Dosen wajib dipilih',
            'dosens_id.exists' => 'Dosen tidak ditemukan',
            'dosens_id.unique' => 'Dosen ini sudah terdaftar sebagai Direktur',
            'status.required' => 'Status wajib dipilih',
            'status.boolean' => 'Status harus berupa nilai boolean',
        ]);

        $dosen = Dosen::where('id', $request->dosens_id)->first();

        $direktur->nama = $dosen->nama;
        $direktur->dosens_id = $request->dosens_id;
        $direktur->email = $dosen->email;
        $direktur->no_telephone = $dosen->no_telephone;
        $direktur->status = $request->status;

        if ($request->filled('password')) {
            $direktur->password = Hash::make($request->password);
		$direktur->is_first_login = true;
        }

        $direktur->save();

        return response()->json(['success' => 'Data direktur berhasil diperbarui']);
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $direktur = Direktur::findOrFail($id);

        $direktur->delete();
        return response()->json(['success' => 'Data direktur berhasil dihapus.']);
    }
}
