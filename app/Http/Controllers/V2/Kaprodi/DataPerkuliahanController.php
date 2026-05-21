<?php

namespace App\Http\Controllers\V2\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kaprodi;
use App\Models\TahunAkademik;
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
        $kaprodi = Kaprodi::findOrFail($user->id);
        
        // Ambil Tahun Akademik Aktif
        $tahunAkademikActive = TahunAkademik::where('status', 1)->first();
        $tahun = $tahunAkademikActive ? $tahunAkademikActive->tahun_akademik : null;

        $dosens = Dosen::where('status', 1)
            ->whereHas('jadwal', function ($query) use ($kaprodi, $tahun) {
                $query->where('tahun', $tahun)
                      ->whereHas('kelas', function ($q) use ($kaprodi) {
                          $q->where('id_prodi', $kaprodi->prodis_id);
                      });
            })
            ->withCount(['jadwal as total_matkul' => function ($query) use ($kaprodi, $tahun) {
                $query->where('tahun', $tahun)
                      ->whereHas('kelas', function ($q) use ($kaprodi) {
                          $q->where('id_prodi', $kaprodi->prodis_id);
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
        $kaprodi = Kaprodi::findOrFail($user->id);
        
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
            ->whereHas('kelas', function ($q) use ($kaprodi) {
                $q->where('id_prodi', $kaprodi->prodis_id);
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
            
            // Fix N+1 check for messages (Optional addition based on Admin's feature, 
            // but we can optimize it if we want to include it. Since the user asked for 
            // the same feature as Admin, I'll include the optimized version here)
            
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
}
