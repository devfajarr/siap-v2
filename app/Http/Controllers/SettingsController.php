<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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
}
