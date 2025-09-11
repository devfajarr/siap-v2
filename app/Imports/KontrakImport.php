<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class KontrakImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
{
    $data = [];

    foreach ($rows as $index => $row) {
        if ($index === 0 && strtoupper($row[0]) === 'PERTEMUAN') {
            continue;
        }

        $data[] = [
            'pertemuan' => (int) $row[0],
            'materi' => $row[1] ?? null,
            'pustaka' => $row[2] ?? null,
        ];
    }

    return $data;
}
}
