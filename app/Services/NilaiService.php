<?php

namespace App\Services;

use App\Models\Absen;
use App\Models\Aktif;
use App\Models\Etika;
use App\Models\Mahasiswa;
use App\Models\Tugas;
use App\Models\Uas;
use App\Models\Uts;

class NilaiService
{
    /**
     * Ambil semua data nilai untuk satu jadwal sekaligus (bulk-load).
     * Menggantikan 6 query pluck() sequential yang berulang di controller legacy.
     *
     * @return array{
     *   mahasiswas: \Illuminate\Support\Collection,
     *   tugass: \Illuminate\Support\Collection,
     *   groupedTugas: \Illuminate\Support\Collection,
     *   jumlahTugas: int,
     *   utss: \Illuminate\Support\Collection,
     *   uass: \Illuminate\Support\Collection,
     *   etikas: \Illuminate\Support\Collection,
     *   aktifs: \Illuminate\Support\Collection,
     *   absens: \Illuminate\Support\Collection,
     *   totalPertemuan: int,
     *   dataAbsensi: \Illuminate\Support\Collection,
     * }
     */
    public static function getRekapData(int $kelas_id, int $matkul_id, int $jadwal_id): array
    {
        // --- Bulk-load semua data nilai dalam 7 query paralel ---
        $tugass = Tugas::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->get();

        $utss = Uts::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->get()
            ->keyBy('mahasiswa_id');

        $uass = Uas::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->get()
            ->keyBy('mahasiswa_id');

        $etikas = Etika::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->get()
            ->keyBy('mahasiswa_id');

        $aktifs = Aktif::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->get()
            ->keyBy('mahasiswa_id');

        $absens = Absen::where('kelas_id', $kelas_id)
            ->where('matkuls_id', $matkul_id)
            ->where('jadwals_id', $jadwal_id)
            ->get();

        // Total pertemuan: MAX dari data absensi yang sudah di-load (zero extra query)
        $totalPertemuan = $absens->max('pertemuan') ?? 0;

        // --- Kumpulkan semua mahasiswa_id unik dari seluruh sumber data ---
        $mahasiswaIds = collect()
            ->concat($tugass->pluck('mahasiswa_id'))
            ->concat($utss->keys())
            ->concat($uass->keys())
            ->concat($etikas->keys())
            ->concat($aktifs->keys())
            ->concat($absens->pluck('mahasiswas_id'))
            ->unique()
            ->filter();

        // Satu query untuk semua mahasiswa (Bypassed untuk testing agar semua mahasiswa di kelas muncul)
        $mahasiswas = Mahasiswa::withTrashed()
            ->where('kelas_id', $kelas_id)
            ->orderBy('nim', 'asc')
            ->get();

        // --- Olah data absensi menjadi summary per mahasiswa ---
        $dataAbsensi = $absens
            ->groupBy('mahasiswas_id')
            ->map(function ($group) use ($totalPertemuan) {
                $totalKehadiran = $group->whereIn('status', ['H', 'T'])->count();
                $persentase = $totalPertemuan > 0
                    ? ($totalKehadiran / $totalPertemuan) * 15
                    : 0;

                return [
                    'total_kehadiran'    => $totalKehadiran,
                    'persentase_kehadiran' => round($persentase, 2),
                    'absensi'            => $group,
                ];
            });

        // --- Olah data tugas ---
        $groupedTugas = $tugass->groupBy('mahasiswa_id');
        $jumlahTugas  = max(1, $tugass->pluck('tugas_ke')->unique()->count());

        return [
            'mahasiswas'     => $mahasiswas,
            'tugass'         => $tugass,
            'groupedTugas'   => $groupedTugas,
            'jumlahTugas'    => $jumlahTugas,
            'utss'           => $utss,
            'uass'           => $uass,
            'etikas'         => $etikas,
            'aktifs'         => $aktifs,
            'absens'         => $absens,
            'totalPertemuan' => $totalPertemuan,
            'dataAbsensi'    => $dataAbsensi,
        ];
    }

    /**
     * Hitung nilai akhir seorang mahasiswa dari data yang sudah di-pre-load.
     * ZERO extra DB query — semua data diambil dari parameter collection.
     *
     * Bobot: Tugas 25% | Keaktifan 5% | Etika 5% | Kehadiran 15% | UTS 25% | UAS 25%
     */
    public static function calculateTotalNilai(
        int $mahasiswaId,
        \Illuminate\Support\Collection $groupedTugas,
        \Illuminate\Support\Collection $utss,
        \Illuminate\Support\Collection $uass,
        \Illuminate\Support\Collection $etikas,
        \Illuminate\Support\Collection $aktifs,
        \Illuminate\Support\Collection $dataAbsensi
    ): float {
        // Nilai tugas: rata-rata dari semua tugas mahasiswa ini
        $tugasMhs   = $groupedTugas->get($mahasiswaId, collect());
        $nilaiTugas = $tugasMhs->count() > 0 ? $tugasMhs->avg('nilai') : 0;

        $nilaiUts  = (float) ($utss->get($mahasiswaId)?->nilai ?? 0);
        $nilaiUas  = (float) ($uass->get($mahasiswaId)?->nilai ?? 0);
        $nilaiEtika = (float) ($etikas->get($mahasiswaId)?->nilai ?? 0);
        $nilaiAktif = (float) ($aktifs->get($mahasiswaId)?->nilai ?? 0);

        $absensiMhs        = $dataAbsensi->get($mahasiswaId);
        $nilaiKehadiran    = $absensiMhs ? (float) ($absensiMhs['persentase_kehadiran'] ?? 0) : 0;

        return ($nilaiTugas  * 0.25)
             + ($nilaiAktif  * 0.05)
             + ($nilaiEtika  * 0.05)
             + ($nilaiKehadiran * 0.15)
             + ($nilaiUts    * 0.25)
             + ($nilaiUas    * 0.25);
    }

    /**
     * Cek kelengkapan komponen nilai untuk satu jadwal.
     * Mengembalikan array status per komponen dan flag `semua_lengkap`.
     *
     * @return array{tugas: bool, uts: bool, uas: bool, etika: bool, aktif: bool, semua_lengkap: bool}
     */
    public static function getCompletionStatus(int $kelas_id, int $matkul_id, int $jadwal_id): array
    {
        $hasTugas = Tugas::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->exists();

        $hasUts = Uts::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->exists();

        $hasUas = Uas::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->exists();

        $hasEtika = Etika::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->exists();

        $hasAktif = Aktif::where('kelas_id', $kelas_id)
            ->where('matkul_id', $matkul_id)
            ->where('jadwal_id', $jadwal_id)
            ->exists();

        $semua = $hasTugas && $hasUts && $hasUas && $hasEtika && $hasAktif;

        return [
            'tugas'        => $hasTugas,
            'uts'          => $hasUts,
            'uas'          => $hasUas,
            'etika'        => $hasEtika,
            'aktif'        => $hasAktif,
            'semua_lengkap' => $semua,
        ];
    }

    /**
     * Konversi nilai angka ke nilai huruf.
     */
    public static function getNilaiHuruf(float $nilai): string
    {
        return match (true) {
            $nilai >= 85 => 'A',
            $nilai >= 80 => 'A-',
            $nilai >= 75 => 'B+',
            $nilai >= 70 => 'B',
            $nilai >= 65 => 'B-',
            $nilai >= 60 => 'C+',
            $nilai >= 55 => 'C',
            $nilai >= 50 => 'C-',
            $nilai >= 40 => 'D',
            $nilai >= 0  => 'E',
            default      => '-',
        };
    }
}
