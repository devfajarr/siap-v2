<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Matkul;
use App\Models\Pegawai;
use App\Models\Ruangan;
use App\Models\JadwalUjian;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JadwalUjianController extends Controller
{
    public function index(Request $request)
    {
        $pengawasId = $request->input('pengawas_id');
        $kelasId = $request->input('kelas_id');
        $jenis = $request->input('jenis');
        $search = $request->input('search');

        // Fetch valid related data for filter and form
        $kelass = Kelas::with('semester', 'prodi')
            ->whereHas('semester', function ($query) {
                $query->where('status', 1);
            })->get();
            
        $pengawass = Pegawai::orderBy('nama', 'asc')->get();
        
        $matkuls = Matkul::with('semester', 'prodi')->orderBy('nama_matkul', 'asc')->get();
        $ruangans = Ruangan::orderBy('nama', 'asc')->get();
        
        // Active academic year
        $tahun = TahunAkademik::where('status', '1')->first();

        // Main Query
        $jadwals = JadwalUjian::with(['pegawai', 'kelas', 'matkul', 'ruangan'])
            ->when($search, function ($query, $search) {
                $query->whereHas('matkul', function ($q) use ($search) {
                    $q->where('nama_matkul', 'like', "%{$search}%");
                });
            })
            ->when($pengawasId, function ($query, $pengawasId) {
                $query->where('pegawais_id', $pengawasId);
            })
            ->when($kelasId, function ($query, $kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->when($jenis, function ($query, $jenis) {
                $query->where('jenis_ujian', $jenis);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/JadwalUjian/Index', [
            'jadwals' => $jadwals,
            'filters' => $request->only(['search', 'pengawas_id', 'kelas_id', 'jenis']),
            'kelass' => $kelass,
            'pengawass' => $pengawass,
            'matkuls' => $matkuls,
            'ruangans' => $ruangans,
            'tahun_akademik' => $tahun ? $tahun->tahun_akademik : null,
            'is_tahun_akademik_active' => $tahun ? true : false,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'matkuls_id' => 'required|exists:matkuls,id',
            'pegawais_id' => 'required|exists:pegawais,id',
            'kelas_id' => 'required|exists:kelas,id',
            'ruangans_id' => 'required|exists:ruangans,id',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'tanggal' => 'required|date',
            'jenis_ujian' => 'required|string',
            'tahun' => 'required|string',
        ], [
            'matkuls_id.required' => 'Mata kuliah harus dipilih',
            'pegawais_id.required' => 'Pengawas harus dipilih',
            'kelas_id.required' => 'Kelas harus dipilih',
            'ruangans_id.required' => 'Ruangan harus dipilih',
            'waktu_mulai.required' => 'Jam mulai harus diisi',
            'waktu_selesai.required' => 'Jam selesai harus diisi',
            'waktu_selesai.after' => 'Jam selesai harus lebih dari jam mulai',
            'tanggal.required' => 'Tanggal ujian harus diisi',
            'jenis_ujian.required' => 'Jenis ujian harus diisi',
            'tahun.required' => 'Tahun Akademik harus tersedia',
        ]);

        $existingUjian = JadwalUjian::where('matkuls_id', $request->matkuls_id)
            ->where('kelas_id', $request->kelas_id)
            ->where('jenis_ujian', $request->jenis_ujian)
            ->where('tanggal', $request->tanggal)
            ->first();

        if ($existingUjian) {
            return redirect()->back()->with('error', 'Jadwal ujian sudah ada untuk kelas dan mata kuliah ini pada tanggal tersebut.');
        }

        JadwalUjian::create([
            'matkuls_id' => $request->matkuls_id,
            'pegawais_id' => $request->pegawais_id,
            'ruangans_id' => $request->ruangans_id,
            'kelas_id' => $request->kelas_id,
            'tahun' => $request->tahun,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'tanggal' => $request->tanggal,
            'jenis_ujian' => $request->jenis_ujian,
        ]);

        return redirect()->back()->with('success', 'Jadwal ujian berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'matkuls_id' => 'required|exists:matkuls,id',
            'pegawais_id' => 'required|exists:pegawais,id',
            'kelas_id' => 'required|exists:kelas,id',
            'ruangans_id' => 'required|exists:ruangans,id',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'tanggal' => 'required|date',
            'jenis_ujian' => 'required|string',
            'tahun' => 'required|string',
        ], [
            'matkuls_id.required' => 'Mata kuliah harus dipilih',
            'pegawais_id.required' => 'Pengawas harus dipilih',
            'kelas_id.required' => 'Kelas harus dipilih',
            'ruangans_id.required' => 'Ruangan harus dipilih',
            'waktu_mulai.required' => 'Jam mulai harus diisi',
            'waktu_selesai.required' => 'Jam selesai harus diisi',
            'waktu_selesai.after' => 'Jam selesai harus lebih dari jam mulai',
            'tanggal.required' => 'Tanggal ujian harus diisi',
            'jenis_ujian.required' => 'Jenis ujian harus diisi',
            'tahun.required' => 'Tahun Akademik harus tersedia',
        ]);

        $jadwal = JadwalUjian::findOrFail($id);

        $existingUjian = JadwalUjian::where('id', '!=', $id)
            ->where('matkuls_id', $request->matkuls_id)
            ->where('kelas_id', $request->kelas_id)
            ->where('jenis_ujian', $request->jenis_ujian)
            ->where('tanggal', $request->tanggal)
            ->first();

        if ($existingUjian) {
            return redirect()->back()->with('error', 'Jadwal ujian lain sudah terdaftar untuk kelas dan mata kuliah ini pada tanggal tersebut.');
        }

        $jadwal->update([
            'matkuls_id' => $request->matkuls_id,
            'pegawais_id' => $request->pegawais_id,
            'ruangans_id' => $request->ruangans_id,
            'kelas_id' => $request->kelas_id,
            'tahun' => $request->tahun,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'tanggal' => $request->tanggal,
            'jenis_ujian' => $request->jenis_ujian,
        ]);

        return redirect()->back()->with('success', 'Jadwal ujian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jadwal = JadwalUjian::findOrFail($id);
        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal ujian berhasil dihapus.');
    }
}
