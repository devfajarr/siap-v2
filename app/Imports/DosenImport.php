<?php

namespace App\Imports;

use App\Models\Dosen;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;

class DosenImport implements ToModel
{
    public function model(array $row)
    {
        if (!isset($row[0]) || strtoupper(trim($row[1])) == 'NIDN') {
            return null;
        }

        return new Dosen([
            'nama'                => $row[0],
            'nidn'                => $row[1] ?? null,
            'pembimbing_akademik' => ($row[2] == 'Y') ? 1 : 0,
            'jenis_kelamin'       => ($row[3] == 'L' ) ? 'Laki-Laki' :'Perempuan',
            'no_telephone'        => $row[4],
            'agama'               => $row[5],
            'tanggal_lahir'       => Carbon::parse($row[6])->format('Y-m-d'),
            'tempat_lahir'        => $row[7],
            'email'               => $row[8],
            'password'            => bcrypt($row[9]),
            'is_first_login'      => true,
            'status'              => 1
        ]);
    }
}
