<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\Matkul;
use App\Models\Pegawai;
use App\Models\Prodi;
use App\Models\Ruangan;
use App\Models\Semester;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JadwalUjianDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dapatkan atau buat Tahun Akademik aktif
        $tahun = TahunAkademik::where('status', '1')->first();
        if (! $tahun) {
            $tahun = TahunAkademik::create([
                'tahun_akademik' => '2025/2026',
                'semester' => 'Ganjil',
                'status' => '1',
            ]);
        }
        $tahunAkademikStr = $tahun->tahun_akademik;

        // 2. Dapatkan pengawas potensial (Dosen & Pegawai)
        $dosens = Dosen::all();
        $pegawais = Pegawai::all();

        if ($dosens->isEmpty()) {
            $dosens = collect([
                Dosen::create([
                    'nama' => 'Dr. H. Ahmad Dahlan, M.T.',
                    'nidn' => '0612345678',
                    'pembimbing_akademik' => 0,
                    'jenis_kelamin' => 'L',
                    'no_telephone' => '08123456789',
                    'agama' => 'Islam',
                    'status' => 1,
                    'tanggal_lahir' => '1980-01-01',
                    'tempat_lahir' => 'Purworejo',
                    'email' => 'ahmad.dahlan@test.com',
                    'password' => Hash::make('password'),
                ]),
                Dosen::create([
                    'nama' => 'Siti Aminah, M.Kom.',
                    'nidn' => '0687654321',
                    'pembimbing_akademik' => 0,
                    'jenis_kelamin' => 'P',
                    'no_telephone' => '08876543210',
                    'agama' => 'Islam',
                    'status' => 1,
                    'tanggal_lahir' => '1985-05-05',
                    'tempat_lahir' => 'Purworejo',
                    'email' => 'siti.aminah@test.com',
                    'password' => Hash::make('password'),
                ]),
            ]);
        }

        if ($pegawais->isEmpty()) {
            $pegawais = collect([
                Pegawai::create([
                    'nama' => 'Bambang Triyono, S.Sos.',
                    'nuptk' => '1122334455',
                    'jenis_kelamin' => 'L',
                    'no_telephone' => '08776655443',
                    'agama' => 'Kristen',
                    'status' => 1,
                    'tanggal_lahir' => '1990-05-15',
                    'tempat_lahir' => 'Purworejo',
                    'email' => 'bambang.t@test.com',
                    'password' => Hash::make('password'),
                ]),
                Pegawai::create([
                    'nama' => 'Rina Wijayanti, A.Md.',
                    'nuptk' => '2233445566',
                    'jenis_kelamin' => 'P',
                    'no_telephone' => '08998877665',
                    'agama' => 'Islam',
                    'status' => 1,
                    'tanggal_lahir' => '1992-04-10',
                    'tempat_lahir' => 'Purworejo',
                    'email' => 'rina.w@test.com',
                    'password' => Hash::make('password'),
                ]),
            ]);
        }

        // Gabungkan untuk mempermudah pemilihan acak
        $pengawasPool = collect()->merge($dosens)->merge($pegawais);

        // 3. Dapatkan atau buat Ruangan
        $ruangans = Ruangan::all();
        if ($ruangans->isEmpty()) {
            $ruangans = collect([
                Ruangan::create(['nama' => 'Ruang Lab Komputer 1']),
                Ruangan::create(['nama' => 'Ruang Teori 102']),
                Ruangan::create(['nama' => 'AULA Utama']),
            ]);
        }

        // 4. Dapatkan atau buat Kelas
        $kelas = Kelas::all();
        if ($kelas->isEmpty()) {
            // Butuh Prodi dan Semester
            $prodi = Prodi::first() ?? Prodi::create([
                'nama_prodi' => 'Teknik Informatika',
                'singkatan' => 'TI',
                'kode_prodi' => '55201',
                'jenjang' => 'S1',
                'alias_nama' => 'Informatics Engineering',
                'alias_jenjang' => 'Bachelor',
            ]);

            $semester = Semester::where('status', 1)->first() ?? Semester::create([
                'semester' => 1,
                'status' => 1,
            ]);

            $kelas = collect([
                Kelas::create([
                    'kode_kelas' => 'TI-1A',
                    'nama_kelas' => 'TI-1A',
                    'jenis_kelas' => 'Reguler',
                    'id_prodi' => $prodi->id,
                    'id_semester' => $semester->id,
                ]),
            ]);
        }

        // 5. Dapatkan atau buat Matkul
        $matkuls = Matkul::all();
        if ($matkuls->isEmpty()) {
            $matkuls = collect([
                Matkul::create([
                    'kode' => 'INF-101',
                    'nama_matkul' => 'Algoritma & Pemrograman',
                    'sks' => 3,
                    'semester_id' => $semester->id ?? 1,
                    'prodi_id' => $prodi->id ?? 1,
                ]),
                Matkul::create([
                    'kode' => 'INF-102',
                    'nama_matkul' => 'Matematika Diskrit',
                    'sks' => 3,
                    'semester_id' => $semester->id ?? 1,
                    'prodi_id' => $prodi->id ?? 1,
                ]),
                Matkul::create([
                    'kode' => 'INF-103',
                    'nama_matkul' => 'Basis Data Dasar',
                    'sks' => 4,
                    'semester_id' => $semester->id ?? 1,
                    'prodi_id' => $prodi->id ?? 1,
                ]),
            ]);
        }

        // 6. Pastikan ada Jadwal mengajar (relasi) agar relevan
        $jadwals = Jadwal::all();
        if ($jadwals->isEmpty()) {
            $jadwals = collect();
            foreach ($kelas as $k) {
                foreach ($matkuls as $idx => $m) {
                    $dosen = $dosens->random();
                    $ruangan = $ruangans->random();
                    $jadwals->push(Jadwal::create([
                        'kelas_id' => $k->id,
                        'matkuls_id' => $m->id,
                        'dosens_id' => $dosen->id,
                        'ruangans_id' => $ruangan->id,
                        'hari' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'][$idx % 5],
                        'waktu_mulai' => '08:00:00',
                        'waktu_selesai' => '10:30:00',
                    ]));
                }
            }
        }

        // 7. Hapus jadwal ujian lama untuk isolasi
        JadwalUjian::query()->forceDelete();

        // 8. Buat Jadwal Ujian UTS dan UAS
        $waktuPilihan = [
            ['08:00:00', '09:30:00'],
            ['10:00:00', '11:30:00'],
            ['13:00:00', '14:30:00'],
        ];

        $today = Carbon::today();

        foreach ($jadwals as $index => $j) {
            $pengawasUts = $pengawasPool->random();
            $pengawasUas = $pengawasPool->random();
            $ruangan = $ruangans->random();

            $waktuUts = $waktuPilihan[$index % count($waktuPilihan)];
            $waktuUas = $waktuPilihan[($index + 1) % count($waktuPilihan)];

            // UTS
            JadwalUjian::create([
                'kelas_id' => $j->kelas_id,
                'matkuls_id' => $j->matkuls_id,
                'ruangans_id' => $ruangan->id,
                'pengawas_id' => $pengawasUts->id,
                'pengawas_type' => get_class($pengawasUts),
                'jenis_ujian' => 'uts',
                'tanggal' => $today->copy()->addDays($index + 1)->format('Y-m-d'),
                'waktu_mulai' => $waktuUts[0],
                'waktu_selesai' => $waktuUts[1],
                'tahun' => $tahunAkademikStr,
            ]);

            // UAS
            JadwalUjian::create([
                'kelas_id' => $j->kelas_id,
                'matkuls_id' => $j->matkuls_id,
                'ruangans_id' => $ruangan->id,
                'pengawas_id' => $pengawasUas->id,
                'pengawas_type' => get_class($pengawasUas),
                'jenis_ujian' => 'uas',
                'tanggal' => $today->copy()->addDays($index + 8)->format('Y-m-d'),
                'waktu_mulai' => $waktuUas[0],
                'waktu_selesai' => $waktuUas[1],
                'tahun' => $tahunAkademikStr,
            ]);
        }
    }
}
