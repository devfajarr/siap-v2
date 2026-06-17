<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\FeederSyncJob;
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

    /**
     * Pull study programs from Neo Feeder.
     */
    public function pullProdis(): JsonResponse
    {
        FeederSyncJob::dispatch('pull-prodis');

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi program studi telah dimulai di latar belakang.',
        ]);
    }

    /**
     * Pull lecturers from Neo Feeder.
     */
    public function pullDosens(): JsonResponse
    {
        FeederSyncJob::dispatch('pull-dosens');

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi penugasan dosen telah dimulai di latar belakang.',
        ]);
    }

    /**
     * Pull courses from Neo Feeder.
     */
    public function pullMatkuls(): JsonResponse
    {
        FeederSyncJob::dispatch('pull-matkuls');

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi mata kuliah telah dimulai di latar belakang.',
        ]);
    }

    /**
     * Pull students from Neo Feeder.
     */
    public function pullMahasiswas(): JsonResponse
    {
        FeederSyncJob::dispatch('pull-mahasiswas');

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi data riwayat mahasiswa telah dimulai di latar belakang.',
        ]);
    }

    /**
     * Push a single student to Neo Feeder.
     */
    public function pushMahasiswa(int $id): JsonResponse
    {
        FeederSyncJob::dispatch('push-mahasiswa', ['mahasiswa_id' => $id]);

        return response()->json([
            'success' => true,
            'message' => 'Kirim data mahasiswa ke Feeder telah dimulai di latar belakang.',
        ]);
    }

    /**
     * Push all active students of a class to Neo Feeder.
     */
    public function pushMahasiswaKelas(int $kelas_id): JsonResponse
    {
        FeederSyncJob::dispatch('push-mahasiswa-kelas', ['kelas_id' => $kelas_id]);

        return response()->json([
            'success' => true,
            'message' => 'Kirim data mahasiswa rombel telah dimulai di latar belakang.',
        ]);
    }
}
