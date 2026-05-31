<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direktur;
use App\Models\Mahasiswa;
use App\Models\PermohonanSurat;
use App\Models\TahunAkademik;
use App\Models\Wadir;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PermohonanSuratController extends Controller
{
    /**
     * Menampilkan daftar permohonan surat yang siap dicetak (setuju_kaprodi = 1, status = 0).
     */
    public function cetak(Request $request)
    {
        $query = PermohonanSurat::with([
            'mahasiswa.kelas.prodi',
            'mahasiswa.kelas.semester',
        ])
            ->where('setuju_kaprodi', 1)
            ->where('status', 0);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('mahasiswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis_permohonan', $request->jenis);
        }

        $permohonans = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/PermohonanSurat/Cetak', [
            'permohonans' => $permohonans,
            'filters' => [
                'search' => $request->search ?? '',
                'jenis' => $request->jenis ?? '',
            ],
        ]);
    }

    /**
     * Menampilkan daftar riwayat permohonan surat yang telah selesai (setuju_kaprodi = 1, status = 1).
     */
    public function selesai(Request $request)
    {
        $query = PermohonanSurat::with([
            'mahasiswa.kelas.prodi',
            'mahasiswa.kelas.semester',
        ])
            ->where('setuju_kaprodi', 1)
            ->where('status', 1);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('mahasiswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis_permohonan', $request->jenis);
        }

        $permohonans = $query->latest('updated_at')->paginate(10)->withQueryString();

        return Inertia::render('Admin/PermohonanSurat/Selesai', [
            'permohonans' => $permohonans,
            'filters' => [
                'search' => $request->search ?? '',
                'jenis' => $request->jenis ?? '',
            ],
        ]);
    }

    /**
     * Menyimpan nomor surat, mengubah status menjadi selesai (1), dan mengirimkan notifikasi WA.
     */
    public function terbitkan(Request $request, $id)
    {
        $request->validate([
            'no_surat' => 'required|string|max:255',
        ]);

        $permohonan = PermohonanSurat::with([
            'mahasiswa.kelas.prodi',
            'mahasiswa.kelas.semester',
        ])->findOrFail($id);

        if ($permohonan->status == 1) {
            return redirect()->back()->with('success', 'Nomor surat sudah diterbitkan sebelumnya.');
        }

        $permohonan->update([
            'status' => true,
            'no_surat' => $request->no_surat,
        ]);

        // Kirim notifikasi WhatsApp dengan penanganan error
        try {
            if ($permohonan->mahasiswa && $permohonan->mahasiswa->no_telephone) {
                $pesan = "*SIA POLSA NOTIFICATION BOT* 🤖\n"
                    ."══════════════════════════\n"
                    ."🟢 *SURAT TERBIT & SIAP DIAMBIL*\n"
                    ."══════════════════════════\n"
                    ."• *Layanan:* {$permohonan->jenis_permohonan}\n"
                    ."• *No. Surat:* {$request->no_surat}\n"
                    ."• *Pemohon:* {$permohonan->mahasiswa->nama_lengkap}\n\n"
                    ."*Status:* Dokumen fisik telah dicetak dan siap diambil di *Bagian Akademik POLSA*.\n"
                    ."──────────────────────────\n"
                    .'📅 _Waktu: '.now()->format('d-m-Y H:i')." WIB_\n"
                    ."🌐 _Akses Portal: siapv2.polsa.ac.id_\n"
                    .'_SIA POLSA - Sistem Informasi Akademik_';

                WhatsappService::kirim($permohonan->mahasiswa->no_telephone, $pesan);
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim WhatsApp penerbitan surat: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'Nomor surat berhasil diterbitkan dan notifikasi telah dikirim.');
    }

    /**
     * Merender dokumen siap cetak (Blade view) tanpa mengubah state database atau mengirim ulang WA.
     */
    public function cetakDokumen($id)
    {
        // Eager load lengkap untuk mencegah N+1 Query pada saat rendering view cetak
        $permohonan = PermohonanSurat::with([
            'mahasiswa.kelas.prodi',
            'mahasiswa.kelas.semester',
        ])->findOrFail($id);

        $anggotaTim = null;
        if (! empty($permohonan->anggota_tim) && is_array($permohonan->anggota_tim)) {
            $anggotaTim = Mahasiswa::with(['kelas.prodi', 'kelas.semester'])
                ->whereIn('id', $permohonan->anggota_tim)
                ->get();
        }

        // Fallback objek default jika data master aktif belum diatur di database
        $direktur = Direktur::where('status', 1)->first() ?? (object) ['nama' => 'Direktur (Belum Diatur)'];
        $wadir = Wadir::where('status', 1)->where('no', 1)->first() ?? (object) ['nama' => 'Wakil Direktur I (Belum Diatur)'];
        $tahunAkademik = TahunAkademik::where('status', 1)->first() ?? (object) ['tahun_akademik' => '2025/2026'];

        $kelas_baru = '';
        if ($permohonan->mahasiswa && $permohonan->mahasiswa->kelas) {
            $kelas_baru = $this->tukarHurufAB($permohonan->mahasiswa->kelas->nama_kelas);
        }

        if ($permohonan->jenis_permohonan == 'Pindah PT') {
            return view('pages.permohonan_surat.pindah_pt', compact('permohonan', 'direktur', 'tahunAkademik'));
        } elseif ($permohonan->jenis_permohonan == 'Keterangan Aktif Kuliah') {
            return view('pages.permohonan_surat.aktif_kuliah', compact('permohonan', 'direktur', 'tahunAkademik'));
        } elseif ($permohonan->jenis_permohonan == 'Ijin PKL') {
            return view('pages.permohonan_surat.ijin_pkl', compact('permohonan', 'direktur', 'tahunAkademik', 'anggotaTim'));
        } elseif ($permohonan->jenis_permohonan == 'Pindah Kelas') {
            return view('pages.permohonan_surat.pindah_kelas', compact('permohonan', 'direktur', 'tahunAkademik', 'kelas_baru'));
        } elseif ($permohonan->jenis_permohonan == 'Mengundurkan Diri') {
            return view('pages.permohonan_surat.mengundurkan_diri', compact('permohonan', 'wadir', 'tahunAkademik'));
        } elseif ($permohonan->jenis_permohonan == 'Cuti Kuliah') {
            return view('pages.permohonan_surat.cuti_kuliah', compact('permohonan', 'wadir', 'tahunAkademik'));
        } elseif ($permohonan->jenis_permohonan == 'Ijin Memperoleh Data PKL' || $permohonan->jenis_permohonan == 'Ijin Memperoleh Data TA') {
            return view('pages.permohonan_surat.ijin_memperoleh_data', compact('permohonan', 'direktur', 'tahunAkademik', 'anggotaTim'));
        }

        return abort(404, 'Template cetak untuk jenis permohonan ini tidak ditemukan.');
    }

    /**
     * Helper method untuk penukaran nama kelas.
     */
    protected function tukarHurufAB($kelas)
    {
        $huruf_terakhir = substr($kelas, -1);

        if ($huruf_terakhir === 'A') {
            $huruf_baru = 'B';
        } elseif ($huruf_terakhir === 'B') {
            $huruf_baru = 'A';
        } else {
            return $kelas;
        }

        return $this->pisahDuaHurufTerakhir(substr($kelas, 0, -1).$huruf_baru);
    }

    protected function pisahDuaHurufTerakhir($kelas)
    {
        if (strlen($kelas) >= 3) {
            $dua_terakhir = substr($kelas, -2);
            $bagian_awal = substr($kelas, 0, -2);

            return $bagian_awal.' '.$dua_terakhir;
        }

        return $kelas;
    }
}
