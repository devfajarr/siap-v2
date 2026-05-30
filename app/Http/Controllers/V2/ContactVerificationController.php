<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappOtpJob;
use App\Models\ContactVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ContactVerificationController extends Controller
{
    /**
     * Resolve the currently authenticated user across all guards.
     */
    protected function resolveUser(): mixed
    {
        foreach (['admin', 'dosen', 'mahasiswa', 'kaprodi', 'direktur', 'wakil_direktur', 'pegawai', 'jabatan'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }

        return null;
    }

    /**
     * Mengirim kode OTP ke nomor WhatsApp pengguna.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'no_telephone' => 'required|string|min:9|max:15',
        ], [
            'no_telephone.required' => 'Nomor WhatsApp wajib diisi.',
            'no_telephone.min' => 'Nomor WhatsApp minimal 9 digit.',
            'no_telephone.max' => 'Nomor WhatsApp maksimal 15 digit.',
        ]);

        $user = $this->resolveUser();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.',
            ], 401);
        }

        // Terapkan rate limiter: maksimal 5 kali per menit
        $key = 'send-otp:'.$user->id.':'.get_class($user);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak permintaan OTP. Silakan tunggu {$seconds} detik lagi.",
            ], 429);
        }

        RateLimiter::hit($key, 60);

        // Generate 6-digit random OTP
        $code = (string) random_int(100000, 999999);

        // Hapus kode OTP lama untuk user ini
        ContactVerification::where('verifiable_id', $user->id)
            ->where('verifiable_type', get_class($user))
            ->where('type', 'whatsapp')
            ->delete();

        // Simpan OTP baru ke database (hashed)
        ContactVerification::create([
            'verifiable_id' => $user->id,
            'verifiable_type' => get_class($user),
            'type' => 'whatsapp',
            'contact' => $request->no_telephone,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
        ]);

        // Dispatch job kirim WhatsApp asinkronus
        SendWhatsappOtpJob::dispatch($request->no_telephone, $code);

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil dikirim ke WhatsApp Anda.',
        ]);
    }

    /**
     * Memverifikasi kode OTP yang dimasukkan pengguna.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'Kode OTP wajib diisi.',
            'code.size' => 'Kode OTP harus tepat 6 digit.',
        ]);

        $user = $this->resolveUser();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.',
            ], 401);
        }

        // Cari OTP di database
        $verification = ContactVerification::where('verifiable_id', $user->id)
            ->where('verifiable_type', get_class($user))
            ->where('type', 'whatsapp')
            ->first();

        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan OTP tidak ditemukan atau telah kadaluwarsa.',
            ], 400);
        }

        // Periksa apakah sudah kadaluwarsa
        if ($verification->expires_at->isPast()) {
            $verification->delete();

            return response()->json([
                'success' => false,
                'message' => 'Kode OTP telah kadaluwarsa. Silakan minta kode baru.',
            ], 400);
        }

        // Cocokkan kode
        if (! Hash::check($request->code, $verification->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP yang dimasukkan salah.',
            ], 400);
        }

        // Jika berhasil, perbarui nomor telepon di model user dan tandai terverifikasi
        $user->update([
            'no_telephone' => $verification->contact,
            'whatsapp_verified_at' => now(),
        ]);

        // Hapus record OTP dari database
        $verification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil diverifikasi!',
            'whatsapp_verified_at' => $user->whatsapp_verified_at ? $user->whatsapp_verified_at->toIso8601String() : null,
            'no_telephone' => $user->no_telephone,
        ]);
    }
}
