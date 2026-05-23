<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Pegawai;
use App\Models\Kaprodi;
use App\Models\Wadir;
use App\Models\Direktur;
use App\Models\Prodi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademicStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed 10 Dosen
        $dosens = [];
        for ($i = 1; $i <= 10; $i++) {
            $dosens[$i] = Dosen::updateOrCreate(
                ['email' => "dosen.seeder.{$i}@email.com"],
                [
                    'nama' => "Dosen Seeder {$i}",
                    'nidn' => "9900100" . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'jenis_kelamin' => $i % 2 === 0 ? 'Perempuan' : 'Laki - Laki',
                    'pembimbing_akademik' => 1,
                    'no_telephone' => "0891234567" . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'agama' => 'islam',
                    'status' => 1,
                    'tanggal_lahir' => '1980-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'tempat_lahir' => 'Kota Seeder',
                    'password' => Hash::make('password')
                ]
            );
        }

        // 2. Seed 10 Pegawai
        for ($i = 1; $i <= 10; $i++) {
            Pegawai::updateOrCreate(
                ['email' => "pegawai.seeder.{$i}@email.com"],
                [
                    'nama' => "Pegawai Seeder {$i}",
                    'nuptk' => "8800100" . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'jenis_kelamin' => $i % 2 === 0 ? 'Perempuan' : 'Laki-laki',
                    'no_telephone' => "0881234567" . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'agama' => 'islam',
                    'status' => 1,
                    'tanggal_lahir' => '1985-05-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'tempat_lahir' => 'Kota Seeder',
                    'password' => Hash::make('password'),
                    'is_first_login' => false
                ]
            );
        }

        // 3. Map Dosen 1 s.d 3 ke Kaprodi
        $prodiTI = Prodi::where('singkatan', 'TI')->first();
        $prodiAK = Prodi::where('singkatan', 'AK')->first();
        $prodiAB = Prodi::where('singkatan', 'AB')->first();

        $kaprodiMappings = [
            1 => $prodiTI,
            2 => $prodiAK,
            3 => $prodiAB,
        ];

        foreach ($kaprodiMappings as $dosenIndex => $prodi) {
            if ($prodi) {
                $dosen = $dosens[$dosenIndex];
                $kaprodi = Kaprodi::updateOrCreate(
                    ['dosens_id' => $dosen->id],
                    [
                        'nama' => $dosen->nama,
                        'no_telephone' => $dosen->no_telephone,
                        'email' => $dosen->email,
                        'status' => 1,
                        'password' => $dosen->password,
                        'is_first_login' => false
                    ]
                );
                $kaprodi->prodis()->sync([$prodi->id]);
            }
        }

        // 4. Map Dosen 4 ke Wadir
        $dosenWadir = $dosens[4];
        Wadir::updateOrCreate(
            ['dosens_id' => $dosenWadir->id],
            [
                'nama' => $dosenWadir->nama,
                'no' => '1',
                'no_telephone' => $dosenWadir->no_telephone,
                'status' => 1,
                'email' => $dosenWadir->email,
                'password' => $dosenWadir->password,
                'is_first_login' => false
            ]
        );

        // 5. Map Dosen 5 ke Direktur
        $dosenDirektur = $dosens[5];
        Direktur::updateOrCreate(
            ['dosens_id' => $dosenDirektur->id],
            [
                'nama' => $dosenDirektur->nama,
                'no_telephone' => $dosenDirektur->no_telephone,
                'status' => 1,
                'email' => $dosenDirektur->email,
                'password' => $dosenDirektur->password,
                'is_first_login' => false
            ]
        );
    }
}
