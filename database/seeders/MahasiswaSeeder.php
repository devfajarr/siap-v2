<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder untuk mengisi data dummy mahasiswa.
 *
 * Seeder ini membuat 10 data dummy mahasiswa yang didistribusikan
 * ke 3 program studi utama melalui relasi kelas masing-masing.
 */
class MahasiswaSeeder extends Seeder
{
    /**
     * Menjalankan proses seeding data mahasiswa.
     */
    public function run(): void
    {
        $kelasTI = Kelas::where('nama_kelas', 'TI 1A')->orWhere('nama_kelas', 'TI1A')->first()
            ?? Kelas::create([
                'kode_kelas' => '22222',
                'nama_kelas' => 'TI 1A',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => 1,
                'id_semester' => 1,
            ]);

        $kelasAK = Kelas::where('nama_kelas', 'AK 1A')->orWhere('nama_kelas', 'AK1A')->first()
            ?? Kelas::create([
                'kode_kelas' => '33333',
                'nama_kelas' => 'AK 1A',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => 2,
                'id_semester' => 1,
            ]);

        $kelasAB = Kelas::where('nama_kelas', 'AB 1A')->orWhere('nama_kelas', 'AB1A')->first()
            ?? Kelas::create([
                'kode_kelas' => '44444',
                'nama_kelas' => 'AB 1A',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => 3,
                'id_semester' => 1,
            ]);

        $dosenIds = Dosen::pluck('id')->toArray();
        $defaultPassword = Hash::make('password');

        $studentsData = [
            [
                'nama' => 'Aditya Pratama',
                'nim' => '241010001',
                'email' => 'aditya.pratama@siap.test',
                'gender' => 'Laki-Laki',
                'ibu' => 'Siti Aminah',
                'kelas' => $kelasTI,
            ],
            [
                'nama' => 'Bima Sakti',
                'nim' => '241010002',
                'email' => 'bima.sakti@siap.test',
                'gender' => 'Laki-Laki',
                'ibu' => 'Sri Wahyuni',
                'kelas' => $kelasTI,
            ],
            [
                'nama' => 'Citra Lestari',
                'nim' => '241010003',
                'email' => 'citra.lestari@siap.test',
                'gender' => 'Perempuan',
                'ibu' => 'Kartini',
                'kelas' => $kelasTI,
            ],
            [
                'nama' => 'Dian Wijaya',
                'nim' => '241010004',
                'email' => 'dian.wijaya@siap.test',
                'gender' => 'Laki-Laki',
                'ibu' => 'Dewi Sartika',
                'kelas' => $kelasTI,
            ],
            [
                'nama' => 'Elsa Fitriani',
                'nim' => '241020001',
                'email' => 'elsa.fitriani@siap.test',
                'gender' => 'Perempuan',
                'ibu' => 'Sumarni',
                'kelas' => $kelasAK,
            ],
            [
                'nama' => 'Fajar Ramadhan',
                'nim' => '241020002',
                'email' => 'fajar.ramadhan@siap.test',
                'gender' => 'Laki-Laki',
                'ibu' => 'Aminah',
                'kelas' => $kelasAK,
            ],
            [
                'nama' => 'Gita Asri',
                'nim' => '241020003',
                'email' => 'gita.asri@siap.test',
                'gender' => 'Perempuan',
                'ibu' => 'Rahayu',
                'kelas' => $kelasAK,
            ],
            [
                'nama' => 'Hadi Wibowo',
                'nim' => '241030001',
                'email' => 'hadi.wibowo@siap.test',
                'gender' => 'Laki-Laki',
                'ibu' => 'Hartini',
                'kelas' => $kelasAB,
            ],
            [
                'nama' => 'Indah Sari',
                'nim' => '241030002',
                'email' => 'indah.sari@siap.test',
                'gender' => 'Perempuan',
                'ibu' => 'Endang',
                'kelas' => $kelasAB,
            ],
            [
                'nama' => 'Joko Prasetyo',
                'nim' => '241030003',
                'email' => 'joko.prasetyo@siap.test',
                'gender' => 'Laki-Laki',
                'ibu' => 'Purwanti',
                'kelas' => $kelasAB,
            ],
        ];

        foreach ($studentsData as $index => $data) {
            $dosenPembimbingId = count($dosenIds) > 0 ? $dosenIds[$index % count($dosenIds)] : null;
            $kelasId = $data['kelas'] ? $data['kelas']->id : $kelasTI->id;

            $mahasiswa = Mahasiswa::updateOrCreate(
                ['nim' => $data['nim']],
                [
                    'dosen_pembimbing_id' => $dosenPembimbingId,
                    'nama_lengkap' => $data['nama'],
                    'tahun_masuk' => '2024',
                    'nisn' => '00412345'.str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'nik' => '32010112345678'.str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'email' => $data['email'],
                    'password' => $defaultPassword,
                    'alamat' => 'Jl. Pahlawan No. '.($index + 1).', Purworejo',
                    'no_telephone' => '0812345678'.str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'tanggal_lahir' => '2004-05-'.str_pad(($index % 28) + 1, 2, '0', STR_PAD_LEFT),
                    'tempat_lahir' => 'Purworejo',
                    'nama_ibu' => $data['ibu'],
                    'jenis_kelamin' => $data['gender'],
                    'kelas_id' => $kelasId,
                    'status_krs' => 1,
                    'is_first_login' => true,
                ]
            );

            Krs::updateOrCreate(
                ['mahasiswa_id' => $mahasiswa->id],
                [
                    'prodi_id' => $data['kelas'] ? $data['kelas']->id_prodi : 1,
                    'semester_id' => $data['kelas'] ? $data['kelas']->id_semester : 1,
                    'kelas_id' => $kelasId,
                    'status_krs' => 1,
                    'setuju_pa' => 1,
                    'setuju_mahasiswa' => 1,
                    'tahun_ajaran' => '2024/2025',
                ]
            );

            Pembayaran::updateOrCreate(
                [
                    'mahasiswa_id' => $mahasiswa->id,
                    'semester_id' => $data['kelas'] ? $data['kelas']->id_semester : 1,
                ],
                [
                    'prodi_id' => $data['kelas'] ? $data['kelas']->id_prodi : 1,
                    'kelas_id' => $kelasId,
                    'bukti_pembayaran' => 'bukti_pembayaran/dummy.jpg',
                    'status_pembayaran' => 1,
                    'keterangan' => 'Sudah',
                ]
            );
        }
    }
}
