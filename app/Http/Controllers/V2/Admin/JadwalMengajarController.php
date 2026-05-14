<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Matkul;
use App\Models\Message;
use App\Models\Ruangan;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JadwalMengajarController extends Controller
{
    public function index(Request $request)
    {
        $dosenId = $request->input('dosen_id');
        $kelasId = $request->input('kelas_id');
        $hari = $request->input('hari');
        $search = $request->input('search');

        // Fetch valid related data for filter and form
        $kelass = Kelas::with('semester', 'prodi')
            ->whereHas('semester', function ($query) {
                $query->where('status', 1);
            })->get();
            
        $dosens = Dosen::orderBy('nama', 'asc')->get();
        
        // Pass matkuls logic to frontend for reactivity based on classes selected, 
        // but we send all matkuls so Vue can filter them by prodi and semester.
        $matkuls = Matkul::with('semester', 'prodi')->orderBy('nama_matkul', 'asc')->get();
        $ruangans = Ruangan::orderBy('nama', 'asc')->get();
        
        // Active academic year with safety checks
        $tahun = TahunAkademik::where('status', '1')->first();

        // Main Query
        $jadwals = Jadwal::with(['dosen', 'kelas', 'matkul', 'ruangan'])
            ->when($search, function ($query, $search) {
                $query->whereHas('matkul', function ($q) use ($search) {
                    $q->where('nama_matkul', 'like', "%{$search}%");
                });
            })
            ->when($dosenId, function ($query, $dosenId) {
                $query->where('dosens_id', $dosenId);
            })
            ->when($kelasId, function ($query, $kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->when($hari, function ($query, $hari) {
                $query->where('hari', $hari);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/JadwalMengajar/Index', [
            'jadwals' => $jadwals,
            'filters' => $request->only(['search', 'dosen_id', 'kelas_id', 'hari']),
            'dosens' => $dosens,
            'kelass' => $kelass,
            'matkuls' => $matkuls,
            'ruangans' => $ruangans,
            // Provide a fallback if no active academic year is found
            'tahun_akademik' => $tahun ? $tahun->tahun_akademik : null,
            'is_tahun_akademik_active' => $tahun ? true : false,
            'is_semester_active' => $kelass->count() > 0 ? true : false,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'matkul_id' => 'required|exists:matkuls,id',
            'dosen_id' => 'required|exists:dosens,id',
            'kelas_id' => 'required|exists:kelas,id',
            'ruangan_id' => 'required|exists:ruangans,id',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'hari' => 'required',
            'tahun' => 'required',
        ], [
            'matkul_id.required' => 'Mata kuliah harus dipilih',
            'matkul_id.exists' => 'Mata kuliah tidak valid',
            'dosen_id.required' => 'Dosen harus dipilih',
            'dosen_id.exists' => 'Dosen tidak valid',
            'kelas_id.required' => 'Kelas harus dipilih',
            'kelas_id.exists' => 'Kelas tidak valid',
            'ruangan_id.required' => 'Ruangan harus dipilih',
            'ruangan_id.exists' => 'Ruangan tidak valid',
            'jam_mulai.required' => 'Jam mulai harus diisi',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid',
            'jam_selesai.required' => 'Jam selesai harus diisi',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid',
            'jam_selesai.after' => 'Jam selesai harus lebih dari jam mulai',
            'hari.required' => 'Hari harus dipilih',
            'tahun.required' => 'Tahun akademik tidak ditemukan (tidak ada yang aktif)',
        ]);

        $existingJadwal = Jadwal::where('matkuls_id', $request->matkul_id)
            ->where('kelas_id', $request->kelas_id)
            ->first();

        if ($existingJadwal) {
            return redirect()->back()->with('error', 'Jadwal sudah ada untuk kelas ini dan mata kuliah ini.');
        }

        Jadwal::create([
            'dosens_id' => $request->dosen_id,
            'matkuls_id' => $request->matkul_id,
            'kelas_id' => $request->kelas_id,
            'tahun' => $request->tahun,
            'ruangans_id' => $request->ruangan_id,
            'waktu_mulai' => $request->jam_mulai,
            'waktu_selesai' => $request->jam_selesai,
            'hari' => $request->hari,
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'matkul_id' => 'required|exists:matkuls,id',
            'dosen_id' => 'required|exists:dosens,id',
            'kelas_id' => 'required|exists:kelas,id',
            'ruangan_id' => 'required|exists:ruangans,id',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'hari' => 'required|string',
            'tahun' => 'required',
        ], [
            'matkul_id.required' => 'Matkul harus dipilih',
            'matkul_id.exists' => 'Matkul tidak valid',
            'dosen_id.required' => 'Dosen harus dipilih',
            'dosen_id.exists' => 'Dosen tidak valid',
            'kelas_id.required' => 'Kelas harus dipilih',
            'kelas_id.exists' => 'Kelas tidak valid',
            'ruangan_id.required' => 'Ruangan harus dipilih',
            'ruangan_id.exists' => 'Ruangan tidak valid',
            'jam_mulai.required' => 'Jam mulai harus diisi',
            'jam_selesai.required' => 'Jam selesai harus diisi',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai',
            'hari.required' => 'Hari harus dipilih',
            'tahun.required' => 'Tahun akademik tidak ditemukan (tidak ada yang aktif)',
        ]);

        $jadwal = Jadwal::findOrFail($id);
        $jadwal->matkuls_id = $request->matkul_id;
        $jadwal->dosens_id = $request->dosen_id;
        $jadwal->kelas_id = $request->kelas_id;
        $jadwal->ruangans_id = $request->ruangan_id;
        $jadwal->waktu_mulai = $request->jam_mulai;
        $jadwal->waktu_selesai = $request->jam_selesai;
        $jadwal->hari = $request->hari;
        $jadwal->tahun = $request->tahun; // Added this to fix the missing update field
        $jadwal->save();

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui');
    }

    public function destroy($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();
        Message::where('jadwal_id', $id)->delete();

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus');
    }
}
