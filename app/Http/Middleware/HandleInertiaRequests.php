<?php

namespace App\Http\Middleware;

use App\Models\Mahasiswa;
use App\Models\OrangTua;
use App\Models\PengajuanCetakKartuUjian;
use App\Models\PengajuanCetakKhs;
use App\Models\PermohonanSurat;
use App\Models\Prodi;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'v2';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // Fallback for multiple guards
        if (! $user) {
            foreach (['admin', 'dosen', 'pegawai', 'mahasiswa', 'kaprodi', 'direktur', 'wakil_direktur', 'jabatan', 'orang_tua'] as $guard) {
                if (auth()->guard($guard)->check()) {
                    $user = auth()->guard($guard)->user();
                    break;
                }
            }
        }

        $role = 'Guest';
        $avatar = '/images/user.png';
        $semesters = [];
        $childrenShared = [];
        $activeChildShared = null;

        if ($user) {
            // Determine Role based on guard or session
            if (auth()->guard('admin')->check()) {
                $role = 'Administrator';
            } elseif (auth()->guard('dosen')->check()) {
                $role = 'Dosen';
            } elseif (auth()->guard('mahasiswa')->check()) {
                $role = 'Mahasiswa';
                if ($user->profile_picture) {
                    $avatar = asset('storage/profile_pictures/'.$user->profile_picture);
                }

                // Fetch semesters based on student's current semester level
                $mahasiswa = Mahasiswa::with('kelas.semester')->find($user->id);
                $currentSemesterLevel = $mahasiswa->kelas->semester->semester ?? 0;

                $semesters = Semester::where('semester', '<=', $currentSemesterLevel)
                    ->orderBy('semester', 'asc')
                    ->get()
                    ->map(function ($semester) {
                        return [
                            'id' => $semester->id,
                            'title' => 'Semester '.$semester->semester,
                            'href' => '/v2/mahasiswa/riwayat/'.$semester->id,
                        ];
                    })->toArray();
            } elseif (auth()->guard('orang_tua')->check()) {
                $role = 'Orang Tua';

                $activeChildId = session('user.activeChildId');

                // Fetch associated students
                $orangTua = OrangTua::with('mahasiswas.kelas.semester')->find($user->id);
                $children = $orangTua ? $orangTua->mahasiswas : collect();

                if (! $activeChildId && $children->isNotEmpty()) {
                    $activeChildId = $children->first()->id;
                    session(['user.activeChildId' => $activeChildId]);
                }

                $activeChild = $children->firstWhere('id', $activeChildId);

                if ($activeChild) {
                    $currentSemesterLevel = $activeChild->kelas->semester->semester ?? 0;
                    $semesters = Semester::where('semester', '<=', $currentSemesterLevel)
                        ->orderBy('semester', 'asc')
                        ->get()
                        ->map(function ($semester) {
                            return [
                                'id' => $semester->id,
                                'title' => 'Semester '.$semester->semester,
                                'href' => '/v2/orang-tua/nilai/riwayat/'.$semester->id,
                            ];
                        })->toArray();

                    $activeChildShared = [
                        'id' => $activeChild->id,
                        'nama_lengkap' => $activeChild->nama_lengkap,
                        'nim' => $activeChild->nim,
                        'kelas_id' => $activeChild->kelas_id,
                        'kelas_name' => $activeChild->kelas->nama_kelas ?? '-',
                        'prodi_name' => $activeChild->kelas->prodi->nama_prodi ?? '-',
                    ];
                }

                $childrenShared = $children->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'nama_lengkap' => $c->nama_lengkap,
                        'nim' => $c->nim,
                    ];
                })->toArray();
            } elseif (auth()->guard('kaprodi')->check()) {
                $role = 'Kaprodi';
            } elseif (auth()->guard('direktur')->check()) {
                $role = 'Direktur';
            } elseif (auth()->guard('wakil_direktur')->check()) {
                $role = 'Wakil Direktur';
            } elseif (auth()->guard('pegawai')->check()) {
                $role = 'Pegawai';
            } elseif (auth()->guard('jabatan')->check()) {
                $roleMap = [
                    'bpmi' => 'BPMI',
                    'kemahasiswaan' => 'Kemahasiswaan',
                    'perpustakaan' => 'Perpustakaan',
                    'sarpras' => 'Sarpras',
                    'personalia' => 'Personalia',
                ];
                $role = $roleMap[$user->nama_jabatan] ?? ucfirst($user->nama_jabatan);
            }
        }

        $prodis = [];
        $activeProdiId = null;
        if ($user && $role === 'Kaprodi') {
            $prodiIds = session('user.prodiIds', []);
            $activeProdiId = session('user.activeProdiId');
            $prodis = Prodi::whereIn('id', $prodiIds)
                ->select('id', 'nama_prodi')
                ->get()
                ->toArray();
        }

        $pendingKhsCount = 0;
        $pendingSuratCount = 0;
        $pendingKartuUjianCount = 0;
        if ($user && $role === 'Administrator') {
            $pendingKhsCount = PengajuanCetakKhs::where('status', 0)->count();
            $pendingSuratCount = PermohonanSurat::where('setuju_kaprodi', 1)
                ->where('status', 0)
                ->count();
            $pendingKartuUjianCount = PengajuanCetakKartuUjian::where('status', 0)->count();
        }

        $baseUser = $user;
        if ($user) {
            if (auth()->guard('kaprodi')->check() || auth()->guard('direktur')->check() || auth()->guard('wakil_direktur')->check()) {
                $baseUser = $user->dosen;
            } elseif (auth()->guard('jabatan')->check()) {
                $baseUser = $user->dosen ?? $user->pegawai;
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'nama' => $user->nama ?? $user->nama_lengkap ?? $user->name,
                    'role' => $role,
                    'avatar' => $avatar,
                    'no_telephone' => $baseUser->no_telephone ?? null,
                    'whatsapp_verified_at' => $baseUser->whatsapp_verified_at ?? null,
                    'prodis' => $prodis,
                    'activeProdiId' => $activeProdiId,
                    'pending_khs_count' => $pendingKhsCount,
                    'pending_surat_count' => $pendingSuratCount,
                    'pending_kartu_ujian_count' => $pendingKartuUjianCount,
                    'jabatans' => session('user.jabatans', []),
                    'is_dosen' => ! empty($user->dosens_id) || auth()->guard('dosen')->check(),
                    'is_pegawai' => ! empty($user->pegawais_id) || auth()->guard('pegawai')->check(),
                    'status_pa' => auth()->guard('dosen')->check() ? auth()->guard('dosen')->user()->pembimbing_akademik : session('user.status_pa', 0),
                    'children' => $childrenShared,
                    'active_child' => $activeChildShared,
                ] : null,
                'semesters' => $semesters,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'app_env' => config('app.env'),
        ];
    }
}
