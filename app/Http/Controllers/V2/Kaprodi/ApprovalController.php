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
use App\Models\Kelas;
use App\Models\Resume;
use App\Models\Semester;
use App\Models\TahunAkademik;
use App\Models\Wadir;
use App\Models\Kaprodi;
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
    public function presensiIndex()
    {
        $prodiId = session('user.activeProdiId');

        $diajukan = PengajuanRekapPresensi::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 0)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        $disetujui = PengajuanRekapPresensi::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 1)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        return Inertia::render('Kaprodi/Approval/Presensi/Index', [
            'diajukanList' => $diajukan,
            'disetujuiList' => $disetujui,
            'title' => 'Rekap Presensi Mahasiswa'
        ]);
    }

    public function presensiDetail($pertemuan, $matkul_id, $kelas_id, $jadwal_id)
    {
        $kelas = Kelas::findOrFail($kelas_id);
        if (!in_array($kelas->id_prodi, session('user.prodiIds', []))) {
            abort(403, 'Unauthorized action.');
        }

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
        $kelas = Kelas::findOrFail($kelas_id);
        if (!in_array($kelas->id_prodi, session('user.prodiIds', []))) {
            abort(403, 'Unauthorized action.');
        }

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
    public function beritaIndex()
    {
        $prodiId = session('user.activeProdiId');

        $diajukan = PengajuanRekapBerita::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 0)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        $disetujui = PengajuanRekapBerita::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 1)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        return Inertia::render('Kaprodi/Approval/Berita/Index', [
            'diajukanList' => $diajukan,
            'disetujuiList' => $disetujui,
            'title' => 'Rekap Berita Acara Perkuliahan'
        ]);
    }

    // --- KONTRAK ---
    public function kontrakIndex()
    {
        $prodiId = session('user.activeProdiId');

        $diajukan = PengajuanRekapkontrak::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 0)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        $disetujui = PengajuanRekapkontrak::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('status', 1)
            ->whereHas('kelas.prodi', function ($query) use ($prodiId) {
                $query->where('id', $prodiId);
            })
            ->latest()
            ->get();

        return Inertia::render('Kaprodi/Approval/Kontrak/Index', [
            'diajukanList' => $diajukan,
            'disetujuiList' => $disetujui,
            'title' => 'Rekap Kontrak Kuliah'
        ]);
    }

    // --- PERMOHONAN SURAT ---
    public function suratDiajukan(Request $request)
    {
        $prodiId = session('user.activeProdiId');

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
        $prodiId = session('user.activeProdiId');

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

    public function beritaDetail($pertemuan, $matkul_id, $kelas_id, $jadwal_id)
    {
        $kelas = Kelas::findOrFail($kelas_id);
        if (!in_array($kelas->id_prodi, session('user.prodiIds', []))) {
            abort(403, 'Unauthorized action.');
        }

        $rentang = $pertemuan === '1-7' ? range(1, 7) : range(8, 14);

        $beritas = Resume::with([
            'dosen' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'kelas.semester' => fn($q) => $q->withTrashed(),
            'matkul' => fn($q) => $q->withTrashed()
        ])
            ->where('matkuls_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->where('jadwals_id', $jadwal_id)
            ->whereIn('pertemuan', $rentang)
            ->get();

        $semester = Semester::where('status', 1)->first();
        $sem = $semester ? (($semester->semester % 2 == 0) ? "GENAP" : "GANJIL") : "GANJIL";
        $tahunAkademik = TahunAkademik::where('status', 1)->first();

        return Inertia::render('Kaprodi/Approval/Berita/Detail', [
            'beritas' => $beritas,
            'tahunAkademik' => $tahunAkademik,
            'sem' => $sem,
            'params' => [
                'pertemuan' => $pertemuan,
                'matkul_id' => $matkul_id,
                'kelas_id' => $kelas_id,
                'jadwal_id' => $jadwal_id
            ]
        ]);
    }

    public function beritaApprove(Request $request, $pertemuan, $matkul_id, $kelas_id, $jadwal_id)
    {
        $kelas = Kelas::findOrFail($kelas_id);
        if (!in_array($kelas->id_prodi, session('user.prodiIds', []))) {
            abort(403, 'Unauthorized action.');
        }

        $rentang = $pertemuan === '1-7' ? range(1, 7) : range(8, 14);

        $resumeRecords = Resume::where('matkuls_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->where('jadwals_id', $jadwal_id)
            ->whereIn('pertemuan', $rentang)
            ->get();

        foreach ($resumeRecords as $resume) {
            $resume->setuju_kaprodi = $request->approve;
            $resume->save();
        }

        $allKaprodiApproved = $resumeRecords->every(fn($r) => $r->setuju_kaprodi);
        $allWadirApproved = $resumeRecords->every(fn($r) => $r->setuju_wadir);

        $statusBerita = ($allKaprodiApproved && $allWadirApproved) ? 1 : 0;

        $pengajuan = PengajuanRekapBerita::where('matkuls_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->where('pertemuan', $pertemuan)
            ->where('jadwal_id', $jadwal_id)
            ->first();

        if ($pengajuan) {
            $pengajuan->update(['status' => $statusBerita]);

            if ($statusBerita) {
                $dosen = Dosen::find($resumeRecords->first()->dosens_id);
                if ($dosen) $dosen->notify(new PengajuanResumeNotification($pengajuan));
            }
        }

        return redirect()->back()->with('success', 'Status persetujuan berita acara berhasil diperbarui');
    }

    public function kontrakDetail($jadwal_id, $matkul_id, $kelas_id)
    {
        $kelas = Kelas::with('prodi')->findOrFail($kelas_id);
        if (!in_array($kelas->id_prodi, session('user.prodiIds', []))) {
            abort(403, 'Unauthorized action.');
        }

        $kontraks = Kontrak::with([
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.semester' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'jadwal.dosen' => fn($q) => $q->withTrashed()
        ])
            ->where('matkuls_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->where('jadwals_id', $jadwal_id)
            ->get();

        $kaprodi = Kaprodi::where('prodis_id', $kelas->id_prodi)->first();
        $wadir = Wadir::where('no', 1)->first();

        return Inertia::render('Kaprodi/Approval/Kontrak/Detail', [
            'kontraks' => $kontraks,
            'kaprodi' => $kaprodi,
            'wadir' => $wadir,
            'params' => [
                'jadwal_id' => $jadwal_id,
                'matkul_id' => $matkul_id,
                'kelas_id' => $kelas_id
            ]
        ]);
    }

    public function kontrakApprove(Request $request, $jadwal_id, $matkul_id, $kelas_id)
    {
        $kelas = Kelas::findOrFail($kelas_id);
        if (!in_array($kelas->id_prodi, session('user.prodiIds', []))) {
            abort(403, 'Unauthorized action.');
        }

        $kontraks = Kontrak::where('jadwals_id', $jadwal_id)
            ->where('matkuls_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->get();

        foreach ($kontraks as $kontrak) {
            $kontrak->setuju_kaprodi = $request->approve;
            $kontrak->save();
        }

        $allKaprodiApproved = $kontraks->every(fn($k) => $k->setuju_kaprodi);
        $allWadirApproved = $kontraks->every(fn($k) => $k->setuju_wadir);

        $statusKontrak = ($allKaprodiApproved && $allWadirApproved) ? 1 : 0;

        foreach ($kontraks as $kontrak) {
            $kontrak->update(['status' => $statusKontrak]);
        }

        $pengajuan = PengajuanRekapkontrak::where('jadwal_id', $jadwal_id)
            ->where('matkul_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->first();

        if ($pengajuan) {
            $pengajuan->update(['status' => $statusKontrak]);

            if ($statusKontrak) {
                $dosen = Dosen::find($kontraks->first()->jadwal->dosens_id);
                if ($dosen) $dosen->notify(new PengajuanKontrakNotification($pengajuan));
            }
        }

        return redirect()->back()->with('success', 'Status persetujuan kontrak kuliah berhasil diperbarui');
    }

    public function switchProdi(Request $request)
    {
        $request->validate(['prodi_id' => 'required|exists:prodi,id']);
        $prodiIds = session('user.prodiIds', []);
        if (in_array($request->prodi_id, $prodiIds)) {
            session(['user.activeProdiId' => $request->prodi_id]);
            session(['user.prodiId' => $request->prodi_id]); // update fallback V1/legacy
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
    }
}
