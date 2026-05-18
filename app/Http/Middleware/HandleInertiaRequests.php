<?php

namespace App\Http\Middleware;

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
        if (!$user) {
            foreach (['admin', 'dosen', 'mahasiswa', 'kaprodi', 'direktur', 'wakil_direktur'] as $guard) {
                if (auth()->guard($guard)->check()) {
                    $user = auth()->guard($guard)->user();
                    break;
                }
            }
        }

        $role = 'Guest';
        $avatar = '/images/user.png';
        $semesters = [];

        if ($user) {
            // Determine Role based on guard or session
            if (auth()->guard('admin')->check()) $role = 'Administrator';
            else if (auth()->guard('dosen')->check()) $role = 'Dosen';
            else if (auth()->guard('mahasiswa')->check()) {
                $role = 'Mahasiswa';
                if ($user->profile_picture) {
                    $avatar = asset('storage/profile_pictures/' . $user->profile_picture);
                }
                $semesters = \App\Models\NilaiHuruf::with('semester')
                    ->where('mahasiswa_id', $user->id)
                    ->select('semester_id')
                    ->groupBy('semester_id')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->semester->id ?? null,
                            'title' => 'Semester ' . ($item->semester->semester ?? ''),
                            'href' => '/v2/mahasiswa/riwayat/' . ($item->semester->id ?? ''),
                        ];
                    })->filter(fn($item) => $item['id'] !== null)->values()->toArray();
            }
            else if (auth()->guard('kaprodi')->check()) $role = 'Kaprodi';
            else if (auth()->guard('direktur')->check()) $role = 'Direktur';
            else if (auth()->guard('wakil_direktur')->check()) $role = 'Wakil Direktur';
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'nama' => $user->nama ?? $user->nama_lengkap ?? $user->name,
                    'role' => $role,
                    'avatar' => $avatar,
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

