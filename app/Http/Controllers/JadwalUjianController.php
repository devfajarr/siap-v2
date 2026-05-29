<?php

namespace App\Http\Controllers;

use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\Matkul;
use App\Models\Pegawai;
use App\Models\Ruangan;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;

class JadwalUjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jadwals = JadwalUjian::with('kelas', 'matkul', 'ruangan', 'pegawai')->latest()->get();
        $pengawass = Pegawai::all();
        $kelass = Kelas::with('semester', 'prodi')->whereHas('semester', function ($query) {
            $query->where('status', 1);
        })->get();
        $matkuls = Matkul::with('semester', 'prodi')->orderBy('nama_matkul', 'asc')->get();
        $ruangans = Ruangan::all();
        $tahun = TahunAkademik::where('status', '1')->first();

        return view('pages.jadwal-ujian.index', compact('jadwals', 'pengawass', 'kelass', 'matkuls', 'ruangans', 'tahun'));
    }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'matkul_id' => 'required|exists:matkuls,id',
            'pengawas_id' => 'required|exists:pegawais,id',
            'kelas_id' => 'required|exists:kelas,id',
            'ruangan_id' => 'required|exists:ruangans,id',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'tanggal' => 'required|date',
            'jenis_ujian' => 'required|string',
        ], [
            'matkul_id.required' => 'Mata kuliah harus dipilih',
            'matkul_id.exists' => 'Mata kuliah tidak valid',
            'pengawas_id.required' => 'Pengawas harus dipilih',
            'pengawas_id.exists' => 'Pengawas tidak valid',
            'kelas_id.required' => 'Kelas harus dipilih',
            'kelas_id.exists' => 'Kelas tidak valid',
            'ruangan_id.required' => 'Ruangan harus dipilih',
            'ruangan_id.exists' => 'Ruangan tidak valid',
            'waktu_mulai.required' => 'Jam mulai harus diisi',
            'waktu_mulai.date_format' => 'Format jam mulai tidak valid',
            'waktu_selesai.required' => 'Jam selesai harus diisi',
            'waktu_selesai.date_format' => 'Format jam selesai tidak valid',
            'waktu_selesai.after' => 'Jam selesai harus lebih dari jam mulai',
            'tanggal.required' => 'Tanggal ujian harus diisi',
            'tanggal.date' => 'Format tanggal tidak valid',
            'jenis_ujian.required' => 'Jenis ujian harus diisi',
        ]);

        $existingUjian = JadwalUjian::where('matkuls_id', $request->matkul_id)
            ->where('kelas_id', $request->kelas_id)
            ->where('jenis_ujian', $request->jenis_ujian)
            ->where('tanggal', $request->tanggal)
            ->first();

        if ($existingUjian) {
            return response()->json([
                'status' => 400,
                'error' => 'Jadwal ujian sudah ada untuk kelas dan mata kuliah ini pada tanggal tersebut.',
            ], 400);
        }

        JadwalUjian::create([
            'matkuls_id' => $request->matkul_id,
            'pegawais_id' => $request->pengawas_id,
            'ruangans_id' => $request->ruangan_id,
            'kelas_id' => $request->kelas_id,
            'tahun' => $request->tahun,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'tanggal' => $request->tanggal,
            'jenis_ujian' => $request->jenis_ujian,
        ]);

        return response()->json([
            'status' => 200,
            'success' => 'Jadwal ujian berhasil ditambahkan!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(JadwalUjian $jadwalUjian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JadwalUjian $jadwalUjian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'matkul_id' => 'required|exists:matkuls,id',
            'pengawas_id' => 'required|exists:pegawais,id',
            'kelas_id' => 'required|exists:kelas,id',
            'ruangan_id' => 'required|exists:ruangans,id',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required|after:waktu_mulai',
            'tanggal' => 'required|date',
            'jenis_ujian' => 'required|string',
        ], [
            'matkul_id.required' => 'Mata kuliah harus dipilih',
            'matkul_id.exists' => 'Mata kuliah tidak valid',
            'pengawas_id.required' => 'Pengawas harus dipilih',
            'pengawas_id.exists' => 'Pengawas tidak valid',
            'kelas_id.required' => 'Kelas harus dipilih',
            'kelas_id.exists' => 'Kelas tidak valid',
            'ruangan_id.required' => 'Ruangan harus dipilih',
            'ruangan_id.exists' => 'Ruangan tidak valid',
            'waktu_mulai.required' => 'Jam mulai harus diisi',
            'waktu_selesai.required' => 'Jam selesai harus diisi',
            'waktu_selesai.after' => 'Jam selesai harus lebih dari jam mulai',
            'tanggal.required' => 'Tanggal ujian harus diisi',
            'tanggal.date' => 'Format tanggal tidak valid',
            'jenis_ujian.required' => 'Jenis ujian harus diisi',
        ]);

        $jadwal = JadwalUjian::findOrFail($id);

        $existingUjian = JadwalUjian::where('id', '!=', $id)
            ->where('matkuls_id', $request->matkul_id)
            ->where('kelas_id', $request->kelas_id)
            ->where('jenis_ujian', $request->jenis_ujian)
            ->where('tanggal', $request->tanggal)
            ->first();

        if ($existingUjian) {
            return response()->json([
                'status' => 400,
                'error' => 'Jadwal ujian lain sudah terdaftar untuk kelas dan mata kuliah ini pada tanggal tersebut.',
            ], 400);
        }

        $jadwal->update([
            'matkuls_id' => $request->matkul_id,
            'pegawais_id' => $request->pengawas_id,
            'ruangans_id' => $request->ruangan_id,
            'kelas_id' => $request->kelas_id,
            'tahun' => $request->tahun,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'tanggal' => $request->tanggal,
            'jenis_ujian' => $request->jenis_ujian,
        ]);

        return response()->json([
            'status' => 200,
            'success' => 'Jadwal ujian berhasil diperbarui!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $jadwal = JadwalUjian::findOrFail($id);
        $jadwal->delete();

        return response()->json([
            'success' => 'Jadwal berhasil dihapus',
        ]);
    }

    public function search(Request $request)
    {
        $pengawas = $request->input('pengawas');
        $kelas = $request->input('kelas');
        $search = $request->input('search');
        $jenis = $request->input('jenis');

        $jadwal = JadwalUjian::with(['matkul', 'pegawai', 'kelas', 'ruangan'])
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('matkul', function ($q) use ($search) {
                    $q->where('nama_matkul', 'LIKE', "%{$search}%");
                });
            })
            ->when($pengawas, function ($query) use ($pengawas) {
                return $query->where('pegawais_id', $pengawas);
            })
            ->when($kelas, function ($query) use ($kelas) {
                return $query->where('kelas_id', $kelas);
            })
            ->when($jenis, function ($query) use ($jenis) {
                return $query->where('jenis_ujian', $jenis);
            })
            ->get();

        return response()->json(['data' => $jadwal]);
    }
}
