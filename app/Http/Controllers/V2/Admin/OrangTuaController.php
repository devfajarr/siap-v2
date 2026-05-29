<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\OrangTua;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OrangTuaController extends Controller
{
    /**
     * Menambahkan atau menautkan akun orang tua ke mahasiswa.
     */
    public function store(Request $request, $mahasiswaId): RedirectResponse
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|min:8',
            'no_telephone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'relationship_type' => 'required|string|in:Ayah,Ibu,Wali',
        ]);

        $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);

        // Check if OrangTua username already exists
        $parent = OrangTua::where('username', $request->username)->first();

        if ($parent) {
            // Check if already linked
            $isLinked = $parent->mahasiswas()->where('mahasiswas.id', $mahasiswaId)->exists();
            if ($isLinked) {
                return redirect()->back()->with('error', 'Orang tua ini sudah terhubung dengan mahasiswa terkait.');
            }
        } else {
            // Required password if new parent account
            $request->validate([
                'password' => 'required|string|min:8',
            ]);

            $parent = OrangTua::create([
                'nama' => $request->nama,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'no_telephone' => $request->no_telephone,
                'alamat' => $request->alamat,
            ]);
        }

        // Link parent to student in pivot table
        $parent->mahasiswas()->attach($mahasiswaId, [
            'relationship_type' => $request->relationship_type,
        ]);

        return redirect()->back()->with('success', 'Akun orang tua berhasil ditambahkan dan ditautkan ke mahasiswa.');
    }

    /**
     * Memutuskan tautan orang tua dari mahasiswa, dan menghapus akun jika tidak mengelola anak lain.
     */
    public function destroy($mahasiswaId, $parentId): RedirectResponse
    {
        $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);
        $parent = OrangTua::findOrFail($parentId);

        // Detach pivot relationship
        $parent->mahasiswas()->detach($mahasiswaId);

        // Clean up parent account if they have no other children linked
        if ($parent->mahasiswas()->count() === 0) {
            $parent->delete();
        }

        return redirect()->back()->with('success', 'Tautan orang tua berhasil dihapus.');
    }
}
