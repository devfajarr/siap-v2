<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureWhatsappIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->whatsapp_verified_at) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tindakan dibatalkan. Nomor WhatsApp Anda belum terverifikasi. Silakan verifikasi nomor Anda di menu Profil terlebih dahulu.',
                ], 403);
            }

            return redirect()->route('v2.profile.edit')->with('error', 'Silakan verifikasi nomor WhatsApp Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
