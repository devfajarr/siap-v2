<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Admin\Jabatan\StoreJabatanRequest;
use App\Http\Requests\V2\Admin\Jabatan\UpdateJabatanRequest;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class JabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $jabatans = Jabatan::with(['dosen', 'pegawai'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_jabatan', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('dosen', function ($dq) use ($search) {
                            $dq->where('nama', 'like', "%{$search}%");
                        })
                        ->orWhereHas('pegawai', function ($pq) use ($search) {
                            $pq->where('nama', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $dosens = Dosen::orderBy('nama', 'asc')->get();
        $pegawais = Pegawai::orderBy('nama', 'asc')->get();

        // Get IDs of lecturers/staff who already have a structural jabatan to support password reuse logic
        $existingJabatanDosenIds = Jabatan::whereNotNull('dosens_id')->pluck('dosens_id')->unique()->values();
        $existingJabatanPegawaiIds = Jabatan::whereNotNull('pegawais_id')->pluck('pegawais_id')->unique()->values();

        return Inertia::render('Admin/DataJabatan/Index', [
            'jabatans' => $jabatans,
            'dosens' => $dosens,
            'pegawais' => $pegawais,
            'existingJabatanDosenIds' => $existingJabatanDosenIds,
            'existingJabatanPegawaiIds' => $existingJabatanPegawaiIds,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJabatanRequest $request)
    {
        $email = null;
        $password = null;

        if ($request->user_type === 'dosen') {
            $user = Dosen::findOrFail($request->dosens_id);
            $email = $user->email;

            if ($request->password_mode === 'base') {
                $password = $user->password;
            } elseif ($request->password_mode === 'existing') {
                $existing = Jabatan::where('dosens_id', $request->dosens_id)->first();
                if ($existing) {
                    $password = $existing->password;
                } else {
                    return redirect()->back()->with('error', 'Data jabatan sebelumnya tidak ditemukan.');
                }
            } else {
                $password = Hash::make($request->password);
            }

            Jabatan::create([
                'dosens_id' => $request->dosens_id,
                'pegawais_id' => null,
                'nama_jabatan' => $request->nama_jabatan,
                'email' => $email,
                'password' => $password,
                'status' => 1,
                'is_first_login' => ($request->password_mode === 'custom'),
            ]);

        } else {
            $user = Pegawai::findOrFail($request->pegawais_id);
            $email = $user->email;

            if ($request->password_mode === 'base') {
                $password = $user->password;
            } elseif ($request->password_mode === 'existing') {
                $existing = Jabatan::where('pegawais_id', $request->pegawais_id)->first();
                if ($existing) {
                    $password = $existing->password;
                } else {
                    return redirect()->back()->with('error', 'Data jabatan sebelumnya tidak ditemukan.');
                }
            } else {
                $password = Hash::make($request->password);
            }

            Jabatan::create([
                'dosens_id' => null,
                'pegawais_id' => $request->pegawais_id,
                'nama_jabatan' => $request->nama_jabatan,
                'email' => $email,
                'password' => $password,
                'status' => 1,
                'is_first_login' => ($request->password_mode === 'custom'),
            ]);
        }

        return redirect()->back()->with('success', 'Jabatan struktural berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJabatanRequest $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);

        $updateData = [
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $password = Hash::make($request->password);
            $updateData['password'] = $password;
            $updateData['is_first_login'] = true;

            // Sync password to all other structural roles of the same person
            if ($jabatan->dosens_id) {
                Jabatan::where('dosens_id', $jabatan->dosens_id)->update(['password' => $password]);
            } elseif ($jabatan->pegawais_id) {
                Jabatan::where('pegawais_id', $jabatan->pegawais_id)->update(['password' => $password]);
            }
        }

        $jabatan->update($updateData);

        return redirect()->back()->with('success', 'Data jabatan struktural berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $jabatan = Jabatan::findOrFail($id);
        $jabatan->delete();

        return redirect()->back()->with('success', 'Jabatan struktural berhasil dihapus.');
    }
}
