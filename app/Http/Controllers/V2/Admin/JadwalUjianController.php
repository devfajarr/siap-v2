<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\Matkul;
use App\Models\Pegawai;
use App\Models\Ruangan;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class JadwalUjianController extends Controller
{
    public function index(Request $request): Response
    {
        $pengawasValue = $request->input('pengawas_value');
        $kelasId = $request->input('kelas_id');
        $jenis = $request->input('jenis');
        $search = $request->input('search');

        $pengawasId = null;
        $pengawasType = null;
        if ($pengawasValue && str_contains($pengawasValue, '-')) {
            [$type, $id] = explode('-', $pengawasValue);
            $pengawasId = $id;
            $pengawasType = $type === 'dosen' ? Dosen::class : Pegawai::class;
        }

        // Fetch valid related data for filter and form
        $kelass = Kelas::with('semester', 'prodi')
            ->whereHas('semester', function ($query) {
                $query->where('status', 1);
            })->get();

        $dosens = Dosen::orderBy('nama', 'asc')->get();
        $pegawais = Pegawai::orderBy('nama', 'asc')->get();

        $matkuls = Matkul::with('semester', 'prodi')->orderBy('nama_matkul', 'asc')->get();
        $ruangans = Ruangan::orderBy('nama', 'asc')->get();

        // Active academic year
        $tahun = TahunAkademik::where('status', '1')->first();

        // Main Query
        $jadwals = JadwalUjian::with(['pengawas', 'kelas', 'matkul', 'ruangan'])
            ->when($search, function ($query, $search) {
                $query->whereHas('matkul', function ($q) use ($search) {
                    $q->where('nama_matkul', 'like', "%{$search}%");
                });
            })
            ->when($pengawasId && $pengawasType, function ($query) use ($pengawasId, $pengawasType) {
                $query->where('pengawas_id', $pengawasId)
                    ->where('pengawas_type', $pengawasType);
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
            'filters' => $request->only(['search', 'pengawas_value', 'kelas_id', 'jenis']),
            'kelass' => $kelass,
            'dosens' => $dosens,
            'pegawais' => $pegawais,
            'matkuls' => $matkuls,
            'ruangans' => $ruangans,
            'tahun_akademik' => $tahun ? $tahun->tahun_akademik : null,
            'is_tahun_akademik_active' => $tahun ? true : false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'matkuls_id' => 'required|exists:matkuls,id',
            'pengawas_value' => ['required', 'string', function ($attribute, $value, $fail) {
                if (! str_contains($value, '-')) {
                    return $fail('Format pengawas tidak valid.');
                }
                [$type, $id] = explode('-', $value);
                if (! in_array($type, ['dosen', 'pegawai'])) {
                    return $fail('Tipe pengawas tidak valid.');
                }
                $table = $type === 'dosen' ? 'dosens' : 'pegawais';
                if (! DB::table($table)->where('id', $id)->exists()) {
                    return $fail('Pengawas tidak ditemukan.');
                }
            }],
            'kelas_id' => 'required|exists:kelas,id',
            'ruangans_id' => 'required|exists:ruangans,id',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'tanggal' => 'required|date',
            'jenis_ujian' => 'required|string',
            'tahun' => 'required|string',
        ], [
            'matkuls_id.required' => 'Mata kuliah harus dipilih',
            'pengawas_value.required' => 'Pengawas harus dipilih',
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

        [$type, $id] = explode('-', $request->pengawas_value);
        $pengawasType = $type === 'dosen' ? Dosen::class : Pegawai::class;

        JadwalUjian::create([
            'matkuls_id' => $request->matkuls_id,
            'pengawas_id' => $id,
            'pengawas_type' => $pengawasType,
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

    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'matkuls_id' => 'required|exists:matkuls,id',
            'pengawas_value' => ['required', 'string', function ($attribute, $value, $fail) {
                if (! str_contains($value, '-')) {
                    return $fail('Format pengawas tidak valid.');
                }
                [$type, $id] = explode('-', $value);
                if (! in_array($type, ['dosen', 'pegawai'])) {
                    return $fail('Tipe pengawas tidak valid.');
                }
                $table = $type === 'dosen' ? 'dosens' : 'pegawais';
                if (! DB::table($table)->where('id', $id)->exists()) {
                    return $fail('Pengawas tidak ditemukan.');
                }
            }],
            'kelas_id' => 'required|exists:kelas,id',
            'ruangans_id' => 'required|exists:ruangans,id',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'tanggal' => 'required|date',
            'jenis_ujian' => 'required|string',
            'tahun' => 'required|string',
        ], [
            'matkuls_id.required' => 'Mata kuliah harus dipilih',
            'pengawas_value.required' => 'Pengawas harus dipilih',
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

        [$type, $id] = explode('-', $request->pengawas_value);
        $pengawasType = $type === 'dosen' ? Dosen::class : Pegawai::class;

        $jadwal->update([
            'matkuls_id' => $request->matkuls_id,
            'pengawas_id' => $id,
            'pengawas_type' => $pengawasType,
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

    public function destroy($id): RedirectResponse
    {
        $jadwal = JadwalUjian::findOrFail($id);
        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal ujian berhasil dihapus.');
    }
}
