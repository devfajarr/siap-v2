<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Matkul;
use App\Models\Prodi;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\Semester;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuestionnaireTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $admin;

    protected Mahasiswa $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Admin
        $this->admin = Admin::create([
            'nama' => 'Admin Kuisioner Test',
            'email' => 'admin-kuisioner@test.com',
            'no_telephone' => '08111222333',
            'password' => Hash::make('password'),
        ]);

        // Setup Mahasiswa
        $this->mahasiswa = Mahasiswa::create([
            'nama_lengkap' => 'Mahasiswa Test Kuisioner',
            'nim' => '22998877',
            'nisn' => '1234567890',
            'nik' => '1234567890123456',
            'jenis_kelamin' => 'L',
            'email' => 'mhs-kuisioner@test.com',
            'no_telephone' => '081222333444',
            'agama' => 'Islam',
            'status' => 1,
            'password' => Hash::make('password'),
            'tanggal_lahir' => '2004-01-01',
            'tempat_lahir' => 'Purworejo',
            'tahun_masuk' => '2022',
            'kelas_id' => 1,
            'alamat' => 'Jl. Test No. 123',
            'nama_ibu' => 'Ibu Test',
        ]);
    }

    /**
     * Test admin can create a new questionnaire.
     */
    public function test_admin_can_create_questionnaire(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post('/v2/admin/kuisioner/pelayanan', [
                'title' => 'Kuis Kepuasan Pelayanan SIAP',
                'description' => 'Mengevaluasi kecepatan server SIAP',
                'status' => 'published',
                'target_respondent' => 'all',
                'sections' => [
                    [
                        'title' => 'Bagian Utama',
                        'description' => 'Silakan isi pertanyaan di bawah ini.',
                        'order' => 0,
                        'questions' => [
                            [
                                'question_text' => 'Bagaimana performa SIAP hari ini?',
                                'question_type' => 'radio',
                                'options' => ['Sangat Baik', 'Cukup', 'Lambat'],
                                'is_required' => true,
                                'order' => 0,
                            ],
                            [
                                'question_text' => 'Tulis saran Anda',
                                'question_type' => 'paragraph',
                                'options' => null,
                                'is_required' => false,
                                'order' => 1,
                            ],
                        ],
                    ],
                ],
            ]);

        $response->assertRedirect('/v2/admin/kuisioner/pelayanan');

        $this->assertDatabaseHas('questionnaires', [
            'title' => 'Kuis Kepuasan Pelayanan SIAP',
            'type' => 'pelayanan',
            'status' => 'published',
        ]);

        $questionnaire = Questionnaire::where('title', 'Kuis Kepuasan Pelayanan SIAP')->first();

        $this->assertDatabaseHas('questionnaire_sections', [
            'questionnaire_id' => $questionnaire->id,
            'title' => 'Bagian Utama',
        ]);

        $this->assertDatabaseHas('questions', [
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Bagaimana performa SIAP hari ini?',
            'question_type' => 'radio',
        ]);
    }

    /**
     * Test respondent can fill and submit responses.
     */
    public function test_respondent_can_submit_answers(): void
    {
        // 1. Create a published questionnaire first
        $questionnaire = Questionnaire::create([
            'title' => 'Evaluasi AMI Tahunan',
            'description' => 'Audit Mutu Internal',
            'type' => 'ami',
            'status' => 'published',
            'target_respondent' => 'all',
            'created_by_id' => $this->admin->id,
            'created_by_type' => get_class($this->admin),
        ]);

        $section = $questionnaire->sections()->create([
            'title' => 'Bagian Utama',
            'description' => 'Silakan isi pertanyaan di bawah ini.',
            'order' => 0,
        ]);

        $question1 = $section->questions()->create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Apakah AMI berjalan lancar?',
            'question_type' => 'radio',
            'options' => ['Ya', 'Tidak'],
            'is_required' => true,
            'order' => 0,
        ]);

        // 2. Submit response as student
        $response = $this->actingAs($this->mahasiswa, 'mahasiswa')
            ->post("/v2/isi-kuisioner/{$questionnaire->id}/submit", [
                'answers' => [
                    $question1->id => 'Ya',
                ],
            ]);

        $response->assertRedirect(route('v2.mahasiswa.kuisioner.index'));

        $this->assertDatabaseHas('questionnaire_responses', [
            'questionnaire_id' => $questionnaire->id,
            'respondent_id' => $this->mahasiswa->id,
            'respondent_type' => get_class($this->mahasiswa),
        ]);

        $qResponse = QuestionnaireResponse::where('questionnaire_id', $questionnaire->id)
            ->where('respondent_id', $this->mahasiswa->id)
            ->first();

        $this->assertDatabaseHas('questionnaire_answers', [
            'response_id' => $qResponse->id,
            'question_id' => $question1->id,
            'answer_value' => 'Ya',
        ]);
    }

    /**
     * Test admin can view analytics and export responses.
     */
    public function test_admin_can_view_analytics_and_export(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Kuis Kinerja Pengajar',
            'description' => 'Evaluasi Dosen Pengajar',
            'type' => 'kinerja_pengajar',
            'status' => 'published',
            'target_respondent' => 'all',
            'created_by_id' => $this->admin->id,
            'created_by_type' => get_class($this->admin),
        ]);

        $section = $questionnaire->sections()->create([
            'title' => 'Bagian Utama',
            'description' => 'Silakan isi pertanyaan di bawah ini.',
            'order' => 0,
        ]);

        $section->questions()->create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Bagaimana performa pengajar?',
            'question_type' => 'radio',
            'options' => ['Baik', 'Buruk'],
            'is_required' => true,
            'order' => 0,
        ]);

        // Get Analytics
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/v2/admin/kuisioner/kinerja-pengajar/{$questionnaire->id}/analytics");

        $response->assertStatus(200);

        // Test Export
        $exportResponse = $this->actingAs($this->admin, 'admin')
            ->get("/v2/admin/kuisioner/kinerja-pengajar/{$questionnaire->id}/export/xlsx");

        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * Test admin can preview questionnaire restricted to Mahasiswa.
     */
    public function test_admin_can_preview_restricted_questionnaire(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Evaluasi Kinerja Dosen',
            'description' => 'Evaluasi Kinerja Dosen oleh Mahasiswa',
            'type' => 'kinerja_pengajar',
            'status' => 'published',
            'target_respondent' => 'mahasiswa',
            'created_by_id' => $this->admin->id,
            'created_by_type' => get_class($this->admin),
        ]);

        $questionnaire->sections()->create([
            'title' => 'Bagian Utama',
            'description' => 'Isian evaluasi.',
            'order' => 0,
        ]);

        // Admin (not a student) should be able to view/preview the form
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/v2/isi-kuisioner/{$questionnaire->id}");

        $response->assertStatus(200);
    }

    /**
     * Test mahasiswa can view list of available questionnaires.
     */
    public function test_mahasiswa_can_view_questionnaires_list(): void
    {
        Questionnaire::create([
            'title' => 'Kuis Pelayanan Mahasiswa',
            'description' => 'Evaluasi fasilitas perkuliahan',
            'type' => 'pelayanan',
            'status' => 'published',
            'target_respondent' => 'mahasiswa',
            'created_by_id' => $this->admin->id,
            'created_by_type' => get_class($this->admin),
        ]);

        $response = $this->actingAs($this->mahasiswa, 'mahasiswa')
            ->get('/v2/mahasiswa/kuisioner');

        $response->assertStatus(200);
    }

    /**
     * Test mahasiswa can submit teacher performance evaluation for a specific lecturer and duplicate is prevented.
     */
    public function test_mahasiswa_can_submit_teacher_evaluation_and_duplicate_is_prevented(): void
    {
        // 1. Create a published teacher evaluation questionnaire
        $questionnaire = Questionnaire::create([
            'title' => 'Evaluasi Kinerja Dosen',
            'description' => 'Evaluasi Dosen oleh Mahasiswa',
            'type' => 'kinerja_pengajar',
            'status' => 'published',
            'target_respondent' => 'mahasiswa',
            'created_by_id' => $this->admin->id,
            'created_by_type' => get_class($this->admin),
        ]);

        $section = $questionnaire->sections()->create([
            'title' => 'Bagian Utama',
            'description' => 'Pertanyaan',
            'order' => 0,
        ]);

        $question1 = $section->questions()->create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Bagaimana dosen mengajar?',
            'question_type' => 'radio',
            'options' => ['Baik', 'Cukup'],
            'is_required' => true,
            'order' => 0,
        ]);

        // 2. Setup Dosen, Kelas, Matkul, and Jadwal
        $dosen = Dosen::create([
            'nama' => 'Dosen Penguji',
            'pembimbing_akademik' => 0,
            'jenis_kelamin' => 'L',
            'no_telephone' => '0812345678',
            'agama' => 'Islam',
            'status' => 1,
            'tanggal_lahir' => '1980-01-01',
            'tempat_lahir' => 'Purworejo',
            'email' => 'dosen-penguji@test.com',
            'password' => Hash::make('password'),
        ]);

        $prodi = Prodi::create([
            'nama_prodi' => 'Teknik Informatika',
            'jenjang' => 'D3',
            'status' => 1,
        ]);

        $semester = Semester::create([
            'semester' => 3,
            'status' => 1, // Active semester
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'TI-3A',
            'id_semester' => $semester->id,
            'id_prodi' => $prodi->id,
        ]);

        // Update student class
        $this->mahasiswa->update([
            'kelas_id' => $kelas->id,
        ]);

        $matkul = Matkul::create([
            'prodi_id' => $prodi->id,
            'semester_id' => $semester->id,
            'nama_matkul' => 'Rekayasa Perangkat Lunak',
            'alias' => 'RPL',
            'kode' => 'RPL101',
        ]);

        $jadwal = Jadwal::create([
            'dosens_id' => $dosen->id,
            'matkuls_id' => $matkul->id,
            'kelas_id' => $kelas->id,
            'ruangans_id' => 1,
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'hari' => 'Senin',
            'tahun' => '2026',
        ]);

        // Check helper initially returns false
        $this->assertFalse($this->mahasiswa->hasCompletedAllTeacherEvaluations());

        // 3. Submit evaluation for this lecturer
        $response = $this->actingAs($this->mahasiswa, 'mahasiswa')
            ->post("/v2/isi-kuisioner/{$questionnaire->id}/submit", [
                'dosen_id' => $dosen->id,
                'matkul_id' => $matkul->id,
                'jadwal_id' => $jadwal->id,
                'answers' => [
                    $question1->id => 'Baik',
                ],
            ]);

        $response->assertRedirect(route('v2.mahasiswa.kuisioner.index'));

        $this->assertDatabaseHas('questionnaire_responses', [
            'questionnaire_id' => $questionnaire->id,
            'respondent_id' => $this->mahasiswa->id,
            'dosen_id' => $dosen->id,
            'matkul_id' => $matkul->id,
            'jadwal_id' => $jadwal->id,
        ]);

        // Now helper should return true
        $this->assertTrue($this->mahasiswa->fresh()->hasCompletedAllTeacherEvaluations());

        // 4. Try submitting again for the same lecturer (should fail)
        $responseDuplicate = $this->actingAs($this->mahasiswa, 'mahasiswa')
            ->post("/v2/isi-kuisioner/{$questionnaire->id}/submit", [
                'dosen_id' => $dosen->id,
                'matkul_id' => $matkul->id,
                'jadwal_id' => $jadwal->id,
                'answers' => [
                    $question1->id => 'Baik',
                ],
            ]);

        $responseDuplicate->assertSessionHasErrors('dosen_id');
    }
}
