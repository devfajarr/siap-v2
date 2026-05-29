<?php

namespace App\Http\Controllers\V2\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Aktif;
use App\Models\Etika;
use App\Models\Jadwal;
use App\Models\Kaprodi;
use App\Models\Mahasiswa;
use App\Models\PengajuanRekapNilai;
use App\Models\TahunAkademik;
use App\Models\Tugas;
use App\Models\Uas;
use App\Models\Uts;
use App\Models\Wadir;
use App\Notifications\PengajuanNilaiNotification;
use App\Services\NilaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NilaiController extends Controller
{
    /**
     * Tampilkan daftar semua jadwal mengajar dosen — Index Nilai V2.
     * Pattern: sama dengan Presensi V2 (card grid, langsung tampil semua jadwal).
     */
    public function index()
    {
        $dosen = Auth::guard('dosen')->user();

        $jadwals = Jadwal::with([
            'matkul' => fn ($q) => $q->withTrashed(),
            'kelas' => fn ($q) => $q->withTrashed(),
            'kelas.prodi' => fn ($q) => $q->withTrashed(),
        ])
            ->where('dosens_id', $dosen->id)
            ->latest()
            ->get();

        // Bulk-load status kelengkapan nilai: cek keberadaan data per komponen
        // Menggunakan groupBy + selectRaw untuk menghindari N+1 query
        $jadwalIds = $jadwals->pluck('id');

        $tugasExists = Tugas::whereIn('jadwal_id', $jadwalIds)->pluck('jadwal_id')->unique();
        $utsExists = Uts::whereIn('jadwal_id', $jadwalIds)->pluck('jadwal_id')->unique();
        $uasExists = Uas::whereIn('jadwal_id', $jadwalIds)->pluck('jadwal_id')->unique();
        $etikaExists = Etika::whereIn('jadwal_id', $jadwalIds)->pluck('jadwal_id')->unique();
        $aktifExists = Aktif::whereIn('jadwal_id', $jadwalIds)->pluck('jadwal_id')->unique();

        // Status pengajuan verifikasi per jadwal
        $pengajuanStatuses = PengajuanRekapNilai::whereIn('jadwal_id', $jadwalIds)
            ->get()
            ->keyBy('jadwal_id');

        $formattedJadwals = $jadwals->map(function ($jadwal) use (
            $tugasExists,
            $utsExists,
            $uasExists,
            $etikaExists,
            $aktifExists,
            $pengajuanStatuses
        ) {
            $hasTugas = $tugasExists->contains($jadwal->id);
            $hasUts = $utsExists->contains($jadwal->id);
            $hasUas = $uasExists->contains($jadwal->id);
            $hasEtika = $etikaExists->contains($jadwal->id);
            $hasAktif = $aktifExists->contains($jadwal->id);

            $completedComponents = collect([$hasTugas, $hasUts, $hasUas, $hasEtika, $hasAktif])
                ->filter()
                ->count();

            $pengajuan = $pengajuanStatuses->get($jadwal->id);

            // Tentukan status nilai keseluruhan
            $statusNilai = match (true) {
                $pengajuan && $pengajuan->status == 1 => 'disetujui',
                $pengajuan && $pengajuan->status == 0 => 'diajukan',
                $completedComponents === 5 => 'lengkap',
                $completedComponents > 0 => 'sebagian',
                default => 'belum',
            };

            return [
                'id' => $jadwal->id,
                'kelas_id' => $jadwal->kelas_id,
                'matkul_id' => $jadwal->matkuls_id,
                'matkul' => $jadwal->matkul?->nama_matkul ?? '-',
                'sks' => ($jadwal->matkul?->teori ?? 0) + ($jadwal->matkul?->praktek ?? 0),
                'kelas' => $jadwal->kelas?->nama_kelas ?? '-',
                'prodi' => $jadwal->kelas?->prodi?->nama_prodi ?? '-',
                'hari' => $jadwal->hari,
                'completion' => [
                    'tugas' => $hasTugas,
                    'uts' => $hasUts,
                    'uas' => $hasUas,
                    'etika' => $hasEtika,
                    'aktif' => $hasAktif,
                    'total' => $completedComponents,
                ],
                'status_nilai' => $statusNilai,
                'pengajuan_id' => $pengajuan?->id,
            ];
        });

        return Inertia::render('Dosen/Nilai/Index', [
            'jadwals' => $formattedJadwals,
        ]);
    }

    /**
     * Detail nilai satu mata kuliah — kirim semua data nilai sekaligus via Inertia props.
     * Menggantikan 6 halaman terpisah di legacy (tugas, uts, uas, etika, aktif, rekap).
     */
    public function show(int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();

        // Verifikasi kepemilikan jadwal — dosen tidak bisa akses data jadwal milik dosen lain
        $jadwal = Jadwal::with([
            'matkul' => fn ($q) => $q->withTrashed(),
            'kelas' => fn ($q) => $q->withTrashed(),
            'kelas.prodi' => fn ($q) => $q->withTrashed(),
            'kelas.semester' => fn ($q) => $q->withTrashed(),
            'dosen' => fn ($q) => $q->withTrashed(),
        ])
            ->where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $kelas_id = $jadwal->kelas_id;
        $matkul_id = $jadwal->matkuls_id;

        // Ambil semua data nilai sekaligus via NilaiService (bulk-load, no N+1)
        $rekapData = NilaiService::getRekapData($kelas_id, $matkul_id, $jadwal_id);

        // Daftar mahasiswa aktif KRS di kelas ini (Bypassed untuk testing)
        $mahasiswaAktif = Mahasiswa::where('kelas_id', $kelas_id)
            // ->where('status_krs', 1)
            ->orderBy('nim', 'asc')
            ->get(['id', 'nim', 'nama_lengkap']);

        // Status pengajuan verifikasi
        $pengajuan = PengajuanRekapNilai::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->first();

        // Cek kelengkapan komponen nilai (dari data yang sudah di-load, tanpa extra query)
        $completionStatus = [
            'tugas' => $rekapData['tugass']->count() > 0,
            'uts' => $rekapData['utss']->count() > 0,
            'uas' => $rekapData['uass']->count() > 0,
            'etika' => $rekapData['etikas']->count() > 0,
            'aktif' => $rekapData['aktifs']->count() > 0,
        ];
        $completionStatus['semua_lengkap'] = ! in_array(false, $completionStatus, true);

        // Hitung nilai akhir setiap mahasiswa dari data yang sudah di-load (zero extra DB query)
        $nilaiAkhir = $rekapData['mahasiswas']->mapWithKeys(function ($mhs) use ($rekapData) {
            $total = NilaiService::calculateTotalNilai(
                mahasiswaId: $mhs->id,
                groupedTugas: $rekapData['groupedTugas'],
                utss: $rekapData['utss'],
                uass: $rekapData['uass'],
                etikas: $rekapData['etikas'],
                aktifs: $rekapData['aktifs'],
                dataAbsensi: $rekapData['dataAbsensi'],
            );

            return [
                $mhs->id => [
                    'total' => round($total, 2),
                    'huruf' => NilaiService::getNilaiHuruf($total),
                ],
            ];
        });

        // Dosen info untuk rekap cetak
        $kaprodi = null;
        if ($jadwal->kelas?->prodi) {
            $kaprodi = Kaprodi::where('prodis_id', $jadwal->kelas->prodi->id)->first();
        }
        $wadir = Wadir::where('no', 1)->first();

        // Format data tugas per mahasiswa untuk frontend
        $tugasGrouped = $rekapData['tugass']->groupBy('mahasiswa_id')->map(
            fn ($items) => $items->sortBy('tugas_ke')->values()->map(
                fn ($t) => ['tugas_ke' => $t->tugas_ke, 'nilai' => $t->nilai, 'id' => $t->id]
            )
        );

        return Inertia::render('Dosen/Nilai/Show', [
            'jadwal' => [
                'id' => $jadwal->id,
                'matkul_id' => $matkul_id,
                'kelas_id' => $kelas_id,
                'matkul' => $jadwal->matkul?->nama_matkul ?? '-',
                'kelas' => $jadwal->kelas?->nama_kelas ?? '-',
                'prodi' => $jadwal->kelas?->prodi?->nama_prodi ?? '-',
                'semester' => $jadwal->kelas?->semester?->nama_semester ?? '-',
                'dosen' => $jadwal->dosen?->nama ?? '-',
                'hari' => $jadwal->hari,
                'sks' => ($jadwal->matkul?->teori ?? 0) + ($jadwal->matkul?->praktek ?? 0),
            ],
            'mahasiswas' => $rekapData['mahasiswas']->map(fn ($m) => [
                'id' => $m->id,
                'nim' => $m->nim,
                'nama_lengkap' => $m->nama_lengkap,
            ]),
            'mahasiswaAktif' => $mahasiswaAktif->map(fn ($m) => [
                'id' => $m->id,
                'nim' => $m->nim,
                'nama_lengkap' => $m->nama_lengkap,
            ]),
            'tugasGrouped' => $tugasGrouped,
            'jumlahTugas' => $rekapData['jumlahTugas'],
            'utss' => $rekapData['utss']->map(fn ($u) => ['nilai' => $u->nilai, 'id' => $u->id]),
            'uass' => $rekapData['uass']->map(fn ($u) => ['nilai' => $u->nilai, 'id' => $u->id]),
            'etikas' => $rekapData['etikas']->map(fn ($e) => ['nilai' => $e->nilai, 'id' => $e->id]),
            'aktifs' => $rekapData['aktifs']->map(fn ($a) => ['nilai' => $a->nilai, 'id' => $a->id]),
            'dataAbsensi' => $rekapData['dataAbsensi'],
            'totalPertemuan' => $rekapData['totalPertemuan'],
            'nilaiAkhir' => $nilaiAkhir,
            'completionStatus' => $completionStatus,
            'pengajuan' => $pengajuan ? [
                'id' => $pengajuan->id,
                'status' => $pengajuan->status,
                'tahun' => $pengajuan->tahun,
            ] : null,
            'kaprodi' => $kaprodi ? ['nama' => $kaprodi->nama] : null,
            'wadir' => $wadir ? ['nama' => $wadir->nama, 'nip' => $wadir->nip ?? null] : null,
        ]);
    }

    // =========================================================================
    // TUGAS
    // =========================================================================

    public function storeTugas(Request $request, int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $request->validate([
            'tugas_ke' => 'required|integer|min:1',
            'mahasiswas_id' => 'required|array|min:1',
            'mahasiswas_id.*' => 'exists:mahasiswas,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $rows = [];
        $now = now();
        foreach ($request->mahasiswas_id as $index => $mhsId) {
            $rows[] = [
                'mahasiswa_id' => $mhsId,
                'matkul_id' => $jadwal->matkuls_id,
                'kelas_id' => $jadwal->kelas_id,
                'jadwal_id' => $jadwal_id,
                'tugas_ke' => $request->tugas_ke,
                'nilai' => $request->nilai[$index] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Tugas::insert($rows);

        return back()->with('success', 'Nilai tugas ke-'.$request->tugas_ke.' berhasil disimpan.');
    }

    public function updateTugas(Request $request, int $jadwal_id, int $tugas_ke)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $request->validate([
            'mahasiswas_id' => 'required|array|min:1',
            'mahasiswas_id.*' => 'exists:mahasiswas,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->mahasiswas_id as $index => $mhsId) {
                Tugas::updateOrCreate(
                    [
                        'mahasiswa_id' => $mhsId,
                        'kelas_id' => $jadwal->kelas_id,
                        'jadwal_id' => $jadwal_id,
                        'matkul_id' => $jadwal->matkuls_id,
                        'tugas_ke' => $tugas_ke,
                    ],
                    ['nilai' => $request->nilai[$index] ?? 0]
                );
            }
            DB::commit();

            return back()->with('success', 'Nilai tugas ke-'.$tugas_ke.' berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui nilai tugas.');
        }
    }

    public function destroyTugas(int $jadwal_id, int $tugas_ke)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        Tugas::where('kelas_id', $jadwal->kelas_id)
            ->where('matkul_id', $jadwal->matkuls_id)
            ->where('jadwal_id', $jadwal_id)
            ->where('tugas_ke', $tugas_ke)
            ->delete();

        return back()->with('success', 'Nilai tugas ke-'.$tugas_ke.' berhasil dihapus.');
    }

    // =========================================================================
    // UTS
    // =========================================================================

    public function storeUts(Request $request, int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $request->validate([
            'mahasiswas_id' => 'required|array|min:1',
            'mahasiswas_id.*' => 'exists:mahasiswas,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $rows = [];
        $now = now();
        foreach ($request->mahasiswas_id as $index => $mhsId) {
            $rows[] = [
                'mahasiswa_id' => $mhsId,
                'matkul_id' => $jadwal->matkuls_id,
                'kelas_id' => $jadwal->kelas_id,
                'jadwal_id' => $jadwal_id,
                'nilai' => $request->nilai[$index] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Uts::insert($rows);

        return back()->with('success', 'Nilai UTS berhasil disimpan.');
    }

    public function updateUts(Request $request, int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $request->validate([
            'mahasiswas_id' => 'required|array|min:1',
            'mahasiswas_id.*' => 'exists:mahasiswas,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->mahasiswas_id as $index => $mhsId) {
                Uts::updateOrCreate(
                    [
                        'mahasiswa_id' => $mhsId,
                        'kelas_id' => $jadwal->kelas_id,
                        'jadwal_id' => $jadwal_id,
                        'matkul_id' => $jadwal->matkuls_id,
                    ],
                    ['nilai' => $request->nilai[$index] ?? 0]
                );
            }
            DB::commit();

            return back()->with('success', 'Nilai UTS berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui nilai UTS.');
        }
    }

    // =========================================================================
    // UAS
    // =========================================================================

    public function storeUas(Request $request, int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $request->validate([
            'mahasiswas_id' => 'required|array|min:1',
            'mahasiswas_id.*' => 'exists:mahasiswas,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $rows = [];
        $now = now();
        foreach ($request->mahasiswas_id as $index => $mhsId) {
            $rows[] = [
                'mahasiswa_id' => $mhsId,
                'matkul_id' => $jadwal->matkuls_id,
                'kelas_id' => $jadwal->kelas_id,
                'jadwal_id' => $jadwal_id,
                'nilai' => $request->nilai[$index] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Uas::insert($rows);

        return back()->with('success', 'Nilai UAS berhasil disimpan.');
    }

    public function updateUas(Request $request, int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $request->validate([
            'mahasiswas_id' => 'required|array|min:1',
            'mahasiswas_id.*' => 'exists:mahasiswas,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->mahasiswas_id as $index => $mhsId) {
                Uas::updateOrCreate(
                    [
                        'mahasiswa_id' => $mhsId,
                        'kelas_id' => $jadwal->kelas_id,
                        'jadwal_id' => $jadwal_id,
                        'matkul_id' => $jadwal->matkuls_id,
                    ],
                    ['nilai' => $request->nilai[$index] ?? 0]
                );
            }
            DB::commit();

            return back()->with('success', 'Nilai UAS berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui nilai UAS.');
        }
    }

    // =========================================================================
    // ETIKA
    // =========================================================================

    public function storeEtika(Request $request, int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $request->validate([
            'mahasiswas_id' => 'required|array|min:1',
            'mahasiswas_id.*' => 'exists:mahasiswas,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $rows = [];
        $now = now();
        foreach ($request->mahasiswas_id as $index => $mhsId) {
            $rows[] = [
                'mahasiswa_id' => $mhsId,
                'matkul_id' => $jadwal->matkuls_id,
                'kelas_id' => $jadwal->kelas_id,
                'jadwal_id' => $jadwal_id,
                'nilai' => $request->nilai[$index] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Etika::insert($rows);

        return back()->with('success', 'Nilai etika berhasil disimpan.');
    }

    public function updateEtika(Request $request, int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $request->validate([
            'mahasiswas_id' => 'required|array|min:1',
            'mahasiswas_id.*' => 'exists:mahasiswas,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->mahasiswas_id as $index => $mhsId) {
                Etika::updateOrCreate(
                    [
                        'mahasiswa_id' => $mhsId,
                        'kelas_id' => $jadwal->kelas_id,
                        'jadwal_id' => $jadwal_id,
                        'matkul_id' => $jadwal->matkuls_id,
                    ],
                    ['nilai' => $request->nilai[$index] ?? 0]
                );
            }
            DB::commit();

            return back()->with('success', 'Nilai etika berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui nilai etika.');
        }
    }

    // =========================================================================
    // KEAKTIFAN
    // =========================================================================

    public function storeAktif(Request $request, int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $request->validate([
            'mahasiswas_id' => 'required|array|min:1',
            'mahasiswas_id.*' => 'exists:mahasiswas,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $rows = [];
        $now = now();
        foreach ($request->mahasiswas_id as $index => $mhsId) {
            $rows[] = [
                'mahasiswa_id' => $mhsId,
                'matkul_id' => $jadwal->matkuls_id,
                'kelas_id' => $jadwal->kelas_id,
                'jadwal_id' => $jadwal_id,
                'nilai' => $request->nilai[$index] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Aktif::insert($rows);

        return back()->with('success', 'Nilai keaktifan berhasil disimpan.');
    }

    public function updateAktif(Request $request, int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $request->validate([
            'mahasiswas_id' => 'required|array|min:1',
            'mahasiswas_id.*' => 'exists:mahasiswas,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->mahasiswas_id as $index => $mhsId) {
                Aktif::updateOrCreate(
                    [
                        'mahasiswa_id' => $mhsId,
                        'kelas_id' => $jadwal->kelas_id,
                        'jadwal_id' => $jadwal_id,
                        'matkul_id' => $jadwal->matkuls_id,
                    ],
                    ['nilai' => $request->nilai[$index] ?? 0]
                );
            }
            DB::commit();

            return back()->with('success', 'Nilai keaktifan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui nilai keaktifan.');
        }
    }

    // =========================================================================
    // REKAP & PENGAJUAN VERIFIKASI
    // =========================================================================

    public function pengajuanRekap(Request $request, int $jadwal_id)
    {
        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $kelas_id = $jadwal->kelas_id;
        $matkul_id = $jadwal->matkuls_id;

        // Guard: cek apakah semua komponen nilai sudah terisi
        $completion = NilaiService::getCompletionStatus($kelas_id, $matkul_id, $jadwal_id);
        if (! $completion['semua_lengkap']) {
            return back()->with('error', 'Semua komponen nilai (Tugas, UTS, UAS, Etika, Keaktifan) harus diisi sebelum mengajukan verifikasi.');
        }

        // Guard: cek apakah sudah pernah diajukan
        $existing = PengajuanRekapNilai::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Rekap nilai untuk mata kuliah ini sudah pernah diajukan.');
        }

        // Guard: cek tahun akademik aktif (null-safe)
        $tahun = TahunAkademik::where('status', 1)->first();
        if (! $tahun) {
            return back()->with('error', 'Tahun akademik aktif belum ditetapkan. Hubungi admin.');
        }

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanRekapNilai::create([
                'kelas_id' => $kelas_id,
                'matkul_id' => $matkul_id,
                'jadwal_id' => $jadwal_id,
                'tahun' => $tahun->tahun_akademik,
                'status' => 0,
            ]);

            // Notifikasi ke semua admin
            $admins = Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new PengajuanNilaiNotification($pengajuan, null));
            }

            DB::commit();

            return back()->with('success', 'Pengajuan verifikasi rekap nilai berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal mengajukan verifikasi: '.$e->getMessage());
        }
    }
}
