<?php

namespace Tests\Feature\V2\Admin;

use App\Http\Middleware\CheckRole;
use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Jabatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class JabatanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_assign_dosen_to_jabatan(): void
    {
        $admin = Admin::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.com',
            'no_telephone' => '08123456789',
            'password' => Hash::make('password'),
        ]);

        $dosen = Dosen::create([
            'nama' => 'Dosen Test',
            'nidn' => '1234567890',
            'jenis_kelamin' => 'L',
            'no_telephone' => '081234567890',
            'agama' => 'Islam',
            'status' => 1,
            'email' => 'dosen@test.com',
            'password' => Hash::make('password_dosen'),
            'pembimbing_akademik' => 0,
            'tanggal_lahir' => '1990-01-01',
            'tempat_lahir' => 'Jakarta',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post('/v2/admin/data-master/data-jabatan', [
                'user_type' => 'dosen',
                'dosens_id' => $dosen->id,
                'nama_jabatan' => 'bpmi',
                'password_mode' => 'base',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jabatans', [
            'dosens_id' => $dosen->id,
            'nama_jabatan' => 'bpmi',
            'email' => 'dosen@test.com',
        ]);
    }

    public function test_jabatan_user_can_login_and_be_redirected(): void
    {
        $dosen = Dosen::create([
            'nama' => 'Dosen Test',
            'nidn' => '1234567890',
            'jenis_kelamin' => 'L',
            'no_telephone' => '081234567890',
            'agama' => 'Islam',
            'status' => 1,
            'email' => 'bpmi@test.com',
            'password' => Hash::make('password_dosen'),
            'pembimbing_akademik' => 0,
            'tanggal_lahir' => '1990-01-01',
            'tempat_lahir' => 'Jakarta',
        ]);

        $jabatan = Jabatan::create([
            'dosens_id' => $dosen->id,
            'nama_jabatan' => 'bpmi',
            'email' => 'bpmi@test.com',
            'password' => Hash::make('secret123'),
            'status' => 1,
        ]);

        $response = $this->post('/login', [
            'username' => 'bpmi@test.com',
            'password' => 'secret123',
            'role' => 'bpmi',
        ]);

        $response->assertRedirect(route('v2.dosen.dashboard'));
        $this->assertTrue(auth()->guard('jabatan')->check());
        $this->assertEquals('bpmi', session('user.role'));
    }

    public function test_middleware_restricts_unauthorized_jabatan_users(): void
    {
        $dosen = Dosen::create([
            'nama' => 'Dosen Test',
            'nidn' => '1234567890',
            'jenis_kelamin' => 'L',
            'no_telephone' => '081234567890',
            'agama' => 'Islam',
            'status' => 1,
            'email' => 'bpmi@test.com',
            'password' => Hash::make('password_dosen'),
            'pembimbing_akademik' => 0,
            'tanggal_lahir' => '1990-01-01',
            'tempat_lahir' => 'Jakarta',
        ]);

        $jabatan = Jabatan::create([
            'dosens_id' => $dosen->id,
            'nama_jabatan' => 'bpmi',
            'email' => 'bpmi@test.com',
            'password' => Hash::make('secret123'),
            'status' => 1,
        ]);

        // Access route restricted to 'sarpras' only
        $this->actingAs($jabatan, 'jabatan');

        $request = Request::create('/v2/sarpras/dashboard', 'GET');

        $middleware = new CheckRole;

        $this->expectException(HttpException::class);

        $middleware->handle($request, function () {}, 'sarpras');
    }
}
