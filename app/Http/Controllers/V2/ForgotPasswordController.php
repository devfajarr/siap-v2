<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappResetPasswordJob;
use App\Models\Admin;
use App\Models\ContactVerification;
use App\Models\Direktur;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\Kaprodi;
use App\Models\Mahasiswa;
use App\Models\OrangTua;
use App\Models\Pegawai;
use App\Models\Wadir;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilkan halaman pemulihan password.
     */
    public function show(Request $request): InertiaResponse
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Mengirimkan kode OTP pemulihan password ke WhatsApp pengguna.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'role' => 'required|string',
        ], [
            'username.required' => 'Username atau NIM wajib diisi.',
            'role.required' => 'Level pengguna wajib dipilih.',
        ]);

        $user = $this->resolveUser($request->username, $request->role);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna dengan level tersebut tidak ditemukan di sistem.',
            ], 404);
        }

        if (empty($user->no_telephone)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp belum terdaftar di sistem. Silakan hubungi admin akademik untuk melakukan reset password.',
            ], 400);
        }

        $limiterKey = 'forgot-password-otp:'.$user->id.':'.get_class($user);
        if (RateLimiter::tooManyAttempts($limiterKey, 3)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak permintaan OTP. Silakan coba lagi dalam {$seconds} detik.",
            ], 429);
        }
        RateLimiter::hit($limiterKey, 300);

        $code = (string) random_int(100000, 999999);

        ContactVerification::where('verifiable_id', $user->id)
            ->where('verifiable_type', get_class($user))
            ->where('type', 'reset_password')
            ->delete();

        ContactVerification::create([
            'verifiable_id' => $user->id,
            'verifiable_type' => get_class($user),
            'type' => 'reset_password',
            'contact' => $user->no_telephone,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
        ]);

        SendWhatsappResetPasswordJob::dispatch($user->no_telephone, $code);

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP pemulihan berhasil dikirim ke nomor WhatsApp Anda.',
            'masked_phone' => $this->maskPhoneNumber($user->no_telephone),
        ]);
    }

    /**
     * Memverifikasi OTP dan mengatur password baru pengguna.
     */
    public function verifyAndReset(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'role' => 'required|string',
            'code' => 'required|string|size:6',
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
            'username.required' => 'Username atau NIM wajib diisi.',
            'role.required' => 'Level pengguna wajib dipilih.',
            'code.required' => 'Kode OTP wajib diisi.',
            'code.size' => 'Kode OTP harus 6 digit.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru harus memiliki minimal 8 karakter.',
        ]);

        $user = $this->resolveUser($request->username, $request->role);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        $verification = ContactVerification::where('verifiable_id', $user->id)
            ->where('verifiable_type', get_class($user))
            ->where('type', 'reset_password')
            ->first();

        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak ditemukan atau telah kadaluwarsa. Silakan minta kode baru.',
            ], 400);
        }

        if ($verification->expires_at->isPast()) {
            $verification->delete();

            return response()->json([
                'success' => false,
                'message' => 'Kode OTP telah kadaluwarsa. Silakan minta kode baru.',
            ], 400);
        }

        if (! Hash::check($request->code, $verification->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP yang dimasukkan salah.',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'is_first_login' => false,
        ]);

        $verification->delete();

        $limiterKey = 'forgot-password-otp:'.$user->id.':'.get_class($user);
        RateLimiter::clear($limiterKey);

        $request->session()->flash('success', 'Kata sandi berhasil disetel ulang. Silakan login menggunakan kata sandi baru Anda.');

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diubah.',
        ]);
    }

    /**
     * Resolves the user based on username and role across guards.
     */
    protected function resolveUser(string $username, string $role): mixed
    {
        if ($role === 'admin') {
            return Admin::where('email', $username)->first();
        } elseif ($role === 'direktur') {
            return Direktur::where('email', $username)->first();
        } elseif ($role === 'wakil_direktur') {
            return Wadir::where('email', $username)->first();
        } elseif ($role === 'kaprodi') {
            return Kaprodi::where('email', $username)->first();
        } elseif ($role === 'mahasiswa') {
            return Mahasiswa::where('nim', $username)->first();
        } elseif ($role === 'orang_tua') {
            return OrangTua::where('username', $username)->first();
        } elseif ($role === 'dosen') {
            return Dosen::where('email', $username)->first();
        } elseif ($role === 'pegawai') {
            return Pegawai::where('email', $username)->first();
        } elseif (in_array($role, ['bpmi', 'kemahasiswaan', 'perpustakaan', 'sarpras', 'personalia'])) {
            return Jabatan::where('email', $username)
                ->where('nama_jabatan', $role)
                ->where('status', 1)
                ->first();
        }

        return null;
    }

    /**
     * Sensor nomor telepon untuk alasan privasi/keamanan.
     */
    protected function maskPhoneNumber(string $phone): string
    {
        $len = strlen($phone);
        if ($len <= 7) {
            return str_repeat('*', $len);
        }

        return substr($phone, 0, 4).str_repeat('*', $len - 7).substr($phone, -3);
    }
}
