<?php

namespace Tests\Feature\V2\Admin;

use App\Models\Admin;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\OrangTua;
use App\Models\Semester;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrangTuaManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $admin;

    protected Mahasiswa $student1;

    protected Mahasiswa $student2;

    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.com',
            'no_telephone' => '08123456789',
            'password' => Hash::make('password'),
        ]);

        $semester = Semester::firstOrCreate(['semester' => 1], ['status' => 1]);
        $this->kelas = Kelas::firstOrCreate(
            ['kode_kelas' => '99999'],
            [
                'nama_kelas' => 'Test Kelas',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => 1,
                'id_semester' => $semester->id,
            ]
        );

        $this->student1 = Mahasiswa::create([
            'nama_lengkap' => 'Student One',
            'nim' => '999010011',
            'nisn' => '9991234511',
            'nik' => '9991011234567811',
            'email' => 'student1@test.com',
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Test 1',
            'no_telephone' => '089900000011',
            'tanggal_lahir' => '2004-01-01',
            'tempat_lahir' => 'Jakarta',
            'nama_ibu' => 'Ibu Kandung 1',
            'jenis_kelamin' => 'Laki-Laki',
            'kelas_id' => $this->kelas->id,
            'status_krs' => 1,
            'tahun_masuk' => '2024',
        ]);

        $this->student2 = Mahasiswa::create([
            'nama_lengkap' => 'Student Two',
            'nim' => '999010012',
            'nisn' => '9991234512',
            'nik' => '9991011234567812',
            'email' => 'student2@test.com',
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Test 2',
            'no_telephone' => '089900000012',
            'tanggal_lahir' => '2004-02-02',
            'tempat_lahir' => 'Jakarta',
            'nama_ibu' => 'Ibu Kandung 2',
            'jenis_kelamin' => 'Laki-Laki',
            'kelas_id' => $this->kelas->id,
            'status_krs' => 1,
            'tahun_masuk' => '2024',
        ]);
    }

    public function test_admin_can_create_and_link_parent_account(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.mahasiswa.orang-tua.store', $this->student1->id), [
                'nama' => 'Orang Tua A',
                'username' => 'ortu.a',
                'password' => 'password123',
                'no_telephone' => '081234567890',
                'alamat' => 'Alamat Ortu A',
                'relationship_type' => 'Ayah',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('orang_tuas', [
            'nama' => 'Orang Tua A',
            'username' => 'ortu.a',
            'no_telephone' => '081234567890',
            'alamat' => 'Alamat Ortu A',
        ]);

        $parent = OrangTua::where('username', 'ortu.a')->first();
        $this->assertNotNull($parent);

        $this->assertDatabaseHas('mahasiswa_parent', [
            'orang_tua_id' => $parent->id,
            'mahasiswa_id' => $this->student1->id,
            'relationship_type' => 'Ayah',
        ]);
    }

    public function test_admin_can_link_existing_parent_account(): void
    {
        // Pre-create the parent
        $parent = OrangTua::create([
            'nama' => 'Orang Tua Existing',
            'username' => 'ortu.existing',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.mahasiswa.orang-tua.store', $this->student1->id), [
                'nama' => 'Orang Tua Existing',
                'username' => 'ortu.existing',
                'relationship_type' => 'Ibu',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('mahasiswa_parent', [
            'orang_tua_id' => $parent->id,
            'mahasiswa_id' => $this->student1->id,
            'relationship_type' => 'Ibu',
        ]);
    }

    public function test_admin_cannot_link_same_parent_account_twice(): void
    {
        $parent = OrangTua::create([
            'nama' => 'Orang Tua Double',
            'username' => 'ortu.double',
            'password' => Hash::make('password123'),
        ]);

        $parent->mahasiswas()->attach($this->student1->id, ['relationship_type' => 'Wali']);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('v2.admin.mahasiswa.orang-tua.store', $this->student1->id), [
                'nama' => 'Orang Tua Double',
                'username' => 'ortu.double',
                'relationship_type' => 'Wali',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Orang tua ini sudah terhubung dengan mahasiswa terkait.');
    }

    public function test_admin_can_unlink_parent_and_cleans_up_orphaned_parent(): void
    {
        $parent = OrangTua::create([
            'nama' => 'Orang Tua Orphan',
            'username' => 'ortu.orphan',
            'password' => Hash::make('password123'),
        ]);

        $parent->mahasiswas()->attach($this->student1->id, ['relationship_type' => 'Ayah']);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('v2.admin.mahasiswa.orang-tua.destroy', [$this->student1->id, $parent->id]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('mahasiswa_parent', [
            'orang_tua_id' => $parent->id,
            'mahasiswa_id' => $this->student1->id,
        ]);

        // Since parent has 0 children linked, they should be deleted from the database
        $this->assertDatabaseMissing('orang_tuas', [
            'id' => $parent->id,
        ]);
    }

    public function test_admin_can_unlink_parent_but_keeps_active_parent_if_linked_elsewhere(): void
    {
        $parent = OrangTua::create([
            'nama' => 'Orang Tua Shared',
            'username' => 'ortu.shared',
            'password' => Hash::make('password123'),
        ]);

        // Link to both student1 and student2
        $parent->mahasiswas()->attach($this->student1->id, ['relationship_type' => 'Ayah']);
        $parent->mahasiswas()->attach($this->student2->id, ['relationship_type' => 'Ayah']);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('v2.admin.mahasiswa.orang-tua.destroy', [$this->student1->id, $parent->id]));

        $response->assertRedirect();

        // Student 1 connection is removed
        $this->assertDatabaseMissing('mahasiswa_parent', [
            'orang_tua_id' => $parent->id,
            'mahasiswa_id' => $this->student1->id,
        ]);

        // Student 2 connection is kept
        $this->assertDatabaseHas('mahasiswa_parent', [
            'orang_tua_id' => $parent->id,
            'mahasiswa_id' => $this->student2->id,
        ]);

        // Parent still exists in database because they are linked to student2
        $this->assertDatabaseHas('orang_tuas', [
            'id' => $parent->id,
        ]);
    }

    public function test_unauthorized_users_cannot_access_orang_tua_management(): void
    {
        // Guests cannot access store
        $response = $this->post(route('v2.admin.mahasiswa.orang-tua.store', $this->student1->id), []);
        $response->assertRedirect('/login');

        // Guests cannot access destroy
        $response = $this->delete(route('v2.admin.mahasiswa.orang-tua.destroy', [$this->student1->id, 1]));
        $response->assertRedirect('/login');

        // A student cannot access store
        $response = $this->actingAs($this->student1, 'mahasiswa')
            ->post(route('v2.admin.mahasiswa.orang-tua.store', $this->student1->id), []);
        $response->assertRedirect('/login');
    }

    public function test_command_can_migrate_student_mothers_to_parent_accounts(): void
    {
        $expectedCount = Mahasiswa::whereNotNull('nama_ibu')
            ->where('nama_ibu', '!=', '')
            ->count();

        // Execute the console command
        $this->artisan('siap:migrate-parents')
            ->expectsOutput("Menemukan {$expectedCount} data mahasiswa untuk diproses.")
            ->assertExitCode(0);

        // Verify parent accounts were created in database
        $this->assertDatabaseHas('orang_tuas', [
            'nama' => 'Ibu Kandung 1',
            'username' => 'ortu.999010011',
        ]);

        $this->assertDatabaseHas('orang_tuas', [
            'nama' => 'Ibu Kandung 2',
            'username' => 'ortu.999010012',
        ]);

        // Verify relationships exist in pivot table
        $parent1 = OrangTua::where('username', 'ortu.999010011')->first();
        $parent2 = OrangTua::where('username', 'ortu.999010012')->first();

        $this->assertNotNull($parent1);
        $this->assertNotNull($parent2);

        $this->assertDatabaseHas('mahasiswa_parent', [
            'orang_tua_id' => $parent1->id,
            'mahasiswa_id' => $this->student1->id,
            'relationship_type' => 'Ibu',
        ]);

        $this->assertDatabaseHas('mahasiswa_parent', [
            'orang_tua_id' => $parent2->id,
            'mahasiswa_id' => $this->student2->id,
            'relationship_type' => 'Ibu',
        ]);
    }
}
