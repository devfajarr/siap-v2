<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\TahunAkademik;
use App\Notifications\PembayaranNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PembayaranController extends Controller
{
    /**
     * Menampilkan daftar pembayaran yang diajukan (status_pembayaran = 0).
     */
    public function diajukan(Request $request)
    {
        $pembayarans = Pembayaran::with([
            'mahasiswa.kelas.prodi',
            'mahasiswa.kelas.semester',
            'prodi',
            'kelas',
            'semester',
        ])
            ->where('status_pembayaran', 0)
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Pembayaran/Diajukan', [
            'pembayarans' => $pembayarans,
        ]);
    }

    /**
     * Menampilkan daftar pembayaran yang telah disetujui (status_pembayaran = 1).
     */
    public function disetujui(Request $request)
    {
        $pembayarans = Pembayaran::with([
            'mahasiswa.kelas.prodi',
            'mahasiswa.kelas.semester',
            'prodi',
            'kelas',
            'semester',
        ])
            ->where('status_pembayaran', 1)
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Pembayaran/Disetujui', [
            'pembayarans' => $pembayarans,
        ]);
    }

    /**
     * Memperbarui status verifikasi pembayaran.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:0,1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $pembayaran = Pembayaran::find($id);

        if (! $pembayaran) {
            return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $mahasiswa = Mahasiswa::findOrFail($pembayaran->mahasiswa_id);

        $pembayaran->status_pembayaran = $request->status_pembayaran;
        $pembayaran->keterangan = $request->keterangan;
        $pembayaran->save();

        $mahasiswa->notify(new PembayaranNotification($pembayaran));

        if ($request->status_pembayaran == 1) {
            $tahunAjaran = TahunAkademik::where('status', 1)->value('tahun_akademik');

            Krs::firstOrCreate([
                'mahasiswa_id' => $pembayaran->mahasiswa_id,
                'semester_id' => $pembayaran->semester_id,
                'tahun_ajaran' => $tahunAjaran,
            ], [
                'prodi_id' => $pembayaran->prodi_id,
                'kelas_id' => $pembayaran->kelas_id,
                'status_krs' => 0,
                'setuju_pa' => 0,
                'setuju_mahasiswa' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }
}
