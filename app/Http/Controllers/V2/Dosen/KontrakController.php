<?php

namespace App\Http\Controllers\V2\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Kontrak;
use App\Models\Kaprodi;
use App\Models\Wadir;
use App\Models\TahunAkademik;
use App\Models\PengajuanRekapkontrak;
use App\Imports\KontrakImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class KontrakController extends Controller
{
    protected $userId;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->userId = Session::get('user.id') ?? Auth::guard('dosen')->id();
            return $next($request);
        });
    }

    public function index()
    {
        $jadwals = Jadwal::with([
            'dosen' => fn($q) => $q->withTrashed(),
            'matkul' => fn($q) => $q->withTrashed(),
            'kelas.prodi' => fn($q) => $q->withTrashed(),
            'ruangan' => fn($q) => $q->withTrashed(),
            'pengajuanKontreak'
        ])
        ->where('dosens_id', $this->userId)
        ->latest()
        ->get();

        $rekapStatuses = PengajuanRekapkontrak::whereIn('jadwal_id', $jadwals->pluck('id'))
            ->get()
            ->keyBy('jadwal_id');

        $pertemuanCounts = Kontrak::whereIn('jadwals_id', $jadwals->pluck('id'))
            ->groupBy('jadwals_id')
            ->selectRaw('jadwals_id, max(pertemuan) as max_pertemuan')
            ->pluck('max_pertemuan', 'jadwals_id');

        return inertia('Dosen/Kontrak/Index', [
            'jadwals' => $jadwals,
            'rekapStatuses' => $rekapStatuses,
            'pertemuanCounts' => $pertemuanCounts,
        ]);
    }

    public function create($id)
    {
        $jadwal = Jadwal::with(['dosen', 'matkul', 'kelas.prodi', 'ruangan'])
            ->where('id', $id)
            ->where('dosens_id', $this->userId)
            ->firstOrFail();

        $tahun = TahunAkademik::where('status', 1)->first();

        return inertia('Dosen/Kontrak/Create', [
            'jadwal' => $jadwal,
            'tahun' => $tahun ? $tahun->tahun_akademik : '',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jadwals_id' => 'required|exists:jadwals,id',
            'matkuls_id' => 'required',
            'kelas_id' => 'required',
            'tahun' => 'required',
            'materiKontrak' => 'required|array',
            'pustakaKontrak' => 'required|array',
        ]);

        $jadwal = Jadwal::where('id', $request->jadwals_id)
            ->where('dosens_id', $this->userId)
            ->first();
        if (!$jadwal) {
            return back()->with('error', 'Jadwal tidak ditemukan atau Anda tidak memiliki akses.');
        }

        DB::beginTransaction();
        try {
            $kontrakData = [];
            foreach ($request->materiKontrak as $pertemuan => $materi) {
                $kontrakData[] = [
                    'tahun' => $request->tahun,
                    'pertemuan' => $pertemuan,
                    'matkuls_id' => $request->matkuls_id,
                    'kelas_id' => $request->kelas_id,
                    'materi' => $materi ?? null,
                    'pustaka' => $request->pustakaKontrak[$pertemuan] ?? null,
                    'jadwals_id' => $request->jadwals_id,
                ];
            }

            Kontrak::insert($kontrakData);
            DB::commit();

            return redirect()->route('v2.dosen.kontrak.index')->with('success', 'Kontrak perkuliahan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan kontrak: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $jadwal = Jadwal::with(['dosen', 'matkul', 'kelas.prodi', 'ruangan'])
            ->where('id', $id)
            ->where('dosens_id', $this->userId)
            ->firstOrFail();

        $kontraks = Kontrak::where('jadwals_id', $id)
            ->orderBy('pertemuan', 'asc')
            ->get();

        return inertia('Dosen/Kontrak/Edit', [
            'jadwal' => $jadwal,
            'kontraks' => $kontraks,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'materiKontrak' => 'required|array',
            'pustakaKontrak' => 'required|array',
            'tahun' => 'required',
            'matkuls_id' => 'required',
            'kelas_id' => 'required',
        ]);

        $jadwal = Jadwal::where('id', $id)
            ->where('dosens_id', $this->userId)
            ->first();
        if (!$jadwal) {
            return back()->with('error', 'Jadwal tidak ditemukan atau Anda tidak memiliki akses.');
        }

        DB::beginTransaction();
        try {
            foreach ($request->materiKontrak as $pertemuan => $materi) {
                Kontrak::updateOrCreate(
                    [
                        'jadwals_id' => $id,
                        'pertemuan' => $pertemuan,
                    ],
                    [
                        'tahun' => $request->tahun,
                        'matkuls_id' => $request->matkuls_id,
                        'kelas_id' => $request->kelas_id,
                        'materi' => $materi ?? null,
                        'pustaka' => $request->pustakaKontrak[$pertemuan] ?? null,
                    ]
                );
            }
            DB::commit();

            return redirect()->route('v2.dosen.kontrak.index')->with('success', 'Data kontrak perkuliahan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui kontrak: ' . $e->getMessage());
        }
    }

    public function rekap($matkul_id, $kelas_id, $jadwal_id)
    {
        $jadwal = Jadwal::with(['dosen', 'matkul', 'kelas.prodi', 'kelas.semester'])
            ->findOrFail($jadwal_id);

        $kontraks = Kontrak::where('jadwals_id', $jadwal_id)
            ->orderBy('pertemuan', 'asc')
            ->get();

        $kaprodi = Kaprodi::where('prodis_id', $jadwal->kelas->prodi->id)->first();

        $wadir = Wadir::where('status', 1)
            ->where('no', 1)
            ->first();

        return inertia('Dosen/Kontrak/Rekap', [
            'jadwal' => $jadwal,
            'kontraks' => $kontraks,
            'kaprodi' => $kaprodi,
            'wadir' => $wadir,
        ]);
    }

    public function importWithReplace(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'jadwals_id' => 'required|exists:jadwals,id'
        ]);

        $tahun = TahunAkademik::where('status', 1)->first();
        if (!$tahun) {
            return back()->with('error', 'Tahun akademik aktif tidak ditemukan.');
        }

        $jadwal = Jadwal::where('id', $request->jadwals_id)
            ->where('dosens_id', $this->userId)
            ->first();
        if (!$jadwal) {
            return back()->with('error', 'Jadwal tidak ditemukan atau Anda tidak memiliki akses.');
        }

        DB::beginTransaction();
        try {
            $data = Excel::toArray(new KontrakImport, $request->file('file'))[0];
            array_shift($data); // Remove header row

            foreach ($data as $row) {
                if (empty($row[0])) continue;

                $pertemuan = (int) $row[0];
                if ($pertemuan < 1 || $pertemuan > 14) continue;

                Kontrak::updateOrCreate(
                    [
                        'tahun' => $tahun->tahun_akademik,
                        'pertemuan' => $pertemuan,
                        'matkuls_id' => $jadwal->matkuls_id,
                        'kelas_id' => $jadwal->kelas_id,
                        'jadwals_id' => $request->jadwals_id,
                    ],
                    [
                        'materi' => $row[1] ?? null,
                        'pustaka' => $row[2] ?? null,
                    ]
                );
            }

            DB::commit();
            return back()->with('success', 'Data kontrak perkuliahan berhasil diimport.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }

    public function downloadFormat()
    {
        $filePath = public_path('format/import_kontrak.xlsx');
        if (!file_exists($filePath)) {
            return back()->with('error', 'File template format import tidak ditemukan.');
        }
        return response()->download($filePath, 'Format_Import_Kontrak.xlsx');
    }
}
