<?php

namespace Tests\Feature\V2;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\OrangTua;
use App\Models\Prodi;
use App\Models\Semester;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrangTuaTest extends TestCase
{
    use DatabaseTransactions;

    protected OrangTua $parent;

    protected Mahasiswa $child1;

    protected Mahasiswa $child2;

    protected Mahasiswa $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $prodi = Prodi::firstOrCreate(
            ['kode_prodi' => 'TI101'],
            [
                'nama_prodi' => 'Teknik Informatika',
                'singkatan' => 'TI',
                'jenjang' => 'D3',
                'alias_nama' => 'Information Technical',
                'alias_jenjang' => 'Diploma Three',
            ]
        );
        $semester = Semester::firstOrCreate(['semester' => 1], ['status' => 1]);
        $kelas = Kelas::firstOrCreate(
            ['kode_kelas' => '99999'],
            [
                'nama_kelas' => 'Test Kelas',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => $prodi->id,
                'id_semester' => $semester->id,
            ]
        );

        // Create student 1 (child 1)
        $this->child1 = Mahasiswa::create([
            'nama_lengkap' => 'Child One',
            'nim' => '999010001',
            'nisn' => '9991234501',
            'nik' => '9991011234567801',
            'email' => 'child1@test.com',
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Test 1',
            'no_telephone' => '089900000001',
            'tanggal_lahir' => '2004-01-01',
            'tempat_lahir' => 'Jakarta',
            'nama_ibu' => 'Ibu Kandung 1',
            'jenis_kelamin' => 'Laki-Laki',
            'kelas_id' => $kelas->id,
            'status_krs' => 1,
            'tahun_masuk' => '2024',
        ]);

        // Create student 2 (child 2)
        $this->child2 = Mahasiswa::create([
            'nama_lengkap' => 'Child Two',
            'nim' => '999010002',
            'nisn' => '9991234502',
            'nik' => '9991011234567802',
            'email' => 'child2@test.com',
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Test 2',
            'no_telephone' => '089900000002',
            'tanggal_lahir' => '2004-02-02',
            'tempat_lahir' => 'Jakarta',
            'nama_ibu' => 'Ibu Kandung 2',
            'jenis_kelamin' => 'Laki-Laki',
            'kelas_id' => $kelas->id,
            'status_krs' => 1,
            'tahun_masuk' => '2024',
        ]);

        // Create other student (not linked)
        $this->otherStudent = Mahasiswa::create([
            'nama_lengkap' => 'Other Student',
            'nim' => '999010003',
            'nisn' => '9991234503',
            'nik' => '9991011234567803',
            'email' => 'other@test.com',
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Test 3',
            'no_telephone' => '089900000003',
            'tanggal_lahir' => '2004-03-03',
            'tempat_lahir' => 'Jakarta',
            'nama_ibu' => 'Ibu Kandung 3',
            'jenis_kelamin' => 'Laki-Laki',
            'kelas_id' => $kelas->id,
            'status_krs' => 1,
            'tahun_masuk' => '2024',
        ]);

        // Create parent account
        $this->parent = OrangTua::create([
            'nama' => 'Orang Tua Test',
            'username' => 'ortu.test',
            'password' => Hash::make('password_ortu'),
            'no_telephone' => '089912345678',
            'alamat' => 'Jl. Ortu Test',
        ]);

        // Link child 1 and child 2 to parent
        $this->parent->mahasiswas()->sync([
            $this->child1->id => ['relationship_type' => 'Ayah'],
            $this->child2->id => ['relationship_type' => 'Ayah'],
        ]);
    }

    /**
     * Test parent login authentication.
     */
    public function test_parent_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'ortu.test',
            'password' => 'password_ortu',
            'role' => 'orang_tua',
        ]);

        $response->assertRedirect(route('v2.orang-tua.dashboard'));
        $this->assertAuthenticatedAs($this->parent, 'orang_tua');
    }

    /**
     * Test parent dashboard access and active child resolution.
     */
    public function test_parent_can_access_dashboard_and_active_child_is_resolved(): void
    {
        $response = $this->actingAs($this->parent, 'orang_tua')
            ->get('/v2/orang-tua/dashboard');

        $response->assertStatus(200);

        // Active child should default to child1
        $this->assertEquals($this->child1->id, session('user.activeChildId'));
    }

    /**
     * Test parent switching between multiple children.
     */
    public function test_parent_can_switch_active_child(): void
    {
        $this->actingAs($this->parent, 'orang_tua');

        // Set initial active child
        session(['user.activeChildId' => $this->child1->id]);

        $response = $this->post('/v2/orang-tua/switch-child', [
            'child_id' => $this->child2->id,
        ]);

        $response->assertRedirect();
        $this->assertEquals($this->child2->id, session('user.activeChildId'));
    }

    /**
     * Test parent cannot switch to a student who is not their child.
     */
    public function test_parent_cannot_switch_to_unlinked_student(): void
    {
        $this->actingAs($this->parent, 'orang_tua');

        // Set initial active child
        session(['user.activeChildId' => $this->child1->id]);

        $response = $this->post('/v2/orang-tua/switch-child', [
            'child_id' => $this->otherStudent->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals($this->child1->id, session('user.activeChildId')); // remains unchanged
    }

    /**
     * Test parent cannot access child pages without authentication.
     */
    public function test_guest_cannot_access_parent_routes(): void
    {
        $response = $this->get('/v2/orang-tua/dashboard');
        $response->assertRedirect('/login');
    }
}
