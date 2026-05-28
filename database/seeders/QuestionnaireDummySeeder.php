<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Matkul;
use App\Models\Prodi;
use App\Models\Questionnaire;
use App\Models\Semester;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class QuestionnaireDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::first();

        // 1. Buat kuisioner Kinerja Pengajar
        $questionnaire = Questionnaire::create([
            'title' => 'Evaluasi Kinerja Dosen Pengajar Semester Ganjil 2025/2026',
            'description' => "Silakan berikan penilaian objektif untuk kinerja dosen pengajar Anda pada semester ini demi meningkatkan mutu proses belajar mengajar.\n\nKerahasiaan identitas Anda dijamin sepenuhnya anonim.",
            'type' => 'kinerja_pengajar',
            'status' => 'published',
            'target_respondent' => 'mahasiswa',
            'created_by_id' => $admin ? $admin->id : null,
            'created_by_type' => $admin ? get_class($admin) : null,
        ]);

        // 2. Buat Seksi
        $section = $questionnaire->sections()->create([
            'title' => 'Bagian A: Kompetensi Pedagogik & Profesional Dosen',
            'description' => 'Evaluasi aspek kemampuan pengajaran, penguasaan materi, serta kedisiplinan dosen.',
            'order' => 0,
        ]);

        // 3. Buat Pertanyaan
        $options = ['Sangat Baik', 'Baik', 'Cukup', 'Kurang', 'Sangat Kurang'];

        $section->questions()->create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Dosen menyampaikan materi kuliah secara sistematis dan jelas serta mudah dipahami.',
            'question_type' => 'radio',
            'options' => $options,
            'is_required' => true,
            'order' => 0,
        ]);

        $section->questions()->create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Kemampuan dosen dalam menguasai topik bahasan perkuliahan dan menjawab pertanyaan mahasiswa.',
            'question_type' => 'radio',
            'options' => $options,
            'is_required' => true,
            'order' => 1,
        ]);

        $section->questions()->create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Dosen datang tepat waktu dan memenuhi persentase kehadiran tatap muka kelas (minimal 75%).',
            'question_type' => 'radio',
            'options' => $options,
            'is_required' => true,
            'order' => 2,
        ]);

        $section->questions()->create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Kemampuan dosen dalam memanfaatkan media interaktif atau teknologi SIAP selama perkuliahan.',
            'question_type' => 'radio',
            'options' => $options,
            'is_required' => true,
            'order' => 3,
        ]);

        $section->questions()->create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Saran, masukan, atau kritik konstruktif untuk perbaikan dosen pengajar terkait.',
            'question_type' => 'paragraph',
            'options' => null,
            'is_required' => false,
            'order' => 4,
        ]);

        // 4. Buat kuisioner Pelayanan (Target: mahasiswa)
        $kuisPelayanan = Questionnaire::create([
            'title' => 'Kuisioner Kepuasan Pelayanan Akademik & Sarana Prasarana',
            'description' => "Evaluasi kepuasan mahasiswa terhadap pelayanan administrasi akademik, kebersihan ruang kelas, dan sarana perkuliahan.\n\nUmpan balik Anda sangat berharga bagi peningkatan fasilitas kampus.",
            'type' => 'pelayanan',
            'status' => 'published',
            'target_respondent' => 'mahasiswa',
            'created_by_id' => $admin ? $admin->id : null,
            'created_by_type' => $admin ? get_class($admin) : null,
        ]);

        $secPelayanan = $kuisPelayanan->sections()->create([
            'title' => 'Aspek Sarana & Pelayanan',
            'description' => 'Berikan penilaian jujur mengenai fasilitas fisik dan administrasi.',
            'order' => 0,
        ]);

        $optsPelayanan = ['Sangat Puas', 'Puas', 'Cukup Puas', 'Kurang Puas'];

        $secPelayanan->questions()->create([
            'questionnaire_id' => $kuisPelayanan->id,
            'question_text' => 'Bagaimana kebersihan, pencahayaan, dan kenyamanan fasilitas ruang kelas (AC, LCD Projector)?',
            'question_type' => 'radio',
            'options' => $optsPelayanan,
            'is_required' => true,
            'order' => 0,
        ]);

        $secPelayanan->questions()->create([
            'questionnaire_id' => $kuisPelayanan->id,
            'question_text' => 'Bagaimana keramahan, kecepatan, dan ketepatan staf administrasi (BAAK) dalam melayani keperluan akademik?',
            'question_type' => 'radio',
            'options' => $optsPelayanan,
            'is_required' => true,
            'order' => 1,
        ]);

        $secPelayanan->questions()->create([
            'questionnaire_id' => $kuisPelayanan->id,
            'question_text' => 'Tuliskan kritik, saran, atau masukan untuk perbaikan sarana prasarana kampus.',
            'question_type' => 'paragraph',
            'options' => null,
            'is_required' => false,
            'order' => 2,
        ]);

        // 5. Buat kuisioner AMI (Target: dosen & pegawai)
        $kuisAmi = Questionnaire::create([
            'title' => 'Kuisioner Audit Mutu Internal (AMI) Prodi & Tatap Pamong 2025/2026',
            'description' => 'Digunakan untuk mengevaluasi implementasi kurikulum, efektivitas penjaminan mutu program studi, dan kepuasan tata pamong bagi Dosen dan Pegawai.',
            'type' => 'ami',
            'status' => 'published',
            'target_respondent' => 'dosen_pegawai',
            'created_by_id' => $admin ? $admin->id : null,
            'created_by_type' => $admin ? get_class($admin) : null,
        ]);

        $secAmi = $kuisAmi->sections()->create([
            'title' => 'Aspek Penjaminan Mutu & Kurikulum',
            'description' => 'Evaluasi tata pamong dan mutu kurikulum tingkat program studi.',
            'order' => 0,
        ]);

        $optsAmi = ['Sangat Setuju', 'Setuju', 'Kurang Setuju', 'Sangat Tidak Setuju'];

        $secAmi->questions()->create([
            'questionnaire_id' => $kuisAmi->id,
            'question_text' => 'Penyusunan kurikulum program studi telah melibatkan stakeholders industri dan alumni secara relevan.',
            'question_type' => 'radio',
            'options' => $optsAmi,
            'is_required' => true,
            'order' => 0,
        ]);

        $secAmi->questions()->create([
            'questionnaire_id' => $kuisAmi->id,
            'question_text' => 'Ketersediaan laboratorium dan peralatan pendukung praktikum sudah memadai untuk mendukung proses pembelajaran.',
            'question_type' => 'radio',
            'options' => $optsAmi,
            'is_required' => true,
            'order' => 1,
        ]);

        $secAmi->questions()->create([
            'questionnaire_id' => $kuisAmi->id,
            'question_text' => 'Tuliskan masukan atau saran evaluasi kurikulum prodi.',
            'question_type' => 'paragraph',
            'options' => null,
            'is_required' => false,
            'order' => 2,
        ]);

        // 6. Pastikan ada data akademik dummy agar mahasiswa dapat melakukan pengisian
        $this->ensureAcademicDataExists();
    }

    /**
     * Memastikan data Dosen, Prodi, Semester, Kelas, Matkul dan Jadwal tersedia.
     */
    protected function ensureAcademicDataExists(): void
    {
        // Pastikan ada Dosen
        if (Dosen::count() === 0) {
            Dosen::create([
                'nama' => 'Dr. Budi Utomo, M.T.',
                'pembimbing_akademik' => 1,
                'jenis_kelamin' => 'L',
                'no_telephone' => '081234567890',
                'agama' => 'Islam',
                'status' => 1,
                'tanggal_lahir' => '1982-04-12',
                'tempat_lahir' => 'Purworejo',
                'email' => 'budi.utomo@polsa.ac.id',
                'password' => Hash::make('password'),
            ]);

            Dosen::create([
                'nama' => 'Rini Lestari, M.Kom.',
                'pembimbing_akademik' => 0,
                'jenis_kelamin' => 'P',
                'no_telephone' => '089876543210',
                'agama' => 'Kristen',
                'status' => 1,
                'tanggal_lahir' => '1988-08-20',
                'tempat_lahir' => 'Sleman',
                'email' => 'rini.lestari@polsa.ac.id',
                'password' => Hash::make('password'),
            ]);
        }

        // Pastikan ada Semester Aktif
        $semester = Semester::where('status', 1)->first();
        if (! $semester) {
            $semester = Semester::create([
                'semester' => 4,
                'status' => 1,
            ]);
        }

        // Pastikan ada Prodi
        $prodi = Prodi::first();
        if (! $prodi) {
            $prodi = Prodi::create([
                'nama_prodi' => 'Teknik Informatika',
                'jenjang' => 'D3',
                'status' => 1,
            ]);
        }

        // Pastikan ada Kelas yang dikaitkan ke semester aktif & prodi
        $kelas = Kelas::first();
        if (! $kelas) {
            $kelas = Kelas::create([
                'nama_kelas' => 'TI-2A',
                'id_semester' => $semester->id,
                'id_prodi' => $prodi->id,
            ]);
        } else {
            // Pastikan semester kelas tsb berstatus aktif untuk simulasi
            $kelas->update([
                'id_semester' => $semester->id,
                'id_prodi' => $prodi->id,
            ]);
        }

        // Pastikan ada Mahasiswa
        $mahasiswa = Mahasiswa::first();
        if ($mahasiswa) {
            $mahasiswa->update(['kelas_id' => $kelas->id]);
        }

        // Pastikan ada Matkul
        if (Matkul::count() === 0) {
            Matkul::create([
                'prodi_id' => $prodi->id,
                'semester_id' => $semester->id,
                'nama_matkul' => 'Pemrograman Berorientasi Objek',
                'alias' => 'PBO',
                'kode' => 'TI302',
            ]);

            Matkul::create([
                'prodi_id' => $prodi->id,
                'semester_id' => $semester->id,
                'nama_matkul' => 'Riset Operasi',
                'alias' => 'RO',
                'kode' => 'TI304',
            ]);
        }

        // Pastikan ada Jadwal Mengajar untuk Kelas ini
        if (Jadwal::where('kelas_id', $kelas->id)->count() === 0) {
            $dosenList = Dosen::take(2)->get();
            $matkulList = Matkul::take(2)->get();

            if ($dosenList->count() >= 2 && $matkulList->count() >= 2) {
                Jadwal::create([
                    'dosens_id' => $dosenList[0]->id,
                    'matkuls_id' => $matkulList[0]->id,
                    'kelas_id' => $kelas->id,
                    'ruangans_id' => 1,
                    'waktu_mulai' => '08:00',
                    'waktu_selesai' => '10:00',
                    'hari' => 'Senin',
                    'tahun' => '2026',
                ]);

                Jadwal::create([
                    'dosens_id' => $dosenList[1]->id,
                    'matkuls_id' => $matkulList[1]->id,
                    'kelas_id' => $kelas->id,
                    'ruangans_id' => 2,
                    'waktu_mulai' => '10:15',
                    'waktu_selesai' => '12:15',
                    'hari' => 'Rabu',
                    'tahun' => '2026',
                ]);
            }
        }
    }
}
