<?php

namespace App\Http\Controllers\V2\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kaprodi;
use App\Models\TahunAkademik;
use App\Models\Absen;
use App\Models\Resume;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DataPerkuliahanController extends Controller
{
    /**
     * Menampilkan daftar dosen yang mengajar di Prodi Kaprodi pada tahun akademik aktif.
     */
    public function index()
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiId = session('user.activeProdiId');
        
        // Ambil Tahun Akademik Aktif
        $tahunAkademikActive = TahunAkademik::where('status', 1)->first();
        $tahun = $tahunAkademikActive ? $tahunAkademikActive->tahun_akademik : null;

        $dosens = Dosen::where('status', 1)
            ->whereHas('jadwal', function ($query) use ($prodiId, $tahun) {
                $query->where('tahun', $tahun)
                      ->whereHas('kelas', function ($q) use ($prodiId) {
                          $q->where('id_prodi', $prodiId);
                      });
            })
            ->withCount(['jadwal as total_matkul' => function ($query) use ($prodiId, $tahun) {
                $query->where('tahun', $tahun)
                      ->whereHas('kelas', function ($q) use ($prodiId) {
                          $q->where('id_prodi', $prodiId);
                      });
            }])
            ->get()
            ->map(function ($dosen) {
                return [
                    'id' => $dosen->id,
                    'nama' => $dosen->nama,
                    'total_matkul' => $dosen->total_matkul,
                ];
            });

        return Inertia::render('Kaprodi/DataPerkuliahan/Index', [
            'dosens' => $dosens,
            'tahunAkademik' => $tahun
        ]);
    }

    /**
     * Menampilkan detail jadwal dosen tertentu pada prodi kaprodi.
     */
    public function show(string $id)
    {
        $user = Auth::guard('kaprodi')->user();
        $prodiIds = session('user.prodiIds', []);
        $prodiId = session('user.activeProdiId');

        // Authorization check: Make sure this dosen teaches in one of the prodis this Kaprodi manages
        $hasAccess = Dosen::where('id', $id)
            ->whereHas('jadwal.kelas', function ($q) use ($prodiIds) {
                $q->whereIn('id_prodi', $prodiIds);
            })
            ->exists();
        if (!$hasAccess) {
            abort(403, 'Unauthorized action.');
        }
        
        $tahunAkademikActive = TahunAkademik::where('status', 1)->first();
        $tahun = $tahunAkademikActive ? $tahunAkademikActive->tahun_akademik : null;

        $dosen = Dosen::findOrFail($id);

        $jadwals = Jadwal::with([
            'matkul',
            'matkul.prodi',
            'matkul.semester',
            'kelas.prodi'
        ])
            ->where('dosens_id', $id)
            ->where('tahun', $tahun)
            ->whereHas('kelas', function ($q) use ($prodiId) {
                $q->where('id_prodi', $prodiId);
            })
            ->withMax('absen as pertemuan_max', 'pertemuan')
            ->withMax('resume as berita_max', 'pertemuan')
            ->withMax('kontrak as kontrak_max', 'pertemuan')
            ->orderBy(function ($query) {
                $query->select('nama_matkul')
                    ->from('matkuls')
                    ->whereColumn('matkuls.id', 'jadwals.matkuls_id');
            }, 'asc')
            ->get();

        $formattedJadwals = $jadwals->map(function ($jadwal) use ($user) {
            
            // Fix N+1 check for messages
            $receiverType = 'App\Models\Kaprodi';
            $dosenType = 'App\Models\Dosen';

            $hasMessage = \App\Models\Message::where('jadwal_id', $jadwal->id)
                ->whereNull('parent_id')
                ->where(function ($query) use ($user, $jadwal, $receiverType, $dosenType) {
                    $query->where(function ($subQuery) use ($user, $jadwal, $receiverType, $dosenType) {
                        $subQuery->where('sender_id', $user->id)
                                 ->where('sender_type', $receiverType)
                                 ->where('receiver_id', $jadwal->dosens_id)
                                 ->where('receiver_type', $dosenType);
                    })->orWhere(function ($subQuery) use ($user, $jadwal, $receiverType, $dosenType) {
                        $subQuery->where('receiver_id', $user->id)
                                 ->where('receiver_type', $receiverType)
                                 ->where('sender_id', $jadwal->dosens_id)
                                 ->where('sender_type', $dosenType);
                    });
                })
                ->exists();

            return [
                'id' => $jadwal->id,
                'kode' => $jadwal->matkul->kode ?? '-',
                'nama_matkul' => $jadwal->matkul->nama_matkul ?? '-',
                'sks' => ($jadwal->matkul->praktek ?? 0) + ($jadwal->matkul->teori ?? 0),
                'prodi' => $jadwal->kelas->prodi->nama_prodi ?? '-',
                'semester' => $jadwal->matkul->semester->semester ?? '-',
                'pertemuan_max' => $jadwal->pertemuan_max ?? 0,
                'berita_max' => $jadwal->berita_max ?? 0,
                'kontrak_max' => $jadwal->kontrak_max ?? 0,
                'matkuls_id' => $jadwal->matkuls_id,
                'kelas_id' => $jadwal->kelas_id,
                'dosens_id' => $jadwal->dosens_id,
                'has_message' => $hasMessage
            ];
        });

        return Inertia::render('Kaprodi/DataPerkuliahan/Show', [
            'dosen' => [
                'id' => $dosen->id,
                'nama' => $dosen->nama,
            ],
            'jadwals' => $formattedJadwals,
            'tahunAkademik' => $tahun
        ]);
    }

    /**
     * Mencetak presensi mahasiswa berdasarkan rentang pertemuan (V1 Duplicate for V2).
     */
    public function cetakPresensi($matkul_id, $kelas_id, $jadwal_id, $rentang)
    {
        $pertemuan = $rentang === '1-7' ? [1, 2, 3, 4, 5, 6, 7] : [8, 9, 10, 11, 12, 13, 14];

        $absens = Absen::with([
            'dosen' => function ($query) {
                $query->withTrashed();
            },
            'kelas.semester' => function ($query) {
                $query->withTrashed();
            },
            'kelas' => function ($query) {
                $query->withTrashed();
            },
            'matkul' => function ($query) {
                $query->withTrashed();
            },
            'prodi' => function ($query) {
                $query->withTrashed();
            },
            'mahasiswa' => function ($query) {
                $query->withTrashed();
            },
            'jadwal' => function ($query) {
                $query->withTrashed();
            }
        ])
            ->where('jadwals_id', $jadwal_id)
            ->where('matkuls_id', $matkul_id)
            ->where('kelas_id', $kelas_id)
            ->whereIn('pertemuan', $pertemuan)
            ->get();

        if ($absens->isEmpty()) {
            abort(404, 'Data presensi tidak ditemukan.');
        }

        if (!in_array($absens->first()->prodis_id, session('user.prodiIds', []))) {
            abort(403, 'Unauthorized action.');
        }

        $tahunAkademik = TahunAkademik::where('status', 1)->get();
        $dosenPengampu = Dosen::where('id', $absens->first()->dosens_id)->first();
        $kaprodi = Kaprodi::whereHas('prodis', function ($q) use ($absens) {
            $q->where('id', $absens->first()->prodis_id);
        })->first();

        $viewName = $rentang === '1-7' 
            ? 'pages.v2.kaprodi.data-perkuliahan.cek1to7' 
            : 'pages.v2.kaprodi.data-perkuliahan.cek8to14';

        return view($viewName, compact('absens', 'tahunAkademik', 'dosenPengampu', 'kaprodi'));
    }

    /**
     * Mencetak Berita Acara Perkuliahan (BAP) berdasarkan rentang pertemuan (V1 Duplicate for V2).
     */
    public function cetakBap($matkuls_id, $kelas_id, $jadwal_id, $rentang)
    {
        $kelas = Kelas::findOrFail($kelas_id);
        if (!in_array($kelas->id_prodi, session('user.prodiIds', []))) {
            abort(403, 'Unauthorized action.');
        }

        $pertemuanRange = $rentang === '1-7' ? [1, 7] : [8, 14];

        $beritas = Resume::with([
            'dosen' => function ($query) {
                $query->withTrashed();
            },
            'matkul' => function ($query) {
                $query->withTrashed();
            },
            'kelas.prodi' => function ($query) {
                $query->withTrashed();
            },
            'kelas' => function ($query) {
                $query->withTrashed();
            },
            'kelas.semester' => function ($query) {
                $query->withTrashed();
            },
            'jadwal' => function ($query) {
                $query->withTrashed();
            }
        ])
            ->where('matkuls_id', $matkuls_id)
            ->where('kelas_id', $kelas_id)
            ->where('jadwals_id', $jadwal_id)
            ->whereBetween('pertemuan', $pertemuanRange)
            ->get();

        $semester = Semester::where('status', 1)->first();
        $sem = "GANJIL";
        if ($semester) {
            $sem = ($semester->semester % 2 == 0) ? "GENAP" : "GANJIL";
        }
        $tahunAkademik = TahunAkademik::where('status', 1)->get();

        $viewName = $rentang === '1-7' 
            ? 'pages.v2.kaprodi.data-perkuliahan.bap-cek1to7' 
            : 'pages.v2.kaprodi.data-perkuliahan.bap-cek8to14';

        return view($viewName, compact('beritas', 'tahunAkademik', 'sem'));
    }
}
