<?php

namespace App\Http\Controllers\V2\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\PermohonanSurat;
use App\Models\TahunAkademik;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PermohonanSuratController extends Controller
{
    /**
     * Menampilkan halaman utama dan daftar riwayat permohonan surat mahasiswa.
     */
    public function index()
    {
        $user = auth()->guard('mahasiswa')->user();

        // Mengambil profil mahasiswa beserta kelas dan prodi tanpa N+1
        $mahasiswa = Mahasiswa::with(['kelas.semester', 'kelas.prodi'])->findOrFail($user->id);

        $permohonans = PermohonanSurat::with(['mahasiswa'])
            ->where(function ($query) use ($user) {
                $query->where('mahasiswa_id', $user->id)
                    ->orWhereJsonContains('anggota_tim', (string) $user->id)
                    ->orWhereJsonContains('anggota_tim', $user->id);
            })
            ->latest()
            ->paginate(10);

        // Mengambil prodi dan jenjang dari kelas mahasiswa pengusul
        $prodi = $mahasiswa->kelas->prodi ?? null;
        $prodiId = $prodi->id ?? null;
        $jenjang = $prodi->jenjang ?? null;

        // Menentukan batas minimal semester pelaksanaan PKL/TA berdasarkan jenjang prodi
        $minSemester = 1;
        if ($jenjang === 'D3') {
            $minSemester = 4; // Untuk D3 pelaksanaannya semester 4
        } elseif ($jenjang === 'D4' || $jenjang === 'S1') {
            $minSemester = 6; // Untuk D4/S1 pelaksanaannya semester 6
        }

        // Mengambil tahun akademik aktif
        $tahunAkademik = TahunAkademik::where('status', 1)->first();
        $tahunString = $tahunAkademik ? $tahunAkademik->tahun_akademik : '2025/2026';

        $tahunAwal = '';
        $tahunAkhir = '';
        if (str_contains($tahunString, '/')) {
            $parts = explode('/', $tahunString);
            $tahunAwal = $parts[0];
            $tahunAkhir = $parts[1];
        }

        $tahunAkademikFormatted = ($tahunAwal && $tahunAkhir)
            ? "{$tahunAwal}/GASAL /{$tahunAkhir}/GENAP"
            : $tahunString;

        // Mengambil seluruh pengajuan PKL/Observasi aktif pada tahun akademik ini
        $activePklSubmissions = PermohonanSurat::whereIn('jenis_permohonan', [
            'Ijin PKL', 'Ijin Memperoleh Data PKL', 'Ijin Memperoleh Data TA',
        ])
            ->where('tahun_akademik', $tahunAkademikFormatted)
            ->where('setuju_kaprodi', '!=', 2)
            ->get();

        $terdaftarIds = [];
        foreach ($activePklSubmissions as $sub) {
            $terdaftarIds[] = $sub->mahasiswa_id;
            if (! empty($sub->anggota_tim) && is_array($sub->anggota_tim)) {
                $terdaftarIds = array_merge($terdaftarIds, $sub->anggota_tim);
            }
        }
        $terdaftarIds = array_unique(array_map('intval', $terdaftarIds));

        $hasActivePkl = in_array($user->id, $terdaftarIds);

        // Mengambil daftar rekan mahasiswa (berdasarkan Program Studi yang sama, memenuhi syarat minimal semester, dan belum terdaftar di PKL/TA lain)
        $queryMahasiswa = Mahasiswa::where('id', '!=', $user->id)
            ->whereNotIn('id', $terdaftarIds);

        if ($prodiId) {
            $queryMahasiswa->whereHas('kelas', function ($q) use ($prodiId, $minSemester) {
                $q->where('id_prodi', $prodiId)
                    ->whereHas('semester', function ($sq) use ($minSemester) {
                        $sq->where('semester', '>=', $minSemester);
                    });
            });
        }

        $daftarMahasiswa = $queryMahasiswa->select('id', 'nim', 'nama_lengkap')
            ->orderBy('nama_lengkap')
            ->get();

        return Inertia::render('Mahasiswa/PermohonanSurat/Index', [
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'nama_lengkap' => $mahasiswa->nama_lengkap,
                'nim' => $mahasiswa->nim,
                'kelas' => $mahasiswa->kelas->nama_kelas ?? '-',
                'prodi' => $mahasiswa->kelas->prodi->nama_prodi ?? '-',
                'semester' => $mahasiswa->kelas->semester->semester ?? '-',
            ],
            'daftarMahasiswa' => $daftarMahasiswa,
            'tahunAkademik' => $tahunAkademikFormatted,
            'permohonans' => $permohonans,
            'hasActivePkl' => $hasActivePkl,
        ]);
    }

    /**
     * Menyimpan formulir pengajuan surat permohonan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jenis_permohonan' => 'required|string|max:255',
            'anggota_tim' => 'nullable|array|max:5',
            'anggota_tim.*' => 'integer|exists:mahasiswas,id',
        ], [
            'jenis_permohonan.required' => 'Jenis permohonan surat wajib dipilih.',
            'anggota_tim.max' => 'Maksimal anggota tim yang dapat dipilih adalah 5 mahasiswa.',
            'anggota_tim.*.exists' => 'Mahasiswa anggota tim yang dipilih tidak valid.',
        ]);

        $user = auth()->guard('mahasiswa')->user();
        $mahasiswa = Mahasiswa::with(['kelas.prodi.kaprodi'])->findOrFail($user->id);

        $jenis = $request->jenis_permohonan;
        // Normalisasi kata 'Izin' menjadi 'Ijin' untuk konsistensi database dan kompatibilitas template cetak legacy di Admin
        $jenis = str_replace('Izin', 'Ijin', $jenis);

        $anggotaTim = (! empty($request->anggota_tim) && is_array($request->anggota_tim))
            ? array_values(array_unique(array_map('intval', $request->anggota_tim)))
            : null;

        $tahunAjuan = $request->tahun_akademik ?? '2025/2026';

        if (in_array($jenis, ['Ijin PKL', 'Ijin Memperoleh Data PKL', 'Ijin Memperoleh Data TA'])) {
            // 1. Validasi apakah pengusul sudah terdaftar di pengajuan aktif tahun ini
            $activeSubmissions = PermohonanSurat::whereIn('jenis_permohonan', ['Ijin PKL', 'Ijin Memperoleh Data PKL', 'Ijin Memperoleh Data TA'])
                ->where('tahun_akademik', $tahunAjuan)
                ->where('setuju_kaprodi', '!=', 2)
                ->get();

            $allActiveIds = [];
            foreach ($activeSubmissions as $sub) {
                $allActiveIds[] = $sub->mahasiswa_id;
                if (! empty($sub->anggota_tim) && is_array($sub->anggota_tim)) {
                    $allActiveIds = array_merge($allActiveIds, $sub->anggota_tim);
                }
            }
            $allActiveIds = array_unique(array_map('intval', $allActiveIds));

            if (in_array($mahasiswa->id, $allActiveIds)) {
                return redirect()->back()->withErrors([
                    'jenis_permohonan' => 'Anda sudah terdaftar dalam pengajuan PKL atau Observasi lain yang sedang aktif pada tahun akademik ini.',
                ]);
            }

            // 2. Validasi apakah ada anggota tim yang sudah terdaftar di pengajuan aktif
            if (! empty($anggotaTim)) {
                foreach ($anggotaTim as $anggotaId) {
                    if (in_array($anggotaId, $allActiveIds)) {
                        $mhsName = Mahasiswa::where('id', $anggotaId)->value('nama_lengkap') ?? "ID {$anggotaId}";

                        return redirect()->back()->withErrors([
                            'anggota_tim' => "Mahasiswa atas nama {$mhsName} sudah terdaftar dalam kelompok pengajuan PKL/Observasi lain.",
                        ]);
                    }
                }
            }
        }

        $payload = [
            'mahasiswa_id' => $mahasiswa->id,
            'setuju_kaprodi' => 0,
            'status' => 0,
            'jenis_permohonan' => $jenis,
            'anggota_tim' => $anggotaTim,
            'tahun_akademik' => $tahunAjuan,
        ];

        if ($jenis === 'Keterangan Aktif Kuliah') {
            $validated = $request->validate([
                'nama_orang_tua' => 'required|string|max:255',
                'alamat_orang_tua' => 'required|string|max:500',
                'pekerjaan' => 'required|string|max:255',
                'nip' => 'nullable|string|max:255',
                'pangkat_golongan' => 'nullable|string|max:255',
                'nama_instansi' => 'required|string|max:255',
                'alamat_instansi' => 'required|string|max:500',
                'keperluan' => 'required|string|max:255',
            ], [
                'nama_orang_tua.required' => 'Nama orang tua harus diisi.',
                'alamat_orang_tua.required' => 'Alamat orang tua harus diisi.',
                'pekerjaan.required' => 'Pekerjaan orang tua harus diisi.',
                'nama_instansi.required' => 'Nama instansi harus diisi.',
                'alamat_instansi.required' => 'Alamat instansi harus diisi.',
                'keperluan.required' => 'Keperluan pengajuan surat harus diisi.',
            ]);

            $payload = array_merge($payload, [
                'nama_orang_tua' => $validated['nama_orang_tua'],
                'alamat_orang_tua' => $validated['alamat_orang_tua'],
                'pekerjaan' => $validated['pekerjaan'],
                'nip' => $validated['nip'] ?? '-',
                'pangkat_golongan' => $validated['pangkat_golongan'] ?? '-',
                'nama_instansi' => $validated['nama_instansi'],
                'alamat_instansi' => $validated['alamat_instansi'],
                'keperluan' => $validated['keperluan'],
            ]);

        } elseif ($jenis === 'Cuti Kuliah') {
            $validated = $request->validate([
                'alasan_cuti' => 'required|string|max:500',
            ], [
                'alasan_cuti.required' => 'Alasan cuti wajib diisi.',
            ]);

            $masaCuti = ($mahasiswa->kelas && $mahasiswa->kelas->semester)
                ? (string) $mahasiswa->kelas->semester->semester
                : '1';

            $payload = array_merge($payload, [
                'alasan_cuti' => $validated['alasan_cuti'],
                'masa_cuti' => $masaCuti,
            ]);

        } elseif ($jenis === 'Pindah Kelas') {
            $validated = $request->validate([
                'kelas_tujuan' => 'required|string|max:255',
            ], [
                'kelas_tujuan.required' => 'Kelas tujuan wajib diisi.',
            ]);

            $kelasAsal = $mahasiswa->kelas->nama_kelas ?? '-';

            $payload = array_merge($payload, [
                'kelas_asal' => $kelasAsal,
                'kelas_tujuan' => $validated['kelas_tujuan'],
            ]);

        } elseif ($jenis === 'Pindah PT') {
            $validated = $request->validate([
                'pt_tujuan' => 'required|string|max:255',
                'status_akreditasi' => 'required|string|max:255',
            ], [
                'pt_tujuan.required' => 'Perguruan Tinggi tujuan wajib diisi.',
                'status_akreditasi.required' => 'Status akreditasi PT tujuan wajib diisi.',
            ]);

            $payload = array_merge($payload, [
                'pt_asal' => 'Politeknik Sawunggalih Aji',
                'pt_tujuan' => $validated['pt_tujuan'],
                'status_akreditasi' => $validated['status_akreditasi'],
            ]);

        } elseif ($jenis === 'Mengundurkan Diri') {
            // Cukup atribut dasar
        } elseif ($jenis === 'Ijin Memperoleh Data TA' || $jenis === 'Ijin Memperoleh Data PKL') {
            $validated = $request->validate([
                'nama_instansi' => 'required|string|max:255',
                'alamat_instansi' => 'required|string|max:500',
                'judul_laporan' => 'required|string|max:255',
                'data_diminta' => 'required|array|min:1',
                'data_diminta.*' => 'required|string|max:255',
            ], [
                'nama_instansi.required' => 'Nama instansi tujuan harus diisi.',
                'alamat_instansi.required' => 'Alamat instansi tujuan harus diisi.',
                'judul_laporan.required' => 'Judul laporan harus diisi.',
                'data_diminta.required' => 'Minimal harus ada 1 data yang diminta.',
                'data_diminta.*.required' => 'Item data yang diminta tidak boleh kosong.',
            ]);

            $payload = array_merge($payload, [
                'nama_instansi' => $validated['nama_instansi'],
                'alamat_instansi' => $validated['alamat_instansi'],
                'judul_laporan' => $validated['judul_laporan'],
                'data_diminta' => $validated['data_diminta'],
            ]);

        } elseif ($jenis === 'Ijin PKL') {
            $validated = $request->validate([
                'nama_instansi' => 'required|string|max:255',
                'alamat_instansi' => 'required|string|max:500',
                'pimpinan' => 'required|string|max:255',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            ], [
                'nama_instansi.required' => 'Nama instansi harus diisi.',
                'alamat_instansi.required' => 'Alamat instansi harus diisi.',
                'pimpinan.required' => 'Nama pimpinan/penanggung jawab instansi harus diisi.',
                'tanggal_mulai.required' => 'Tanggal mulai PKL wajib diisi.',
                'tanggal_selesai.required' => 'Tanggal selesai PKL wajib diisi.',
                'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            ]);

            $payload = array_merge($payload, [
                'nama_instansi' => $validated['nama_instansi'],
                'alamat_instansi' => $validated['alamat_instansi'],
                'pimpinan' => $validated['pimpinan'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
            ]);
        }

        $permohonan = PermohonanSurat::create($payload);

        // Notifikasi WA ke Kaprodi
        try {
            if (config('app.whatsapp_notification', true)) {
                $kaprodi = $mahasiswa->kelas->prodi->kaprodi ?? null;
                if ($kaprodi && $kaprodi->no_telephone) {
                    $prodiNama = $mahasiswa->kelas->prodi->nama_prodi ?? '-';
                    $pesan = "*SIA POLSA NOTIFICATION BOT* 🤖\n"
                        ."══════════════════════════\n"
                        ."🟡 *PENGAJUAN SURAT BARU*\n"
                        ."══════════════════════════\n"
                        ."• *Layanan:* {$jenis}\n"
                        ."• *Pemohon:* {$mahasiswa->nama_lengkap}\n"
                        ."• *NIM:* {$mahasiswa->nim}\n"
                        ."• *Prodi:* {$prodiNama}\n\n"
                        ."Mohon untuk memeriksa dan melakukan verifikasi pengajuan ini melalui sistem SIA POLSA.\n"
                        ."──────────────────────────\n"
                        .'📅 _Waktu: '.now()->format('d-m-Y H:i')." WIB_\n"
                        ."🌐 _Akses Portal: siapv2.polsa.ac.id_\n"
                        .'_SIA POLSA - Sistem Informasi Akademik_';

                    WhatsappService::kirim($kaprodi->no_telephone, $pesan);
                }
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi WA pengajuan surat: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'Formulir permohonan surat berhasil diajukan dan menunggu verifikasi.');
    }

    /**
     * Mengupdate formulir pengajuan yang masih berstatus belum disetujui.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->guard('mahasiswa')->user();

        // Mencegah celah IDOR dan memastikan surat belum diproses, dengan otorisasi 403 untuk anggota
        $permohonan = PermohonanSurat::where('setuju_kaprodi', 0)->findOrFail($id);

        if ($permohonan->mahasiswa_id !== $user->id) {
            abort(403, 'Hanya ketua tim yang diizinkan untuk mengubah pengajuan ini.');
        }

        if ($request->has('anggota_tim')) {
            $request->validate([
                'anggota_tim' => 'nullable|array|max:5',
                'anggota_tim.*' => 'integer|exists:mahasiswas,id',
            ]);
        }

        $anggotaTim = (! empty($request->anggota_tim) && is_array($request->anggota_tim))
            ? array_values(array_unique(array_map('intval', $request->anggota_tim)))
            : null;

        $jenis = $permohonan->jenis_permohonan;

        if (in_array($jenis, ['Ijin PKL', 'Ijin Memperoleh Data PKL', 'Ijin Memperoleh Data TA']) && ! empty($anggotaTim)) {
            $otherSubmissions = PermohonanSurat::whereIn('jenis_permohonan', ['Ijin PKL', 'Ijin Memperoleh Data PKL', 'Ijin Memperoleh Data TA'])
                ->where('tahun_akademik', $permohonan->tahun_akademik)
                ->where('id', '!=', $id)
                ->where('setuju_kaprodi', '!=', 2)
                ->get();

            $allOtherActiveIds = [];
            foreach ($otherSubmissions as $sub) {
                $allOtherActiveIds[] = $sub->mahasiswa_id;
                if (! empty($sub->anggota_tim) && is_array($sub->anggota_tim)) {
                    $allOtherActiveIds = array_merge($allOtherActiveIds, $sub->anggota_tim);
                }
            }
            $allOtherActiveIds = array_unique(array_map('intval', $allOtherActiveIds));

            foreach ($anggotaTim as $anggotaId) {
                if (in_array($anggotaId, $allOtherActiveIds)) {
                    $mhsName = Mahasiswa::where('id', $anggotaId)->value('nama_lengkap') ?? "ID {$anggotaId}";

                    return redirect()->back()->withErrors([
                        'anggota_tim' => "Mahasiswa atas nama {$mhsName} sudah terdaftar dalam kelompok pengajuan PKL/Observasi lain.",
                    ]);
                }
            }
        }

        if ($jenis === 'Keterangan Aktif Kuliah') {
            $validated = $request->validate([
                'nama_orang_tua' => 'required|string|max:255',
                'alamat_orang_tua' => 'required|string|max:500',
                'pekerjaan' => 'required|string|max:255',
                'nip' => 'nullable|string|max:255',
                'pangkat_golongan' => 'nullable|string|max:255',
                'nama_instansi' => 'required|string|max:255',
                'alamat_instansi' => 'required|string|max:500',
                'keperluan' => 'required|string|max:255',
            ]);

            $permohonan->update([
                'nama_orang_tua' => $validated['nama_orang_tua'],
                'alamat_orang_tua' => $validated['alamat_orang_tua'],
                'pekerjaan' => $validated['pekerjaan'],
                'nip' => $validated['nip'] ?? '-',
                'pangkat_golongan' => $validated['pangkat_golongan'] ?? '-',
                'nama_instansi' => $validated['nama_instansi'],
                'alamat_instansi' => $validated['alamat_instansi'],
                'keperluan' => $validated['keperluan'],
            ]);

        } elseif ($jenis === 'Cuti Kuliah') {
            $validated = $request->validate([
                'alasan_cuti' => 'required|string|max:500',
            ]);
            $permohonan->update(['alasan_cuti' => $validated['alasan_cuti']]);

        } elseif ($jenis === 'Pindah Kelas') {
            $validated = $request->validate([
                'kelas_tujuan' => 'required|string|max:255',
            ]);
            $permohonan->update(['kelas_tujuan' => $validated['kelas_tujuan']]);

        } elseif ($jenis === 'Pindah PT') {
            $validated = $request->validate([
                'pt_tujuan' => 'required|string|max:255',
                'status_akreditasi' => 'required|string|max:255',
            ]);
            $permohonan->update([
                'pt_tujuan' => $validated['pt_tujuan'],
                'status_akreditasi' => $validated['status_akreditasi'],
            ]);

        } elseif ($jenis === 'Ijin Memperoleh Data TA' || $jenis === 'Ijin Memperoleh Data PKL') {
            $validated = $request->validate([
                'nama_instansi' => 'required|string|max:255',
                'alamat_instansi' => 'required|string|max:500',
                'judul_laporan' => 'required|string|max:255',
                'data_diminta' => 'required|array|min:1',
                'data_diminta.*' => 'required|string|max:255',
            ]);

            $permohonan->update([
                'nama_instansi' => $validated['nama_instansi'],
                'alamat_instansi' => $validated['alamat_instansi'],
                'judul_laporan' => $validated['judul_laporan'],
                'data_diminta' => $validated['data_diminta'],
                'anggota_tim' => $anggotaTim,
            ]);

        } elseif ($jenis === 'Ijin PKL') {
            $validated = $request->validate([
                'nama_instansi' => 'required|string|max:255',
                'alamat_instansi' => 'required|string|max:500',
                'pimpinan' => 'required|string|max:255',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            ]);

            $permohonan->update([
                'nama_instansi' => $validated['nama_instansi'],
                'alamat_instansi' => $validated['alamat_instansi'],
                'pimpinan' => $validated['pimpinan'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'anggota_tim' => $anggotaTim,
            ]);
        }

        return redirect()->back()->with('success', 'Data permohonan surat berhasil diperbarui.');
    }

    /**
     * Menghapus permohonan surat yang belum diproses.
     */
    public function destroy($id)
    {
        $user = auth()->guard('mahasiswa')->user();

        // Mencegah celah IDOR dan penghapusan surat yang sudah diverifikasi, dengan otorisasi 403 untuk anggota
        $permohonan = PermohonanSurat::where('setuju_kaprodi', 0)->findOrFail($id);

        if ($permohonan->mahasiswa_id !== $user->id) {
            abort(403, 'Hanya ketua tim yang diizinkan untuk menghapus pengajuan ini.');
        }

        $permohonan->delete();

        return redirect()->back()->with('success', 'Surat permohonan berhasil dibatalkan dan dihapus.');
    }
}
