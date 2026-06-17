<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeederWilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeederSyncController extends Controller
{
    /**
     * Search administrative regions (Kecamatan) for autocomplete field.
     */
    public function searchWilayah(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));

        if (empty($q)) {
            return response()->json([]);
        }

        $wilayahs = FeederWilayah::query()
            ->with(['parent.parent'])
            ->where('id_level_wilayah', 3)
            ->where('nama_wilayah', 'like', "%{$q}%")
            ->limit(20)
            ->get();

        $results = $wilayahs->map(function (FeederWilayah $w): array {
            $kabName = $w->parent ? $w->parent->nama_wilayah : null;
            $provName = ($w->parent && $w->parent->parent) ? $w->parent->parent->nama_wilayah : null;

            $label = $w->nama_wilayah;
            if ($kabName) {
                $label .= ", {$kabName}";
            }
            if ($provName) {
                $label .= ", {$provName}";
            }

            return [
                'id_wilayah' => $w->id_wilayah,
                'nama_wilayah' => $w->nama_wilayah,
                'label' => $label,
            ];
        });

        return response()->json($results);
    }
}
