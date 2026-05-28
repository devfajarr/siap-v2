<?php

namespace App\Http\Controllers\V2\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the Pegawai dashboard.
     */
    public function index(): Response
    {
        $user = Auth::guard('pegawai')->user();
        if (! $user && Auth::guard('jabatan')->check()) {
            $user = Auth::guard('jabatan')->user()->pegawai;
        }

        return Inertia::render('Pegawai/Dashboard', [
            'user' => $user,
            'stats' => [
                'totalTugas' => 0,
                'totalNotifikasi' => 0,
            ],
        ]);
    }
}
