<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanRekapNilai;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RekapNilaiController extends Controller
{
    /**
     * Menampilkan daftar pengajuan rekap nilai yang menunggu persetujuan (status = 0).
     */
    public function pengajuan(Request $request)
    {
        $pengajuans = PengajuanRekapNilai::with([
            'kelas' => fn ($query) => $query->withTrashed(),
            'kelas.prodi' => fn ($query) => $query->withTrashed(),
            'jadwal' => fn ($query) => $query->withTrashed(),
            'jadwal.dosen' => fn ($query) => $query->withTrashed(),
            'matkul' => fn ($query) => $query->withTrashed(),
        ])
            ->where('status', 0)
            ->latest()
            ->get();

        return Inertia::render('Admin/RekapNilai/Pengajuan', [
            'pengajuans' => $pengajuans,
        ]);
    }

    /**
     * Menampilkan daftar rekap nilai yang telah disetujui (status = 1).
     */
    public function disetujui(Request $request)
    {
        $pengajuans = PengajuanRekapNilai::with([
            'kelas' => fn ($query) => $query->withTrashed(),
            'kelas.prodi' => fn ($query) => $query->withTrashed(),
            'jadwal' => fn ($query) => $query->withTrashed(),
            'jadwal.dosen' => fn ($query) => $query->withTrashed(),
            'matkul' => fn ($query) => $query->withTrashed(),
        ])
            ->where('status', 1)
            ->latest()
            ->get();

        return Inertia::render('Admin/RekapNilai/Disetujui', [
            'pengajuans' => $pengajuans,
        ]);
    }
}
