<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanCetakKartuUjian;
use App\Notifications\KartuUjianNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PengajuanKartuUjianController extends Controller
{
    /**
     * Menampilkan daftar permohonan cetak kartu ujian.
     */
    public function index(Request $request): Response
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $pengajuans = PengajuanCetakKartuUjian::with([
            'mahasiswa.kelas.prodi',
            'mahasiswa.kelas.semester',
            'semester',
        ])
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', (int) $status);
            })
            ->when($search, function ($query, $search) {
                $query->whereHas('mahasiswa', function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Transform results for the frontend
        $formatted = $pengajuans->through(function ($p) {
            return [
                'id' => $p->id,
                'nim' => $p->mahasiswa->nim ?? '-',
                'nama_lengkap' => $p->mahasiswa->nama_lengkap ?? '-',
                'prodi' => $p->mahasiswa->kelas->prodi->nama_prodi ?? '-',
                'kelas' => $p->mahasiswa->kelas->nama_kelas ?? '-',
                'semester' => $p->semester->nama_semester ?? '-',
                'jenis_ujian' => strtoupper($p->jenis_ujian),
                'bukti_spp' => $p->bukti_spp,
                'bukti_pembayaran_ujian' => $p->bukti_pembayaran_ujian,
                'bulan_spp' => $p->bulan_spp,
                'tahun_spp' => $p->tahun_spp,
                'status' => (int) $p->status,
                'keterangan' => $p->keterangan,
                'created_at' => $p->created_at->translatedFormat('d F Y H:i'),
            ];
        });

        return Inertia::render('Admin/PengajuanKartuUjian/Index', [
            'pengajuans' => $formatted,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Memperbarui status verifikasi pengajuan kartu ujian (Disetujui/Ditolak).
     */
    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:1,2',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $pengajuan = PengajuanCetakKartuUjian::findOrFail($id);

        $pengajuan->update([
            'status' => (int) $request->status,
            'keterangan' => $request->keterangan,
            'petugas_id' => auth()->guard('admin')->user()->id,
        ]);

        // Kirim notifikasi database & WhatsApp
        $pengajuan->mahasiswa->notify(new KartuUjianNotification($pengajuan));

        $message = $request->status == 1
            ? 'Pengajuan kartu ujian berhasil disetujui (siap diambil).'
            : 'Pengajuan kartu ujian berhasil ditolak.';

        return redirect()->back()->with('success', $message);
    }
}
