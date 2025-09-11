<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;


class MahasiswaImport implements ToModel
{
    protected $kelasId;

    public function __construct($kelasId) {
        $this->kelasId = $kelasId;
    }

    public function model(array $row)
    {
        if (!isset($row[0]) || $row[0] == 'NIM') {
            return null;
        }

        $alamat = implode(' ', array_filter([
            $row[8],
            'RT ' . $row[9],
            'RW ' . $row[10],
            implode(', ', array_filter([$row[11], $row[12], $row[13], $row[14]]))
        ]));


        return new Mahasiswa([
            'nim' => $row[0],
            'nama_lengkap' => $row[1],
            'tahun_masuk'=> $row[2],
            'tempat_lahir' => $row[3],
            'tanggal_lahir' => \Carbon\Carbon::parse($row[4])->format('Y-m-d'),
            'jenis_kelamin' => ($row[5] == 'L') ? 'Laki-Laki' : (($row[5] == 'P') ? 'Perempuan' : null),
            'nik' => $row[6],
            'nisn' => $row[7],
            'alamat' => $alamat,
            'no_telephone' => $row[15],
            'email' => $row[16],
            'nama_ibu' => $row[17],
            'kelas_id' => $this->kelasId,
            'status_krs' => false,
            'is_first_login' => true,
            'dosen_pembimbing_id' => null,
            'password' => Hash::make($row[0]),
        ]);
    }
}
