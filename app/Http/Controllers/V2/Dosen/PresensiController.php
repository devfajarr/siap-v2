<?php

namespace App\Http\Controllers\V2\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\RequestEditPresensi;
use App\Models\Resume;
use App\Models\TahunAkademik;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PresensiController extends Controller
{
    public function index()
    {
        $dosen = Auth::guard('dosen')->user();
        
        $jadwals = Jadwal::with(['dosen', 'matkul', 'kelas.prodi', 'ruangan'])
            ->where('dosens_id', $dosen->id)
            ->latest()
            ->get();

        $today = Carbon::today()->format('Y-m-d');

        $formattedJadwals = $jadwals->map(function ($jadwal) use ($today) {
            $pertemuanMax = Absen::where('jadwals_id', $jadwal->id)->max('pertemuan') ?? 0;
            
            $absenToday = Absen::where('jadwals_id', $jadwal->id)
                ->where('kelas_id', $jadwal->kelas->id)
                ->whereDate('tanggal', $today)
                ->first();
                
            $statusPresensi = 'available'; 
            if ($pertemuanMax >= 14 && !$absenToday) {
                $statusPresensi = 'completed'; 
            } elseif ($absenToday) {
                $statusPresensi = 'filled_today'; 
            }

            // Fetch approved but not yet used edit requests
            $approvedEdits = RequestEditPresensi::where('jadwal_id', $jadwal->id)
                ->where('disetujui', true)
                ->where('status', false)
                ->get()
                ->map(function ($req) {
                    return [
                        'id' => $req->id,
                        'pertemuan' => $req->pertemuan,
                    ];
                });

            return [
                'id' => $jadwal->id,
                'matkul' => $jadwal->matkul->nama_matkul,
                'sks' => $jadwal->matkul->praktek + $jadwal->matkul->teori,
                'hari' => $jadwal->hari,
                'waktu' => Carbon::parse($jadwal->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->waktu_selesai)->format('H:i'),
                'kelas' => $jadwal->kelas->nama_kelas,
                'ruangan' => $jadwal->ruangan->nama,
                'prodi' => $jadwal->kelas->prodi->nama_prodi,
                'pertemuan_sekarang' => $pertemuanMax,
                'pertemuan_hari_ini' => $absenToday ? $absenToday->pertemuan : null,
                'status_presensi' => $statusPresensi,
                'approved_edits' => $approvedEdits,
            ];
        });

        return Inertia::render('Dosen/Presensi/Index', [
            'jadwals' => $formattedJadwals
        ]);
    }

    public function requestEdit(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwals,id',
            'pertemuan' => 'required|integer|min:1|max:14',
        ], [
            'pertemuan.required' => 'Pertemuan harus dipilih.'
        ]);

        $dosen = Auth::guard('dosen')->user();
        $jadwal = Jadwal::with(['matkul', 'kelas'])->findOrFail($request->jadwal_id);
        $nomor_akademik = Admin::pluck('no_telephone')->first();

        try {
            RequestEditPresensi::create([
                'jadwal_id' => $request->jadwal_id,
                'pertemuan' => $request->pertemuan,
                'disetujui' => false,
                'status' => false,
            ]);

            if ($nomor_akademik) {
                WhatsappService::kirim(
                    $nomor_akademik,
                    "📢 *Pengajuan Edit Presensi (V2)!*\n\n"
                        . "📌 *Dosen:* {$dosen->nama}\n"
                        . "📖 *Mata Kuliah:* {$jadwal->matkul->nama_matkul}\n"
                        . "🏫 *Kelas:* {$jadwal->kelas->nama_kelas}\n"
                        . "📅 *Pertemuan:* {$request->pertemuan}\n\n"
                        . "Mohon segera lakukan verifikasi di Dashboard Admin. ✅"
                );
            }

            return back()->with('success', 'Pengajuan edit presensi berhasil dikirim ke Admin.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim pengajuan: ' . $e->getMessage());
        }
    }

    public function create($id)
    {
        $dosen = Auth::guard('dosen')->user();
        
        $jadwal = Jadwal::with(['dosen', 'matkul', 'kelas.prodi', 'ruangan'])
            ->where('dosens_id', $dosen->id)
            ->where('id', $id)
            ->firstOrFail();
            
        $pertemuan = Absen::where('jadwals_id', $jadwal->id)->max('pertemuan');
        $pertemuan = $pertemuan ? $pertemuan + 1 : 1;
        
        $mahasiswas = Mahasiswa::where('kelas_id', $jadwal->kelas->id)
            ->where('status_krs', 1)
            ->orderBy('nama_lengkap', 'asc')
            ->get();
            
        $tahun = TahunAkademik::where('status', 1)->first();

        return Inertia::render('Dosen/Presensi/Create', [
            'jadwal' => [
                'id' => $jadwal->id,
                'matkuls_id' => $jadwal->matkul->id,
                'matkul_nama' => $jadwal->matkul->nama_matkul,
                'dosens_id' => $jadwal->dosen->id,
                'dosen_nama' => $jadwal->dosen->nama,
                'kelas_id' => $jadwal->kelas->id,
                'kelas_nama' => $jadwal->kelas->nama_kelas,
                'prodis_id' => $jadwal->kelas->prodi->id,
                'prodi_nama' => $jadwal->kelas->prodi->nama_prodi,
                'tanggal' => Carbon::now()->format('d/m/Y'),
                'tahun_akademik' => $tahun ? $tahun->tahun_akademik : '',
            ],
            'pertemuan' => $pertemuan,
            'mahasiswas' => $mahasiswas->map(function ($m) {
                return [
                    'id' => $m->id,
                    'nim' => $m->nim,
                    'nama_lengkap' => $m->nama_lengkap,
                ];
            })
        ]);
    }

    public function edit($jadwal_id, $pertemuan)
    {
        $dosen = Auth::guard('dosen')->user();
        
        $jadwal = Jadwal::with(['dosen', 'matkul', 'kelas.prodi', 'ruangan'])
            ->where('dosens_id', $dosen->id)
            ->where('id', $jadwal_id)
            ->firstOrFail();

        $absens = Absen::where('jadwals_id', $jadwal_id)
            ->where('pertemuan', $pertemuan)
            ->get()
            ->pluck('status', 'mahasiswas_id');

        $resume = Resume::where('jadwals_id', $jadwal_id)
            ->where('pertemuan', $pertemuan)
            ->first();

        $mahasiswas = Mahasiswa::where('kelas_id', $jadwal->kelas->id)
            ->where('status_krs', 1)
            ->orderBy('nama_lengkap', 'asc')
            ->get();
            
        $tahun = TahunAkademik::where('status', 1)->first();

        return Inertia::render('Dosen/Presensi/Edit', [
            'jadwal' => [
                'id' => $jadwal->id,
                'matkuls_id' => $jadwal->matkul->id,
                'matkul_nama' => $jadwal->matkul->nama_matkul,
                'dosens_id' => $jadwal->dosen->id,
                'dosen_nama' => $jadwal->dosen->nama,
                'kelas_id' => $jadwal->kelas->id,
                'kelas_nama' => $jadwal->kelas->nama_kelas,
                'prodis_id' => $jadwal->kelas->prodi->id,
                'prodi_nama' => $jadwal->kelas->prodi->nama_prodi,
                'tanggal' => $resume ? Carbon::parse($resume->tanggal)->format('d/m/Y') : Carbon::now()->format('d/m/Y'),
                'tahun_akademik' => $tahun ? $tahun->tahun_akademik : '',
            ],
            'pertemuan' => (int)$pertemuan,
            'existingStatus' => $absens,
            'existingMateri' => $resume ? $resume->materi : '',
            'mahasiswas' => $mahasiswas->map(function ($m) {
                return [
                    'id' => $m->id,
                    'nim' => $m->nim,
                    'nama_lengkap' => $m->nama_lengkap,
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pertemuan' => 'required|integer',
            'jadwals_id' => 'required|exists:jadwals,id',
            'matkuls_id' => 'required',
            'dosens_id' => 'required',
            'prodis_id' => 'required',
            'kelas_id' => 'required',
            'tahun' => 'required',
            'status' => 'required|array',
            'status.*' => 'required|in:H,T,I,S,A,C',
            'materiResume' => 'required|string',
            'jumlahHadir' => 'required|integer',
            'jumlahTidakHadir' => 'required|integer',
        ], [
            'status.*.required' => 'Seluruh mahasiswa harus diberikan status kehadiran.',
            'status.*.in' => 'Status kehadiran tidak valid.',
        ]);

        DB::beginTransaction();
        try {
            $mahasiswaIds = array_keys($request->status);

            foreach ($mahasiswaIds as $mahasiswaId) {
                Absen::create([
                    'pertemuan' => $request->pertemuan,
                    'tanggal' => now(),
                    'jadwals_id' => $request->jadwals_id,
                    'matkuls_id' => $request->matkuls_id,
                    'dosens_id' => $request->dosens_id,
                    'prodis_id' => $request->prodis_id,
                    'kelas_id' => $request->kelas_id,
                    'mahasiswas_id' => $mahasiswaId,
                    'tahun' => $request->tahun,
                    'status' => $request->status[$mahasiswaId],
                ]);
            }

            Resume::create([
                'pertemuan' => $request->pertemuan,
                'tanggal' => now(),
                'waktu' => now(),
                'tahun' => $request->tahun,
                'materi' => $request->materiResume,
                'jadwals_id' => $request->jadwals_id,
                'dosens_id' => $request->dosens_id,
                'matkuls_id' => $request->matkuls_id,
                'prodis_id' => $request->prodis_id,
                'kelas_id' => $request->kelas_id,
                'tidak_hadir' => $request->jumlahTidakHadir,
                'hadir' => $request->jumlahHadir,
            ]);

            DB::commit();
            return redirect()->route('v2.dosen.presensi.index')->with('success', 'Data presensi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = 'Gagal menyimpan data presensi. Pastikan semua status kehadiran telah diisi atau hubungi administrator.';
            if (config('app.env') === 'local') {
                $errorMessage = 'Gagal menyimpan presensi: ' . $e->getMessage();
            }
            return back()->with('error', $errorMessage);
        }
    }

    public function update(Request $request, $jadwal_id, $pertemuan)
    {
        $request->validate([
            'status' => 'required|array',
            'status.*' => 'required|in:H,T,I,S,A,C',
            'materiResume' => 'required|string',
            'jumlahHadir' => 'required|integer',
            'jumlahTidakHadir' => 'required|integer',
        ], [
            'status.*.required' => 'Seluruh mahasiswa harus diberikan status kehadiran.',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->status as $mahasiswaId => $status) {
                Absen::where('jadwals_id', $jadwal_id)
                    ->where('pertemuan', $pertemuan)
                    ->where('mahasiswas_id', $mahasiswaId)
                    ->update(['status' => $status]);
            }

            Resume::where('jadwals_id', $jadwal_id)
                ->where('pertemuan', $pertemuan)
                ->update([
                    'materi' => $request->materiResume,
                    'hadir' => $request->jumlahHadir,
                    'tidak_hadir' => $request->jumlahTidakHadir,
                ]);

            // Mark Request as completed if exists
            RequestEditPresensi::where('jadwal_id', $jadwal_id)
                ->where('pertemuan', $pertemuan)
                ->where('disetujui', true)
                ->update(['status' => true]);

            DB::commit();
            return redirect()->route('v2.dosen.presensi.index')->with('success', 'Data presensi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = 'Gagal memperbarui data presensi.';
            if (config('app.env') === 'local') {
                $errorMessage .= ' ' . $e->getMessage();
            }
            return back()->with('error', $errorMessage);
        }
    }
}
