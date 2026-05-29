<?php

namespace App\Http\Controllers\V2\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\JadwalUjian;
use App\Models\Mahasiswa;
use App\Models\PengajuanCetakKartuUjian;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class JadwalUjianController extends Controller
{
    /**
     * Menampilkan daftar jadwal ujian dan form pengajuan kartu ujian.
     */
    public function index(Request $request): Response
    {
        $user = auth()->guard('mahasiswa')->user();
        $mahasiswa = Mahasiswa::with(['kelas.prodi', 'kelas.semester'])->findOrFail($user->id);
        $kelas = $mahasiswa->kelas;

        if (! $kelas || ! $kelas->id_semester) {
            return Inertia::render('Mahasiswa/JadwalUjian/Index', [
                'mahasiswa' => [
                    'nama_lengkap' => $mahasiswa->nama_lengkap,
                    'nim' => $mahasiswa->nim,
                    'prodi' => '-',
                    'semester' => '-',
                ],
                'tahun_akademik' => null,
                'uts' => [
                    'questionnaire_completed' => false,
                    'schedules' => [],
                    'pengajuan' => null,
                    'pelayanan_list' => [],
                ],
                'uas' => [
                    'questionnaire_completed' => false,
                    'schedules' => [],
                    'pengajuan' => null,
                    'kinerja_list' => [],
                ],
            ]);
        }

        $tahun = TahunAkademik::where('status', '1')->first();
        $tahunAkademikStr = $tahun ? $tahun->tahun_akademik : '';

        // UTS CONFIGURATION
        $utsQuestionnaireCompleted = $mahasiswa->hasCompletedServiceEvaluations();
        $utsPengajuan = PengajuanCetakKartuUjian::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $kelas->id_semester)
            ->where('jenis_ujian', 'uts')
            ->first();

        $utsSchedules = JadwalUjian::with(['pengawas', 'ruangan', 'matkul'])
            ->where('kelas_id', $kelas->id)
            ->where('jenis_ujian', 'uts')
            ->where('tahun', $tahunAkademikStr)
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->get()
            ->map(function ($j) {
                return [
                    'id' => $j->id,
                    'matkul' => $j->matkul->nama_matkul ?? '-',
                    'kode_matkul' => $j->matkul->kode ?? '-',
                    'pengawas' => $j->pengawas->nama ?? '-',
                    'ruangan' => $j->ruangan->nama ?? '-',
                    'tanggal' => $j->tanggal,
                    'waktu' => substr($j->waktu_mulai, 0, 5).' - '.substr($j->waktu_selesai, 0, 5),
                ];
            });

        // List published service questionnaires to fill
        $publishedPelayanan = Questionnaire::where('type', 'pelayanan')
            ->where('status', 'published')
            ->whereIn('target_respondent', ['all', 'mahasiswa'])
            ->get();

        $pelayananList = $publishedPelayanan->map(function ($q) use ($mahasiswa) {
            $isSubmitted = QuestionnaireResponse::where('questionnaire_id', $q->id)
                ->where('respondent_id', $mahasiswa->id)
                ->where('respondent_type', Mahasiswa::class)
                ->exists();

            return [
                'id' => $q->id,
                'title' => $q->title,
                'is_submitted' => $isSubmitted,
            ];
        });

        // UAS CONFIGURATION
        $uasQuestionnaireCompleted = $mahasiswa->hasCompletedAllTeacherEvaluations();
        $uasPengajuan = PengajuanCetakKartuUjian::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $kelas->id_semester)
            ->where('jenis_ujian', 'uas')
            ->first();

        $uasSchedules = JadwalUjian::with(['pengawas', 'ruangan', 'matkul'])
            ->where('kelas_id', $kelas->id)
            ->where('jenis_ujian', 'uas')
            ->where('tahun', $tahunAkademikStr)
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->get()
            ->map(function ($j) {
                return [
                    'id' => $j->id,
                    'matkul' => $j->matkul->nama_matkul ?? '-',
                    'kode_matkul' => $j->matkul->kode ?? '-',
                    'pengawas' => $j->pengawas->nama ?? '-',
                    'ruangan' => $j->ruangan->nama ?? '-',
                    'tanggal' => $j->tanggal,
                    'waktu' => substr($j->waktu_mulai, 0, 5).' - '.substr($j->waktu_selesai, 0, 5),
                ];
            });

        // List published teacher performance questionnaires and schedules to evaluate
        $publishedKinerja = Questionnaire::where('type', 'kinerja_pengajar')
            ->where('status', 'published')
            ->whereIn('target_respondent', ['all', 'mahasiswa'])
            ->get();

        $classSchedules = Jadwal::where('kelas_id', $mahasiswa->kelas_id)
            ->whereHas('kelas.semester', function ($query) {
                $query->where('status', 1);
            })
            ->with(['dosen', 'matkul'])
            ->get();

        $kinerjaList = collect();
        foreach ($publishedKinerja as $q) {
            foreach ($classSchedules as $schedule) {
                $isSubmitted = QuestionnaireResponse::where('questionnaire_id', $q->id)
                    ->where('respondent_id', $mahasiswa->id)
                    ->where('respondent_type', Mahasiswa::class)
                    ->where('dosen_id', $schedule->dosens_id)
                    ->where('jadwal_id', $schedule->id)
                    ->exists();

                $kinerjaList->push([
                    'questionnaire_id' => $q->id,
                    'dosen_id' => $schedule->dosens_id,
                    'nama_dosen' => $schedule->dosen->nama ?? '-',
                    'matkul_id' => $schedule->matkuls_id,
                    'nama_matkul' => $schedule->matkul->nama_matkul ?? '-',
                    'jadwal_id' => $schedule->id,
                    'is_submitted' => $isSubmitted,
                ]);
            }
        }

        return Inertia::render('Mahasiswa/JadwalUjian/Index', [
            'mahasiswa' => [
                'nama_lengkap' => $mahasiswa->nama_lengkap,
                'nim' => $mahasiswa->nim,
                'prodi' => $kelas->prodi->nama_prodi ?? '-',
                'semester' => $kelas->semester->nama_semester ?? '-',
            ],
            'tahun_akademik' => $tahunAkademikStr,
            'uts' => [
                'questionnaire_completed' => $utsQuestionnaireCompleted,
                'schedules' => $utsSchedules,
                'pengajuan' => $utsPengajuan ? [
                    'id' => $utsPengajuan->id,
                    'status' => (int) $utsPengajuan->status,
                    'keterangan' => $utsPengajuan->keterangan,
                    'bukti_spp' => $utsPengajuan->bukti_spp,
                    'bukti_pembayaran_ujian' => $utsPengajuan->bukti_pembayaran_ujian,
                    'bulan_spp' => $utsPengajuan->bulan_spp,
                    'tahun_spp' => $utsPengajuan->tahun_spp,
                ] : null,
                'pelayanan_list' => $pelayananList,
            ],
            'uas' => [
                'questionnaire_completed' => $uasQuestionnaireCompleted,
                'schedules' => $uasSchedules,
                'pengajuan' => $uasPengajuan ? [
                    'id' => $uasPengajuan->id,
                    'status' => (int) $uasPengajuan->status,
                    'keterangan' => $uasPengajuan->keterangan,
                    'bukti_spp' => $uasPengajuan->bukti_spp,
                    'bukti_pembayaran_ujian' => $uasPengajuan->bukti_pembayaran_ujian,
                    'bulan_spp' => $uasPengajuan->bulan_spp,
                    'tahun_spp' => $uasPengajuan->tahun_spp,
                ] : null,
                'kinerja_list' => $kinerjaList,
            ],
        ]);
    }

    /**
     * Memproses pengajuan kartu ujian UTS/UAS oleh mahasiswa.
     */
    public function ajukan(Request $request): RedirectResponse
    {
        $request->validate([
            'jenis_ujian' => 'required|in:uts,uas',
            'bulan_spp' => 'required|string',
            'tahun_spp' => 'required|integer',
            'bukti_spp' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'bukti_pembayaran_ujian' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'bukti_spp.required' => 'Bukti pembayaran SPP wajib diunggah.',
            'bukti_spp.image' => 'Bukti pembayaran SPP harus berupa gambar.',
            'bukti_spp.max' => 'Ukuran bukti pembayaran SPP maksimal 5MB.',
            'bukti_pembayaran_ujian.required' => 'Bukti pembayaran ujian wajib diunggah.',
            'bukti_pembayaran_ujian.image' => 'Bukti pembayaran ujian harus berupa gambar.',
            'bukti_pembayaran_ujian.max' => 'Ukuran bukti pembayaran ujian maksimal 5MB.',
        ]);

        $user = auth()->guard('mahasiswa')->user();
        $mahasiswa = Mahasiswa::with('kelas')->findOrFail($user->id);
        $kelas = $mahasiswa->kelas;

        if (! $kelas || ! $kelas->id_semester) {
            return redirect()->back()->with('error', 'Data kelas atau semester aktif tidak ditemukan.');
        }

        // Verifikasi Kuisioner
        if ($request->jenis_ujian === 'uts') {
            if (! $mahasiswa->hasCompletedServiceEvaluations()) {
                return redirect()->back()->with('error', 'Silakan selesaikan Kuisioner Pelayanan terlebih dahulu.');
            }
        } else {
            if (! $mahasiswa->hasCompletedAllTeacherEvaluations()) {
                return redirect()->back()->with('error', 'Silakan selesaikan Kuisioner Pengajar untuk seluruh dosen terlebih dahulu.');
            }
        }

        $existing = PengajuanCetakKartuUjian::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $kelas->id_semester)
            ->where('jenis_ujian', $request->jenis_ujian)
            ->first();

        // Upload bukti pembayaran
        $buktiSppPath = $request->file('bukti_spp')->store('bukti_kartu_ujian', 'public');
        $buktiUjianPath = $request->file('bukti_pembayaran_ujian')->store('bukti_kartu_ujian', 'public');

        if ($existing) {
            if ($existing->bukti_spp && Storage::disk('public')->exists($existing->bukti_spp)) {
                Storage::disk('public')->delete($existing->bukti_spp);
            }
            if ($existing->bukti_pembayaran_ujian && Storage::disk('public')->exists($existing->bukti_pembayaran_ujian)) {
                Storage::disk('public')->delete($existing->bukti_pembayaran_ujian);
            }
        }

        PengajuanCetakKartuUjian::updateOrCreate([
            'mahasiswa_id' => $mahasiswa->id,
            'semester_id' => $kelas->id_semester,
            'jenis_ujian' => $request->jenis_ujian,
        ], [
            'bukti_spp' => $buktiSppPath,
            'bukti_pembayaran_ujian' => $buktiUjianPath,
            'bulan_spp' => $request->bulan_spp,
            'tahun_spp' => $request->tahun_spp,
            'status' => 0, // Reset ke Pending
            'keterangan' => null,
        ]);

        $jenisUjianUpper = strtoupper($request->jenis_ujian);

        return redirect()->back()->with('success', "Permohonan kartu ujian {$jenisUjianUpper} berhasil diajukan dan sedang menunggu verifikasi.");
    }
}
