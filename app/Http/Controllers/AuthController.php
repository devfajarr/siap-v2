<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Wadir;
use App\Models\Kaprodi;
use App\Models\Direktur;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        $roleRedirects = [
            'admin' => 'v2.admin.dashboard',
            'mahasiswa' => 'v2.mahasiswa.dashboard',
            'direktur' => 'v2.direktur.dashboard',
            'wakil_direktur' => 'v2.direktur.dashboard',
            'dosen' => 'v2.dosen.dashboard',
            'kaprodi' => 'v2.kaprodi.dashboard',
        ];

        foreach (['admin', 'mahasiswa', 'direktur', 'wakil_direktur', 'dosen', 'kaprodi'] as $guard) {
            if (Auth::guard($guard)->check()) {
                $redirectRoute = $roleRedirects[$guard] ?? 'dashboard';
                return redirect()->route($redirectRoute);
            }
        }

        return Inertia::render('Auth/Login');
    }


    public function processLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'role' => 'required'
        ]);
        $role = $request->role;
        $user = null;

        if ($role === 'admin') {
            $user = Admin::where('email', $request->username)->first();
            $guard = 'admin';
        } elseif ($role === 'direktur') {
            $user = Direktur::where('email', $request->username)->first();
            $guard = 'direktur';
        } elseif ($role === 'wakil_direktur') {
            $user = Wadir::where('email', $request->username)->first();
            $guard = 'wakil_direktur';
        } elseif ($role === 'kaprodi') {
            $user = Kaprodi::with('prodis')->where('email', $request->username)->first();
            $guard = 'kaprodi';
        } elseif ($role === 'mahasiswa') {
            $user = Mahasiswa::where('nim', $request->username)->first();
            $guard = 'mahasiswa';
        } elseif ($role === 'dosen') {
            $user = Dosen::where('email', $request->username)->first();
            $guard = 'dosen';
        }
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::guard($guard)->login($user);

            $prodiIds = [];
            $activeProdiId = null;
            if ($role === 'kaprodi') {
                $prodiIds = $user->prodis->pluck('id')->toArray();
                $activeProdiId = $prodiIds[0] ?? null;
            }

            session(['user' => [
                'id' => $user->id,
                'kelasId' => $user->kelas_id,
                'nama' => $user->nama ?? $user->nama_lengkap,
                'role' => $role,
                'wadir' => $user->no,
                'prodiId' => $role === 'kaprodi' ? $activeProdiId : ($user->prodis_id ?? null),
                'prodiIds' => $prodiIds,
                'activeProdiId' => $activeProdiId,
                'email' => $user->email,
                'status_pa' => $user->pembimbing_akademik,
            ]]);
            $roleRedirects = [
                'admin' => 'v2.admin.dashboard',
                'mahasiswa' => 'v2.mahasiswa.dashboard',
                'direktur' => 'v2.direktur.dashboard',
                'wakil_direktur' => 'v2.direktur.dashboard',
                'dosen' => 'v2.dosen.dashboard',
                'kaprodi' => 'v2.kaprodi.dashboard',
            ];
            $redirectRoute = $roleRedirects[$role] ?? 'dashboard';
            return redirect()->route($redirectRoute);
        } else
            return back()->withErrors([
                'username' => 'Username atau password salah',
            ])->withInput($request->only('username', 'role'));
    }

    public function logout(Request $request)
    {
        $role = session('user.role');

        switch ($role) {
            case 'admin':
                Auth::guard('admin')->logout();
                break;
            case 'direktur':
                Auth::guard('direktur')->logout();
                break;
            case 'wakil_direktur':
                Auth::guard('wakil_direktur')->logout();
                break;
            case 'kaprodi':
                Auth::guard('kaprodi')->logout();
                break;
            case 'mahasiswa':
                Auth::guard('mahasiswa')->logout();
                break;
            case 'dosen':
                Auth::guard('dosen')->logout();
                break;
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function processFirstLogin(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                'min:8'
            ]
        ]);

        $user = Auth::user();

        $guardMap = [
            Admin::class => 'admin',
            Direktur::class => 'direktur',
            Wadir::class => 'wakil_direktur',
            Kaprodi::class => 'kaprodi',
            Mahasiswa::class => 'mahasiswa',
            Dosen::class => 'dosen'
        ];

        $guard = $guardMap[get_class($user)] ?? null;

        if (!$guard) {
            return back()->withErrors('Tipe pengguna tidak dikenali');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'is_first_login' => false
        ]);

        Auth::guard($guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Password berhasil diatur. Silakan login kembali.')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');;
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'old_password' => 'required|correct_password',
            'new_password' => 'required|confirmed|min:8',
            'new_password_confirmation' => 'required|same:new_password',
        ], [
            'old_password.required' => 'Password lama harus diisi.',
            'old_password.correct_password' => 'Password lama yang Anda masukkan salah.',
            'new_password.required' => 'Password baru harus diisi.',
            'new_password.confirmed' => 'Password baru dan konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password baru harus memiliki minimal 8 karakter.',
            'new_password_confirmation.required' => 'Konfirmasi password baru harus diisi.',
            'new_password_confirmation.same' => 'Konfirmasi password baru tidak sama dengan password baru.',
        ]);


        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Password lama salah.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        $guardMap = [
            Admin::class => 'admin',
            Direktur::class => 'direktur',
            Wadir::class => 'wakil_direktur',
            Kaprodi::class => 'kaprodi',
            Mahasiswa::class => 'mahasiswa',
            Dosen::class => 'dosen'
        ];

        $guard = $guardMap[get_class($user)] ?? null;

        if (!$guard) {
            return back()->withErrors('Tipe pengguna tidak dikenali');
        }

        $auth = Auth::guard($guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah, silakan login kembali.'
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');


    }
}
