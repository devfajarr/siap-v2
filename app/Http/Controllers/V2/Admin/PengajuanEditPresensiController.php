<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\RequestEditPresensi;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PengajuanEditPresensiController extends Controller
{
    public function index()
    {
        // Mendapatkan data yang belum disetujui, menggunakan pagination
        $pengajuans = RequestEditPresensi::with([
            'jadwal.dosen',
            'jadwal.matkul',
        ])->where('disetujui', false)
            ->latest()
            ->paginate(10)
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'pertemuan' => $item->pertemuan,
                    'nama_dosen' => $item->jadwal->dosen->nama ?? '-',
                    'nama_matkul' => $item->jadwal->matkul->nama_matkul ?? '-',
                ];
            });

        return Inertia::render('Admin/PengajuanEditPresensi/Index', [
            'pengajuans' => $pengajuans,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:request_edit_presensis,id',
        ]);

        // [OPTIMASI BUG] Gunakan findOrFail, menghindari method findOr tanpa callback
        $pengajuan = RequestEditPresensi::findOrFail($request->id);

        // [OPTIMASI LOGIKA] Hindari double klik / proses berulang jika sudah disetujui
        if ($pengajuan->disetujui) {
            return redirect()->back()->with('success', 'Pengajuan sudah disetujui sebelumnya.');
        }

        // [OPTIMASI EAGER LOADING] Load matkul dan kelas untuk menghindari N+1 saat memanggil $jadwal->matkul
        $jadwal = Jadwal::with(['dosen', 'matkul', 'kelas'])->findOrFail($pengajuan->jadwal_id);

        // Update disetujui
        $success = $pengajuan->update([
            'disetujui' => true,
        ]);

        if ($success) {
            // [OPTIMASI ERROR HANDLING] Bungkus dengan try-catch agar kegagalan API WA tidak merusak proses backend
            try {
                if ($jadwal->dosen && $jadwal->dosen->no_telephone) {
                    WhatsappService::kirim(
                        $jadwal->dosen->no_telephone,
                        "✅ *Pengajuan Edit Presensi Disetujui!*\n\n"
                            ."📖 *Mata Kuliah:* {$jadwal->matkul->nama_matkul}\n"
                            ."🏫 *Kelas:* {$jadwal->kelas->nama_kelas}\n"
                            ."📅 *Pertemuan:* {$pengajuan->pertemuan}\n\n"
                            .'Akses untuk mengedit presensi telah dibuka. 🎯'
                    );
                }
            } catch (\Exception $e) {
                // Log exception secara silent, tidak menampilkan 500 ke pengguna
                Log::error('Gagal mengirim WhatsApp persetujuan edit presensi: '.$e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Pengajuan Berhasil Diverifikasi');
    }
}
