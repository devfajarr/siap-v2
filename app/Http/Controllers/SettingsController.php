<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function getDailySchedule()
    {
        $status = Settings::where('key', 'daily_schedule')->value('value') ?? 0;

        return response()->json(['status' => (int) $status]);
    }

    public function toggleDailySchedule(Request $request)
    {
        $status = $request->input('status');

        Settings::updateOrInsert(
            ['key' => 'daily_schedule'],
            ['value' => $status]
        );

        return response()->json(['message' => 'Status jadwal harian diperbarui!', 'status' => $status]);
    }

    public function toggleExamCardPeriod(Request $request): JsonResponse
    {
        $request->validate([
            'jenis' => 'required|in:uts,uas',
            'status' => 'required|boolean',
        ]);

        $key = 'buka_kartu_'.$request->jenis;
        $status = $request->input('status');

        Settings::updateOrInsert(
            ['key' => $key],
            ['value' => $status]
        );

        return response()->json([
            'message' => 'Periode kartu ujian '.strtoupper($request->jenis).' berhasil diperbarui!',
            'status' => (int) $status,
        ]);
    }
}
