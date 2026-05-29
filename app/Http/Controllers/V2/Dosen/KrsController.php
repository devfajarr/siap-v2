<?php

namespace App\Http\Controllers\V2\Dosen;

use App\Http\Controllers\Controller;
use App\Jobs\SendKrsNotificationJob;
use App\Models\Krs;
use App\Models\Matkul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class KrsController extends Controller
{
    public function index(Request $request)
    {
        $dosenId = Auth::guard('dosen')->id();
        $tab = $request->query('tab', 'diajukan'); // Default ke diajukan

        $query = Krs::with(['mahasiswa', 'kelas.prodi'])
            ->whereHas('mahasiswa', function ($q) use ($dosenId) {
                $q->where('dosen_pembimbing_id', $dosenId);
            });

        if ($tab === 'diajukan') {
            $query->where('status_krs', 0)
                ->where('setuju_pa', 0)
                ->where('setuju_mahasiswa', 1);
        } else {
            // Disetujui
            $query->where('status_krs', 1)
                ->where('setuju_pa', 1)
                ->where('setuju_mahasiswa', 1);
        }

        $krss = $query->latest()->get()->map(function ($krs) {
            return [
                'id' => $krs->id,
                'mahasiswa_id' => $krs->mahasiswa_id,
                'nim' => $krs->mahasiswa->nim,
                'nama_mahasiswa' => $krs->mahasiswa->nama_lengkap,
                'kelas' => $krs->kelas->nama_kelas ?? '-',
                'prodi' => $krs->kelas->prodi->nama_prodi ?? '-',
                'status_krs' => $krs->status_krs,
                'setuju_pa' => $krs->setuju_pa,
                'setuju_mahasiswa' => $krs->setuju_mahasiswa,
                'created_at' => $krs->created_at->format('Y-m-d H:i'),
                'semester_id' => $krs->semester_id,
                'prodi_id' => $krs->prodi_id,
            ];
        });

        return Inertia::render('Dosen/KRS/Index', [
            'krss' => $krss,
            'currentTab' => $tab,
        ]);
    }

    public function detail(Request $request, $id)
    {
        $dosenId = Auth::guard('dosen')->id();

        $krs = Krs::with(['mahasiswa', 'kelas', 'prodi', 'semester'])->find($id);

        if (! $krs || $krs->mahasiswa->dosen_pembimbing_id !== $dosenId) {
            abort(403, 'Akses ditolak.');
        }

        $matkulKrs = Matkul::where('prodi_id', $krs->prodi_id)
            ->where('semester_id', $krs->semester_id)
            ->get()->map(function ($mk) {
                return [
                    'id' => $mk->id,
                    'kode_matkul' => $mk->kode,
                    'nama_matkul' => $mk->nama_matkul,
                    'sks' => $mk->teori + $mk->praktek,
                    'semester' => $mk->semester_id,
                ];
            });

        return response()->json([
            'krs' => [
                'id' => $krs->id,
                'nim' => $krs->mahasiswa->nim,
                'nama' => $krs->mahasiswa->nama_lengkap,
                'kelas' => $krs->kelas->nama_kelas,
                'prodi' => $krs->prodi->nama_prodi,
                'semester' => $krs->semester->nama_semester ?? '-',
            ],
            'matkuls' => $matkulKrs,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $dosenId = Auth::guard('dosen')->id();

        $krs = Krs::with('mahasiswa')->find($id);

        if (! $krs || $krs->mahasiswa->dosen_pembimbing_id !== $dosenId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk memverifikasi KRS ini.');
        }

        if ($krs->setuju_pa == 1) {
            return redirect()->back()->with('error', 'KRS sudah diverifikasi sebelumnya.');
        }

        $krs->setuju_pa = 1;

        if ($krs->setuju_mahasiswa == 1) {
            $krs->status_krs = 1;

            $mahasiswa = $krs->mahasiswa;
            $mahasiswa->status_krs = true;
            $mahasiswa->save();
        }

        $krs->save();

        if ($krs->status_krs == 1) {
            // Dispatch job ke antrean untuk menunda HTTP request (Tunda untuk pengujian)
            SendKrsNotificationJob::dispatch($krs->id);
        }

        return redirect()->back()->with('success', 'KRS berhasil diverifikasi.');
    }
}
