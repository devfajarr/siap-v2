<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ?
            ['admin', 'direktur', 'wakil_direktur', 'kaprodi', 'mahasiswa', 'dosen', 'jabatan'] :
            $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $guardRedirects = [
                    'admin' => 'v2.admin.dashboard',
                    'mahasiswa' => 'v2.mahasiswa.dashboard',
                    'direktur' => 'v2.direktur.dashboard',
                    'wakil_direktur' => 'v2.direktur.dashboard',
                    'dosen' => 'v2.dosen.dashboard',
                    'kaprodi' => 'v2.kaprodi.dashboard',
                    'bpmi' => 'v2.dosen.dashboard',
                    'kemahasiswaan' => 'v2.dosen.dashboard',
                    'perpustakaan' => 'v2.dosen.dashboard',
                    'sarpras' => 'v2.dosen.dashboard',
                    'personalia' => 'v2.dosen.dashboard',
                ];
                if ($guard === 'jabatan') {
                    $user = Auth::guard('jabatan')->user();
                    $route = $guardRedirects[$user->nama_jabatan] ?? 'v2.dosen.dashboard';
                } else {
                    $route = $guardRedirects[$guard] ?? 'dashboard';
                }

                return redirect()->route($route);
            }
        }

        return $next($request);
    }
}
