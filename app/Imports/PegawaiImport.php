<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Pegawai;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;

class PegawaiImport implements ToModel
{
    public function model(array $row)
    {
        if (!isset($row[0]) || strtoupper(trim($row[1])) == 'NUPTK') {
            return null;
        }

        return new Pegawai([
            'nama'                => $row[0],
            'nuptk'                => $row[1] ?? null,
            'jenis_kelamin'       => ($row[2] == 'L' ) ? 'Laki-Laki' :'Perempuan',
            'no_telephone'        => $row[3],
            'agama'               => $row[4],
            'tanggal_lahir'       => Carbon::parse($row[5])->format('Y-m-d'),
            'tempat_lahir'        => $row[6],
            'email'               => $row[7],
            'password'            => bcrypt($row[8]),
            'is_first_login'      => true,
            'status'              => 1
        ]);
    }
}
