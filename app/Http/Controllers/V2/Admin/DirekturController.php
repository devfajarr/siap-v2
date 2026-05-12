<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Direktur;
use App\Models\Wadir;
use App\Models\Kaprodi;
use App\Http\Requests\V2\Admin\Direktur\StoreDirekturRequest;
use App\Http\Requests\V2\Admin\Direktur\UpdateDirekturRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DirekturController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $direkturs = Direktur::with('dosen')
            ->when($search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $dosens = Dosen::orderBy('nama', 'asc')->get();
        
        // Get IDs of lecturers who already have a structural position
        $existingKaprodiDosenIds = Kaprodi::pluck('dosens_id');
        $existingDirekturDosenIds = Direktur::pluck('dosens_id');
        $existingWadirDosenIds = Wadir::pluck('dosens_id');
        
        $hasAnyPositionIds = $existingKaprodiDosenIds
            ->concat($existingDirekturDosenIds)
            ->concat($existingWadirDosenIds)
            ->unique()
            ->values();

        return Inertia::render('Admin/DataDirektur/Index', [
            'direkturs' => $direkturs,
            'dosens' => $dosens,
            'hasAnyPositionIds' => $hasAnyPositionIds,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreDirekturRequest $request)
    {

        $dosen = Dosen::findOrFail($request->dosens_id);
        $password = null;

        if ($request->password_mode === 'dosen') {
            $password = $dosen->password;
        } elseif ($request->password_mode === 'existing') {
            // Check in Kaprodi, then Wadir
            $existing = Kaprodi::where('dosens_id', $request->dosens_id)->first() 
                      ?? Wadir::where('dosens_id', $request->dosens_id)->first();
            
            if ($existing) {
                $password = $existing->password;
            } else {
                return redirect()->back()->with('error', 'Data jabatan sebelumnya tidak ditemukan.');
            }
        } else {
            $password = Hash::make($request->password);
        }

        Direktur::create([
            'nama' => $dosen->nama,
            'dosens_id' => $request->dosens_id,
            'no_telephone' => $dosen->no_telephone,
            'email' => $dosen->email,
            'status' => 1,
            'password' => $password,
            'is_first_login' => ($request->password_mode === 'custom')
        ]);

        return redirect()->back()->with('success', 'Direktur berhasil ditambahkan.');
    }

    public function update(UpdateDirekturRequest $request, $id)
    {
        $direktur = Direktur::findOrFail($id);

        $updateData = ['status' => $request->status];

        if ($request->filled('password')) {
            $password = Hash::make($request->password);
            $updateData['password'] = $password;
            $updateData['is_first_login'] = true;

            // Global Sync Password
            Kaprodi::where('dosens_id', $direktur->dosens_id)->update(['password' => $password]);
            Direktur::where('dosens_id', $direktur->dosens_id)->update(['password' => $password]);
            Wadir::where('dosens_id', $direktur->dosens_id)->update(['password' => $password]);
        }

        $direktur->update($updateData);

        return redirect()->back()->with('success', 'Data Direktur berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $direktur = Direktur::findOrFail($id);
        $direktur->delete();

        return redirect()->back()->with('success', 'Direktur berhasil dihapus.');
    }
}
