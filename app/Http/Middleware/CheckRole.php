<?php

namespace App\Http\Middleware;

use App\Models\Jabatan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        $userJabatans = [];

        if (Auth::guard('jabatan')->check()) {
            $user = Auth::guard('jabatan')->user();
            if ($user->dosens_id) {
                $userJabatans = Jabatan::where('dosens_id', $user->dosens_id)
                    ->where('status', 1)
                    ->pluck('nama_jabatan')
                    ->toArray();
            } elseif ($user->pegawais_id) {
                $userJabatans = Jabatan::where('pegawais_id', $user->pegawais_id)
                    ->where('status', 1)
                    ->pluck('nama_jabatan')
                    ->toArray();
            }
        } elseif (Auth::guard('dosen')->check()) {
            $user = Auth::guard('dosen')->user();
            $userJabatans = Jabatan::where('dosens_id', $user->id)
                ->where('status', 1)
                ->pluck('nama_jabatan')
                ->toArray();
        } elseif (Auth::guard('pegawai')->check()) {
            $user = Auth::guard('pegawai')->user();
            $userJabatans = Jabatan::where('pegawais_id', $user->id)
                ->where('status', 1)
                ->pluck('nama_jabatan')
                ->toArray();
        }

        $sessionJabatans = session('user.jabatans', []);
        $allRoles = array_unique(array_merge($userJabatans, $sessionJabatans));

        foreach ($roles as $role) {
            if (in_array(strtolower(trim($role)), $allRoles)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
    }
}
