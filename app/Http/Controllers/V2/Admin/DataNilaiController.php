<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jadwal;
use Inertia\Inertia;

class DataNilaiController extends Controller
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

        return Inertia::render('Admin/DataNilai/Index', [
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
            ->orderBy(function ($query) {
                $query->select('nama_matkul')
                    ->from('matkuls')
                    ->whereColumn('matkuls.id', 'jadwals.matkuls_id');
            }, 'asc')
            ->get();

        $formattedJadwals = $jadwals->map(function ($jadwal) {
            return [
                'id' => $jadwal->id,
                'kode' => $jadwal->matkul->kode ?? '-',
                'nama_matkul' => $jadwal->matkul->nama_matkul ?? '-',
                'sks' => ($jadwal->matkul->praktek ?? 0) + ($jadwal->matkul->teori ?? 0),
                'prodi' => $jadwal->kelas->prodi->nama_prodi ?? ($jadwal->matkul->prodi->nama_prodi ?? '-'),
                'semester' => $jadwal->matkul->semester->semester ?? '-',
                'pertemuan_max' => $jadwal->pertemuan_max ?? 0,
                'matkuls_id' => $jadwal->matkuls_id,
                'kelas_id' => $jadwal->kelas_id,
                'dosens_id' => $jadwal->dosens_id,
            ];
        });

        return Inertia::render('Admin/DataNilai/Show', [
            'dosen' => [
                'id' => $dosen->id,
                'nama' => $dosen->nama,
            ],
            'jadwals' => $formattedJadwals,
        ]);
    }
}
