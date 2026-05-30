<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ForceChangePasswordController extends Controller
{
    /**
     * Tampilkan halaman ganti password paksa.
     */
    public function show(Request $request): RedirectResponse|InertiaResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->is_first_login) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/ForceChangePassword');
    }

    /**
     * Proses penggantian password default.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->is_first_login) {
            return redirect()->route('dashboard');
        }

        // Validasi password baru yang kuat
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru harus memiliki minimal 8 karakter.',
        ]);

        // Simpan password baru
        $user->update([
            'password' => Hash::make($request->password),
            'is_first_login' => false,
        ]);

        // Regenerasi session untuk keamanan tambahan (session fixation)
        $request->session()->regenerate();

        // Redirect pengguna langsung ke dashboard (Auto-Login / Seamless transition)
        $role = session('user.role');
        $roleRedirects = [
            'admin' => 'v2.admin.dashboard',
            'mahasiswa' => 'v2.mahasiswa.dashboard',
            'direktur' => 'v2.direktur.dashboard',
            'wakil_direktur' => 'v2.direktur.dashboard',
            'dosen' => 'v2.dosen.dashboard',
            'pegawai' => 'v2.pegawai.dashboard',
            'kaprodi' => 'v2.kaprodi.dashboard',
            'bpmi' => 'v2.pegawai.dashboard',
            'kemahasiswaan' => 'v2.pegawai.dashboard',
            'perpustakaan' => 'v2.pegawai.dashboard',
            'sarpras' => 'v2.pegawai.dashboard',
            'personalia' => 'v2.pegawai.dashboard',
            'orang_tua' => 'v2.orang-tua.dashboard',
        ];

        $redirectRoute = $roleRedirects[$role] ?? 'dashboard';

        return redirect()->route($redirectRoute)->with('success', 'Password default berhasil diubah!');
    }
}
