<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\Kaprodi;
use App\Http\Requests\V2\Admin\Kaprodi\StoreKaprodiRequest;
use App\Http\Requests\V2\Admin\Kaprodi\UpdateKaprodiRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class KaprodiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $kaprodis = Kaprodi::with(['prodis', 'dosen'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('prodis', function ($pq) use ($search) {
                            $pq->where('nama_prodi', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $dosens = Dosen::orderBy('nama', 'asc')->get();
        $prodis = Prodi::orderBy('nama_prodi', 'asc')->get();

        // Get IDs of lecturers who are already Kaprodis for password reuse logic
        $existingKaprodiDosenIds = Kaprodi::pluck('dosens_id')->unique()->values();

        return Inertia::render('Admin/DataKaprodi/Index', [
            'kaprodis' => $kaprodis,
            'dosens' => $dosens,
            'prodis' => $prodis,
            'existingKaprodiDosenIds' => $existingKaprodiDosenIds,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreKaprodiRequest $request)
    {

        $dosen = Dosen::findOrFail($request->dosens_id);
        $password = null;

        if ($request->password_mode === 'dosen') {
            $password = $dosen->password;
        } elseif ($request->password_mode === 'existing') {
            $existing = Kaprodi::where('dosens_id', $request->dosens_id)->first();
            if ($existing) {
                $password = $existing->password;
            } else {
                return redirect()->back()->with('error', 'Data Kaprodi sebelumnya tidak ditemukan.');
            }
        } else {
            $password = Hash::make($request->password);
        }

        $kaprodi = Kaprodi::create([
            'nama' => $dosen->nama,
            'dosens_id' => $request->dosens_id,
            'no_telephone' => $dosen->no_telephone,
            'email' => $dosen->email,
            'status' => 1,
            'password' => $password,
            'is_first_login' => ($request->password_mode === 'custom')
        ]);

        $kaprodi->prodis()->sync($request->prodis_id);

        return redirect()->back()->with('success', 'Kaprodi berhasil ditambahkan.');
    }

    public function update(UpdateKaprodiRequest $request, $id)
    {
        $kaprodi = Kaprodi::findOrFail($id);

        $dosen = Dosen::findOrFail($request->dosens_id);
        
        $updateData = [
            'nama' => $dosen->nama,
            'dosens_id' => $request->dosens_id,
            'email' => $dosen->email,
            'no_telephone' => $dosen->no_telephone,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $password = Hash::make($request->password);
            $updateData['password'] = $password;
            $updateData['is_first_login'] = true;

            // Sync password to all Kaprodi records of this dosen
            Kaprodi::where('dosens_id', $request->dosens_id)->update(['password' => $password]);
        }

        $kaprodi->update($updateData);
        $kaprodi->prodis()->sync($request->prodis_id);

        return redirect()->back()->with('success', 'Data Kaprodi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kaprodi = Kaprodi::findOrFail($id);
        $kaprodi->delete();

        return redirect()->back()->with('success', 'Kaprodi berhasil dihapus.');
    }
}
