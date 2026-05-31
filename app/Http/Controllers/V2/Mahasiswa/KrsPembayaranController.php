<?php

namespace App\Http\Controllers\V2\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Matkul;
use App\Models\Pembayaran;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class KrsPembayaranController extends Controller
{
    /**
     * Menampilkan halaman utama KRS & Pembayaran Mahasiswa.
     */
    public function index()
    {
        $user = auth()->guard('mahasiswa')->user();

        // Mengambil data mahasiswa beserta kelas, semester, prodi, dan pembimbing akademik tanpa N+1 query
        $mahasiswa = Mahasiswa::with([
            'kelas.semester',
            'kelas.prodi',
            'pembimbingAkademik',
        ])->findOrFail($user->id);

        $kelas = $mahasiswa->kelas;

        if (! $kelas || ! $kelas->id_semester || ! $kelas->id_prodi) {
            return Inertia::render('Mahasiswa/KrsPembayaran/Index', [
                'mahasiswa' => [
                    'nama_lengkap' => $mahasiswa->nama_lengkap,
                    'nim' => $mahasiswa->nim,
                    'prodi' => '-',
                    'semester' => '-',
                    'pembimbing_akademik' => $mahasiswa->pembimbingAkademik->nama ?? '-',
                ],
                'matkuls' => [],
                'pembayaran' => null,
                'krs' => null,
                'cekStatusKrs' => 0,
            ]);
        }

        $matkulKrs = Matkul::where('semester_id', $kelas->id_semester)
            ->where('prodi_id', $kelas->id_prodi)
            ->get()
            ->map(function ($matkul) {
                return [
                    'id' => $matkul->id,
                    'kode' => $matkul->kode,
                    'nama_matkul' => $matkul->nama_matkul,
                    'teori' => (int) $matkul->teori,
                    'praktek' => (int) $matkul->praktek,
                    'sks' => (int) ($matkul->teori + $matkul->praktek),
                ];
            });

        // Mengambil bukti pembayaran semester berjalan
        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $kelas->id_semester)
            ->latest()
            ->first();

        // Mengambil data KRS untuk semester berjalan
        $krs = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $kelas->id_semester)
            ->first();

        return Inertia::render('Mahasiswa/KrsPembayaran/Index', [
            'mahasiswa' => [
                'nama_lengkap' => $mahasiswa->nama_lengkap,
                'nim' => $mahasiswa->nim,
                'prodi' => $kelas->prodi->nama_prodi ?? '-',
                'semester' => $kelas->semester->nama_semester ?? '-',
                'pembimbing_akademik' => $mahasiswa->pembimbingAkademik->nama ?? '-',
            ],
            'matkuls' => $matkulKrs,
            'pembayaran' => $pembayaran ? [
                'id' => $pembayaran->id,
                'status_pembayaran' => (int) $pembayaran->status_pembayaran,
                'keterangan' => $pembayaran->keterangan,
                'bukti_pembayaran' => $pembayaran->bukti_pembayaran,
                'created_at' => $pembayaran->created_at->translatedFormat('d F Y H:i'),
            ] : null,
            'krs' => $krs ? [
                'id' => $krs->id,
                'status_krs' => (int) $krs->status_krs,
                'setuju_pa' => (int) $krs->setuju_pa,
                'setuju_mahasiswa' => (int) $krs->setuju_mahasiswa,
                'tahun_ajaran' => $krs->tahun_ajaran,
                'created_at' => $krs->created_at->translatedFormat('d F Y'),
            ] : null,
            'cekStatusKrs' => (int) $mahasiswa->status_krs,
        ]);
    }

    /**
     * Mengunggah bukti pembayaran KRS.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user = auth()->guard('mahasiswa')->user();
        $mahasiswa = Mahasiswa::with('kelas.semester')->findOrFail($user->id);

        if (! $mahasiswa->kelas || ! $mahasiswa->kelas->id_semester) {
            return redirect()->back()->with('error', 'Data kelas atau semester aktif tidak ditemukan.');
        }

        $semesterId = $mahasiswa->kelas->id_semester;

        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterId)
            ->first();

        $path = $request->file('file')->store('bukti_pembayaran', 'public');

        if ($pembayaran) {
            if ($pembayaran->bukti_pembayaran && Storage::disk('public')->exists($pembayaran->bukti_pembayaran)) {
                Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
            }
            $pembayaran->update([
                'bukti_pembayaran' => $path,
                'status_pembayaran' => 0,
                'keterangan' => 'Belum',
            ]);
        } else {
            Pembayaran::create([
                'mahasiswa_id' => $mahasiswa->id,
                'semester_id' => $semesterId,
                'jumlah_pembayaran' => 0,
                'bukti_pembayaran' => $path,
                'status_pembayaran' => 0,
                'keterangan' => 'Belum',
            ]);
        }

        return redirect()->route('v2.mahasiswa.krs-pembayaran.index')
            ->with('success', 'Bukti pembayaran berhasil diunggah dan sedang menunggu verifikasi administrasi.');
    }

    /**
     * Mengajukan dokumen KRS awal setelah pembayaran lunas.
     */
    public function pengajuanKrs()
    {
        $user = auth()->guard('mahasiswa')->user();
        $mahasiswa = Mahasiswa::with('kelas.semester', 'kelas.prodi')->findOrFail($user->id);

        if (! $mahasiswa->kelas || ! $mahasiswa->kelas->id_semester || ! $mahasiswa->kelas->id_prodi) {
            return redirect()->back()->with('error', 'Data kelas, prodi, atau semester tidak lengkap.');
        }

        $semesterId = $mahasiswa->kelas->id_semester;
        $prodiId = $mahasiswa->kelas->id_prodi;
        $kelasId = $mahasiswa->kelas_id;

        // Validasi pembayaran lunas
        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterId)
            ->first();

        if (! $pembayaran || $pembayaran->status_pembayaran !== 1 || $pembayaran->keterangan !== 'Sudah') {
            return redirect()->back()->with('error', 'Pengajuan KRS hanya dapat dilakukan setelah pembayaran semester terverifikasi lunas.');
        }

        $krs = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterId)
            ->first();

        if ($krs) {
            return redirect()->back()->with('error', 'Dokumen KRS untuk semester ini sudah pernah dibuat.');
        }

        $tahunAjaran = $mahasiswa->kelas->semester->tahun_ajaran ?? date('Y').'/'.(date('Y') + 1);

        Krs::create([
            'mahasiswa_id' => $mahasiswa->id,
            'semester_id' => $semesterId,
            'prodi_id' => $prodiId,
            'kelas_id' => $kelasId,
            'status_krs' => 0,
            'setuju_pa' => 0,
            'setuju_mahasiswa' => 0,
            'keterangan' => 'Belum',
            'tahun_ajaran' => $tahunAjaran,
        ]);

        return redirect()->route('v2.mahasiswa.krs-pembayaran.index')
            ->with('success', 'Dokumen KRS berhasil diinisiasi. Silakan periksa daftar matakuliah dan lakukan penandatanganan.');
    }

    /**
     * Persetujuan / Penandatanganan KRS oleh Mahasiswa.
     */
    public function persetujuan(Request $request, $id)
    {
        $user = auth()->guard('mahasiswa')->user();

        // Mencegah IDOR
        $krs = Krs::with(['mahasiswa.pembimbingAkademik', 'kelas', 'prodi'])->where('id', $id)
            ->where('mahasiswa_id', $user->id)
            ->firstOrFail();

        $krs->update([
            'setuju_mahasiswa' => 1,
        ]);

        $dosenPa = $krs->mahasiswa->pembimbingAkademik ?? null;
        if ($dosenPa && $dosenPa->no_telephone) {
            if (config('app.whatsapp_notification', true)) {
                $kelasNama = $krs->kelas->nama_kelas ?? '-';
                $prodiNama = $krs->prodi->nama_prodi ?? '-';
                $pesanDosen = "*SIA POLSA NOTIFICATION BOT* 🤖\n"
                    ."══════════════════════════\n"
                    ."🟡 *VERIFIKASI KRS DIBUTUHKAN*\n"
                    ."══════════════════════════\n"
                    ."• *Mahasiswa:* {$krs->mahasiswa->nama_lengkap}\n"
                    ."• *NIM:* {$krs->mahasiswa->nim}\n"
                    ."• *Kelas:* {$kelasNama}\n"
                    ."• *Prodi:* {$prodiNama}\n\n"
                    ."*Status:* Mahasiswa telah menandatangani KRS. Mohon Dosen PA untuk memeriksa dan memverifikasi KRS di sistem.\n"
                    ."──────────────────────────\n"
                    .'📅 _Waktu: '.now()->format('d-m-Y H:i')." WIB_\n"
                    ."🌐 _Akses Portal: siapv2.polsa.ac.id_\n"
                    .'_SIA POLSA - Sistem Informasi Akademik_';
                WhatsappService::kirim($dosenPa->no_telephone, $pesanDosen);
            }
        }

        return redirect()->route('v2.mahasiswa.krs-pembayaran.index')
            ->with('success', 'KRS berhasil ditandatangani dan telah diserahkan kepada Dosen Pembimbing Akademik untuk diverifikasi.');
    }

    /**
     * Menampilkan halaman dokumen cetak resmi KRS menggunakan view blade legacy.
     */
    public function cetakKrs($id)
    {
        $user = auth()->guard('mahasiswa')->user();

        // Mencegah IDOR & Eager load relasi yang dibutuhkan oleh view blade
        $krs = Krs::with([
            'mahasiswa.kelas.semester',
            'mahasiswa.kelas.prodi',
            'mahasiswa.pembimbingAkademik',
            'semester',
            'prodi',
        ])
            ->where('id', $id)
            ->where('mahasiswa_id', $user->id)
            ->firstOrFail();

        $prodiId = $krs->prodi_id;
        $semesterId = $krs->semester_id;

        $matkulKrs = Matkul::where('prodi_id', $prodiId)
            ->where('semester_id', $semesterId)
            ->get();

        return view('pages.mahasiswa.krs_pembayaran.cetak_krs', compact('krs', 'matkulKrs'));
    }
}
