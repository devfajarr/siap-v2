<?php

namespace App\Http\Controllers\V2\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Admin;
use App\Models\PengajuanRekapPresensi;
use App\Models\PengajuanRekapkontrak;
use App\Models\PengajuanRekapBerita;
use App\Models\PengajuanRekapNilai;
use App\Models\PermohonanSurat;
use App\Models\Kontrak;
use App\Notifications\PengajuanPresensiNotification;
use App\Notifications\PengajuanKontrakNotification;
use App\Notifications\PengajuanResumeNotification;
use App\Notifications\PengajuanNilaiNotification;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ApprovalController extends Controller
{
    // --- PRE SENSI ---
    public function presensiDiajukan()
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;

        $presensis = PengajuanRekapPresensi::with(['matkul', 'kelas.prodi', 'jadwal.dosen'])
            ->where('status', 0)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        return Inertia::render('Kaprodi/Approval/Presensi/Index', [
            'presensis' => $presensis,
            'title' => 'Pengajuan Rekap Presensi'
        ]);
    }

    public function presensiDisetujui()
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;

        $presensis = PengajuanRekapPresensi::with(['matkul', 'kelas.prodi', 'jadwal.dosen'])
            ->where('status', 1)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        return Inertia::render('Kaprodi/Approval/Presensi/Index', [
            'presensis' => $presensis,
            'title' => 'Rekap Presensi Disetujui'
        ]);
    }

    public function presensiDetail($pertemuan, $matkul_id, $kelas_id, $jadwal_id)
    {
        $rentang = $pertemuan === '1-7' ? range(1, 7) : range(8, 14);

        $absens = Absen::with(['mahasiswa', 'kelas', 'matkul', 'dosen'])
            ->where('matkuls_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->where('jadwals_id', $jadwal_id)
            ->whereIn('pertemuan', $rentang)
            ->get();

        return Inertia::render('Kaprodi/Approval/Presensi/Detail', [
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
            $absen->setuju_kaprodi = $request->approve;
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

        return redirect()->back()->with('success', 'Status persetujuan presensi berhasil diperbarui');
    }

    // --- BERITA ACARA ---
    public function beritaDiajukan()
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;

        $beritas = PengajuanRekapBerita::with(['matkul', 'kelas.prodi', 'jadwal.dosen'])
            ->where('status', 0)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        return Inertia::render('Kaprodi/Approval/Berita/Index', [
            'beritas' => $beritas,
            'title' => 'Pengajuan Rekap Berita Acara'
        ]);
    }

    public function beritaDisetujui()
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;

        $beritas = PengajuanRekapBerita::with(['matkul', 'kelas.prodi', 'jadwal.dosen'])
            ->where('status', 1)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        return Inertia::render('Kaprodi/Approval/Berita/Index', [
            'beritas' => $beritas,
            'title' => 'Rekap Berita Acara Disetujui'
        ]);
    }

    // --- KONTRAK ---
    public function kontrakDiajukan()
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;

        $kontraks = PengajuanRekapkontrak::with(['matkul', 'kelas.prodi', 'jadwal.dosen'])
            ->where('status', 0)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        return Inertia::render('Kaprodi/Approval/Kontrak/Index', [
            'kontraks' => $kontraks,
            'title' => 'Pengajuan Rekap Kontrak'
        ]);
    }

    public function kontrakDisetujui()
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;

        $kontraks = PengajuanRekapkontrak::with(['matkul', 'kelas.prodi', 'jadwal.dosen'])
            ->where('status', 1)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        return Inertia::render('Kaprodi/Approval/Kontrak/Index', [
            'kontraks' => $kontraks,
            'title' => 'Rekap Kontrak Disetujui'
        ]);
    }

    // --- PERMOHONAN SURAT ---
    public function suratDiajukan(Request $request)
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;

        $query = PermohonanSurat::with(['mahasiswa.kelas.prodi', 'mahasiswa.kelas.semester'])
            ->whereHas('mahasiswa.kelas.prodi', function ($q) use ($prodiId) {
                $q->where('id', $prodiId);
            })
            ->where('setuju_kaprodi', 0);

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

        $permohonans->getCollection()->transform(function ($permohonan) {
            $anggotaTim = null;
            if (!empty($permohonan->anggota_tim) && is_array($permohonan->anggota_tim)) {
                $anggotaTim = \App\Models\Mahasiswa::whereIn('id', $permohonan->anggota_tim)->get();
            }
            $permohonan->anggota_tim_detail = $anggotaTim;
            return $permohonan;
        });

        return Inertia::render('Kaprodi/Approval/Surat/Index', [
            'permohonans' => $permohonans,
            'filters' => [
                'search' => $request->search ?? '',
                'jenis' => $request->jenis ?? '',
            ],
            'title' => 'Verifikasi Permohonan Surat'
        ]);
    }

    public function suratDisetujui(Request $request)
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = $user->prodis_id;

        $query = PermohonanSurat::with(['mahasiswa.kelas.prodi', 'mahasiswa.kelas.semester'])
            ->whereHas('mahasiswa.kelas.prodi', function ($q) use ($prodiId) {
                $q->where('id', $prodiId);
            })
            ->whereIn('setuju_kaprodi', [1, 2]);

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

        $permohonans->getCollection()->transform(function ($permohonan) {
            $anggotaTim = null;
            if (!empty($permohonan->anggota_tim) && is_array($permohonan->anggota_tim)) {
                $anggotaTim = \App\Models\Mahasiswa::whereIn('id', $permohonan->anggota_tim)->get();
            }
            $permohonan->anggota_tim_detail = $anggotaTim;
            return $permohonan;
        });

        return Inertia::render('Kaprodi/Approval/Surat/Index', [
            'permohonans' => $permohonans,
            'filters' => [
                'search' => $request->search ?? '',
                'jenis' => $request->jenis ?? '',
            ],
            'title' => 'Riwayat Persetujuan Surat'
        ]);
    }

    public function permohonanSuratApprove(Request $request)
    {
        $permohonan = PermohonanSurat::with('mahasiswa')->findOrFail($request->id);
        $nomor_akademik = Admin::pluck('no_telephone')->first();

        $permohonan->update([
            'setuju_kaprodi' => 1,
            'keterangan_ditolak' => null
        ]);

        if ($nomor_akademik) {
            WhatsappService::kirim(
                $nomor_akademik,
                "✅ *Verifikasi Permohonan Surat* ✅\n\n" .
                "📄 Jenis Surat: {$permohonan->jenis_permohonan}\n" .
                "👤 Nama Mahasiswa: {$permohonan->mahasiswa->nama_lengkap}\n" .
                "🎓 NIM: {$permohonan->mahasiswa->nim}\n\n" .
                "📌 Permohonan surat ini telah diverifikasi oleh Kaprodi dan siap untuk dicetak"
            );
        }

        return redirect()->back()->with('success', 'Permohonan surat berhasil diverifikasi');
    }

    public function permohonanSuratReject(Request $request, $id)
    {
        $request->validate([
            'keterangan_ditolak' => 'required|string|min:5'
        ]);

        $permohonan = PermohonanSurat::with('mahasiswa')->findOrFail($id);
        
        $permohonan->update([
            'setuju_kaprodi' => 2,
            'keterangan_ditolak' => $request->keterangan_ditolak
        ]);

        if ($permohonan->mahasiswa && $permohonan->mahasiswa->no_telephone) {
            WhatsappService::kirim(
                $permohonan->mahasiswa->no_telephone,
                "❌ *Pemberitahuan Surat Permohonan* ❌\n\n" .
                "📄 Jenis Surat: {$permohonan->jenis_permohonan}\n" .
                "📌 Status: *Ditolak oleh Kaprodi*\n" .
                "📝 Alasan: *{$request->keterangan_ditolak}*\n\n" .
                "📍 Silakan hubungi Kaprodi untuk informasi lebih lanjut atau ajukan permohonan baru dengan data yang benar."
            );
        }

        return redirect()->back()->with('success', 'Permohonan surat berhasil ditolak.');
    }
}
