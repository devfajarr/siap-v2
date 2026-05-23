<?php

namespace App\Http\Controllers\V2\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\PengajuanRekapPresensi;
use App\Models\PengajuanRekapkontrak;
use App\Models\PengajuanRekapBerita;
use App\Notifications\PengajuanPresensiNotification;
use App\Notifications\PengajuanResumeNotification;
use App\Notifications\PengajuanKontrakNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ApprovalController extends Controller
{
    // --- PRE SENSI ---
    public function presensiIndex()
    {
        $prodis = Prodi::select('id', 'nama_prodi')->get();

        $diajukan = PengajuanRekapPresensi::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 0)
            ->latest()
            ->get();

        $disetujui = PengajuanRekapPresensi::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 1)
            ->latest()
            ->get();

        return Inertia::render('Direktur/Approval/Presensi/Index', [
            'prodis' => $prodis,
            'diajukanList' => $diajukan,
            'disetujuiList' => $disetujui,
            'title' => 'Rekap Presensi Mahasiswa'
        ]);
    }

    public function presensiDetail($pertemuan, $matkul_id, $kelas_id, $jadwal_id)
    {
        $rentang = $pertemuan === '1-7' ? range(1, 7) : range(8, 14);

        $absens = Absen::with([
            'mahasiswa' => fn($q) => $q->withTrashed(),
            'kelas' => fn($q) => $q->withTrashed(),
            'matkul' => fn($q) => $q->withTrashed(),
            'dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('matkuls_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->where('jadwals_id', $jadwal_id)
            ->whereIn('pertemuan', $rentang)
            ->get();

        return Inertia::render('Direktur/Approval/Presensi/Detail', [
            'absens' => $absens,
            'params' => [
                'pertemuan' => $pertemuan,
                'matkul_id' => $matkul_id,
                'kelas_id' => $kelas_id,
                'jadwal_id' => $jadwal_id
            ]
        ]);
    }

    public function presensiApprove(Request $request, $pertemuan, $matkul_id, $kelas_id, $jadwal_id)
    {
        $rentang = $pertemuan === '1-7' ? range(1, 7) : range(8, 14);
        
        $absenRecords = Absen::where('matkuls_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->where('jadwals_id', $jadwal_id)
            ->whereIn('pertemuan', $rentang)
            ->get();

        foreach ($absenRecords as $absen) {
            $absen->setuju_wadir = $request->approve;
            $absen->save();
        }

        $allKaprodiApproved = $absenRecords->every(fn($a) => $a->setuju_kaprodi);
        $allWadirApproved = $absenRecords->every(fn($a) => $a->setuju_wadir);

        $statusPresensi = ($allKaprodiApproved && $allWadirApproved) ? 1 : 0;

        $pengajuan = PengajuanRekapPresensi::where('matkul_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->where('pertemuan', $pertemuan)
            ->first();

        if ($pengajuan) {
            $pengajuan->update(['status' => $statusPresensi]);
            
            if ($statusPresensi) {
                $dosen = Dosen::find($absenRecords->first()->dosens_id);
                if ($dosen) $dosen->notify(new PengajuanPresensiNotification($pengajuan));
            }
        }

        return redirect()->back()->with('success', 'Status persetujuan presensi pimpinan berhasil diperbarui');
    }

    // --- BERITA ACARA ---
    public function beritaIndex()
    {
        $prodis = Prodi::select('id', 'nama_prodi')->get();

        $diajukan = PengajuanRekapBerita::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 0)
            ->latest()
            ->get();

        $disetujui = PengajuanRekapBerita::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 1)
            ->latest()
            ->get();

        return Inertia::render('Direktur/Approval/Berita/Index', [
            'prodis' => $prodis,
            'diajukanList' => $diajukan,
            'disetujuiList' => $disetujui,
            'title' => 'Rekap Berita Acara Perkuliahan'
        ]);
    }

    // --- KONTRAK ---
    public function kontrakIndex()
    {
        $prodis = Prodi::select('id', 'nama_prodi')->get();

        $diajukan = PengajuanRekapkontrak::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 0)
            ->latest()
            ->get();

        $disetujui = PengajuanRekapkontrak::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 1)
            ->latest()
            ->get();

        return Inertia::render('Direktur/Approval/Kontrak/Index', [
            'prodis' => $prodis,
            'diajukanList' => $diajukan,
            'disetujuiList' => $disetujui,
            'title' => 'Rekap Kontrak Kuliah'
        ]);
    }
}
