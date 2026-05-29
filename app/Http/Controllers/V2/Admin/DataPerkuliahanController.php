<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class DataPerkuliahanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mendapatkan daftar dosen yang aktif dan memiliki jadwal
        $dosens = Dosen::where('status', 1)
            ->whereHas('jadwal')
            ->withCount('jadwal as total_matkul')
            ->get()
            ->map(function ($dosen) {
                return [
                    'id' => $dosen->id,
                    'nama' => $dosen->nama,
                    'total_matkul' => $dosen->total_matkul,
                ];
            });

        return Inertia::render('Admin/DataPerkuliahan/Index', [
            'dosens' => $dosens,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $dosen = Dosen::findOrFail($id);

        $jadwals = Jadwal::with([
            'matkul',
            'matkul.prodi',
            'matkul.semester',
            'kelas.prodi',
        ])
            ->where('dosens_id', $id)
            ->withMax('absen as pertemuan_max', 'pertemuan')
            ->withMax('resume as berita_max', 'pertemuan')
            ->withMax('kontrak as kontrak_max', 'pertemuan')
            ->orderBy(function ($query) {
                $query->select('nama_matkul')
                    ->from('matkuls')
                    ->whereColumn('matkuls.id', 'jadwals.matkuls_id');
            }, 'asc')
            ->get();

        $formattedJadwals = $jadwals->map(function ($jadwal) {

            // Evaluasi message menggunakan DB::raw subquery atau exists query untuk performa
            // Karena relasi message ada di Jadwal
            $userId = Session::get('user.id');
            $role = Session::get('user.role');

            // Map role to Model namespace (Simplified inline mapping as used in legacy code)
            $roleToModelMap = [
                'admin' => 'App\Models\Admin',
                'direktur' => 'App\Models\Direktur',
                'wakil_direktur' => 'App\Models\Wadir',
                'kaprodi' => 'App\Models\Kaprodi',
                'mahasiswa' => 'App\Models\Mahasiswa',
                'dosen' => 'App\Models\Dosen',
            ];

            $receiverType = $roleToModelMap[$role] ?? '';
            $dosenType = $roleToModelMap['dosen'];

            $hasMessage = Message::where('jadwal_id', $jadwal->id)
                ->where(function ($query) use ($jadwal, $receiverType, $dosenType, $userId) {
                    $query->where(function ($subQuery) use ($jadwal, $receiverType, $dosenType, $userId) {
                        $subQuery->where('sender_id', $userId)
                            ->where('sender_type', $receiverType)
                            ->where('receiver_id', $jadwal->dosens_id)
                            ->where('receiver_type', $dosenType);
                    })->orWhere(function ($subQuery) use ($jadwal, $receiverType, $dosenType, $userId) {
                        $subQuery->where('receiver_id', $userId)
                            ->where('receiver_type', $receiverType)
                            ->where('sender_id', $jadwal->dosens_id)
                            ->where('sender_type', $dosenType);
                    });
                })
                ->whereNull('parent_id')
                ->exists();

            return [
                'id' => $jadwal->id,
                'kode' => $jadwal->matkul->kode ?? '-',
                'nama_matkul' => $jadwal->matkul->nama_matkul ?? '-',
                'sks' => ($jadwal->matkul->praktek ?? 0) + ($jadwal->matkul->teori ?? 0),
                'prodi' => $jadwal->kelas->prodi->nama_prodi ?? '-',
                'semester' => $jadwal->matkul->semester->semester ?? '-',
                'pertemuan_max' => $jadwal->pertemuan_max ?? 0,
                'berita_max' => $jadwal->berita_max ?? 0,
                'kontrak_max' => $jadwal->kontrak_max ?? 0,
                'matkuls_id' => $jadwal->matkuls_id,
                'kelas_id' => $jadwal->kelas_id,
                'dosens_id' => $jadwal->dosens_id,
                'has_message' => $hasMessage,
            ];
        });

        return Inertia::render('Admin/DataPerkuliahan/Show', [
            'dosen' => [
                'id' => $dosen->id,
                'nama' => $dosen->nama,
            ],
            'jadwals' => $formattedJadwals,
        ]);
    }
}
