<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            if (isset($user->is_first_login) && $user->is_first_login) {
                // Kecualikan rute ganti password, rute logout, debug tools, dsb.
                $except = [
                    'v2/force-change-password',
                    'logout',
                    '_boost/*',
                    '_ignition/*',
                ];

                if (! $request->is($except)) {
                    return redirect()->route('v2.force-change-password');
                }
            }
        }

        return $next($request);
    }
}
