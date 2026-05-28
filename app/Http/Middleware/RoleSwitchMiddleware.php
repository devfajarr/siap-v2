<?php

namespace App\Http\Middleware;

use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\Pegawai;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleSwitchMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        if (Auth::guard('dosen')->check()) {
            /** @var Dosen $dosenUser */
            $dosenUser = Auth::guard('dosen')->user();

            if (preg_match('/^v2\/(bpmi|kemahasiswaan|perpustakaan|sarpras|personalia)(\/|$)/i', $path, $matches)) {
                $targetRole = strtolower($matches[1]);

                $jabatan = Jabatan::where('dosens_id', $dosenUser->id)
                    ->where('nama_jabatan', $targetRole)
                    ->where('status', 1)
                    ->first();

                if ($jabatan) {
                    Auth::guard('dosen')->logout();
                    Auth::guard('jabatan')->login($jabatan);

                    $jabatansList = Jabatan::where('dosens_id', $dosenUser->id)
                        ->where('status', 1)
                        ->pluck('nama_jabatan')
                        ->toArray();

                    session(['user' => [
                        'id' => $jabatan->id,
                        'kelasId' => $jabatan->kelas_id ?? null,
                        'nama' => $jabatan->nama ?? $jabatan->nama_lengkap ?? $jabatan->name,
                        'role' => $targetRole,
                        'jabatans' => $jabatansList,
                        'wadir' => $jabatan->no ?? null,
                        'prodiId' => $jabatan->prodis_id ?? null,
                        'prodiIds' => [],
                        'activeProdiId' => null,
                        'email' => $jabatan->email,
                        'status_pa' => $dosenUser->pembimbing_akademik ?? 0,
                    ]]);

                    return redirect($request->fullUrl());
                }
            }
        }

        if (Auth::guard('pegawai')->check()) {
            /** @var Pegawai $pegawaiUser */
            $pegawaiUser = Auth::guard('pegawai')->user();

            if (preg_match('/^v2\/(bpmi|kemahasiswaan|perpustakaan|sarpras|personalia)(\/|$)/i', $path, $matches)) {
                $targetRole = strtolower($matches[1]);

                $jabatan = Jabatan::where('pegawais_id', $pegawaiUser->id)
                    ->where('nama_jabatan', $targetRole)
                    ->where('status', 1)
                    ->first();

                if ($jabatan) {
                    Auth::guard('pegawai')->logout();
                    Auth::guard('jabatan')->login($jabatan);

                    $jabatansList = Jabatan::where('pegawais_id', $pegawaiUser->id)
                        ->where('status', 1)
                        ->pluck('nama_jabatan')
                        ->toArray();

                    session(['user' => [
                        'id' => $jabatan->id,
                        'kelasId' => $jabatan->kelas_id ?? null,
                        'nama' => $jabatan->nama ?? $jabatan->nama_lengkap ?? $jabatan->name,
                        'role' => $targetRole,
                        'jabatans' => $jabatansList,
                        'wadir' => $jabatan->no ?? null,
                        'prodiId' => $jabatan->prodis_id ?? null,
                        'prodiIds' => [],
                        'activeProdiId' => null,
                        'email' => $jabatan->email,
                        'status_pa' => 0,
                    ]]);

                    return redirect($request->fullUrl());
                }
            }
        }

        if (Auth::guard('jabatan')->check()) {
            /** @var Jabatan $jabatanUser */
            $jabatanUser = Auth::guard('jabatan')->user();

            if (preg_match('/^v2\/dosen(\/|$)/i', $path) && ! preg_match('/^v2\/dosen\/dashboard(\/|$)/i', $path) && $jabatanUser->dosens_id) {
                $dosen = Dosen::find($jabatanUser->dosens_id);

                if ($dosen) {
                    Auth::guard('jabatan')->logout();
                    Auth::guard('dosen')->login($dosen);

                    $jabatansList = Jabatan::where('dosens_id', $dosen->id)
                        ->where('status', 1)
                        ->pluck('nama_jabatan')
                        ->toArray();

                    session(['user' => [
                        'id' => $dosen->id,
                        'kelasId' => $dosen->kelas_id ?? null,
                        'nama' => $dosen->nama ?? $dosen->nama_lengkap ?? $dosen->name,
                        'role' => 'dosen',
                        'jabatans' => $jabatansList,
                        'wadir' => null,
                        'prodiId' => $dosen->prodis_id ?? null,
                        'prodiIds' => [],
                        'activeProdiId' => null,
                        'email' => $dosen->email,
                        'status_pa' => $dosen->pembimbing_akademik ?? 0,
                    ]]);

                    return redirect($request->fullUrl());
                }
            }

            if (preg_match('/^v2\/pegawai(\/|$)/i', $path) && ! preg_match('/^v2\/pegawai\/dashboard(\/|$)/i', $path) && $jabatanUser->pegawais_id) {
                $pegawai = Pegawai::find($jabatanUser->pegawais_id);

                if ($pegawai) {
                    Auth::guard('jabatan')->logout();
                    Auth::guard('pegawai')->login($pegawai);

                    $jabatansList = Jabatan::where('pegawais_id', $pegawai->id)
                        ->where('status', 1)
                        ->pluck('nama_jabatan')
                        ->toArray();

                    session(['user' => [
                        'id' => $pegawai->id,
                        'kelasId' => null,
                        'nama' => $pegawai->nama ?? $pegawai->nama_lengkap ?? $pegawai->name,
                        'role' => 'pegawai',
                        'jabatans' => $jabatansList,
                        'wadir' => null,
                        'prodiId' => null,
                        'prodiIds' => [],
                        'activeProdiId' => null,
                        'email' => $pegawai->email,
                        'status_pa' => 0,
                    ]]);

                    return redirect($request->fullUrl());
                }
            }
        }

        return $next($request);
    }
}
