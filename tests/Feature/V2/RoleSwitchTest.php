<?php

namespace Tests\Feature\V2;

use App\Models\Dosen;
use App\Models\Jabatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleSwitchTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test that a Dosen accessing a structural path (e.g., bpmi/dashboard)
     * is automatically switched to the Jabatan guard.
     */
    public function test_dosen_accessing_structural_path_is_switched_to_jabatan_guard(): void
    {
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
            'is_first_login' => false,
        ]);

        $jabatan = Jabatan::create([
            'dosens_id' => $dosen->id,
            'nama_jabatan' => 'bpmi',
            'email' => 'dosen@test.com',
            'password' => Hash::make('secret123'),
            'status' => 1,
            'is_first_login' => false,
        ]);

        $response = $this->actingAs($dosen, 'dosen')
            ->get('/v2/bpmi/dashboard');

        // It should redirect to re-evaluate the request with the new guard
        $response->assertRedirect(url('/v2/bpmi/dashboard'));

        // Clear actingAs so that next request resolves from session
        $this->app['auth']->forgetGuards();

        $response = $this->get('/v2/bpmi/dashboard');
        $response->assertRedirect(url('/v2/dosen/dashboard')); // Redirects structural role dashboard to dosen dashboard

        $this->assertEquals('bpmi', session('user.role'));
    }

    /**
     * Test that a Jabatan user accessing a Dosen path (excluding dashboard)
     * is automatically switched back to the Dosen guard.
     */
    public function test_jabatan_user_accessing_dosen_path_is_switched_back_to_dosen_guard(): void
    {
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
            'is_first_login' => false,
        ]);

        $jabatan = Jabatan::create([
            'dosens_id' => $dosen->id,
            'nama_jabatan' => 'bpmi',
            'email' => 'dosen@test.com',
            'password' => Hash::make('secret123'),
            'status' => 1,
            'is_first_login' => false,
        ]);

        $response = $this->actingAs($jabatan, 'jabatan')
            ->get('/v2/dosen/data-presensi');

        // It should redirect to re-evaluate the request with the Dosen guard
        $response->assertRedirect(url('/v2/dosen/data-presensi'));

        // Clear actingAs so that next request resolves from session
        $this->app['auth']->forgetGuards();

        $response = $this->get('/v2/dosen/data-presensi');
        $response->assertStatus(200);
        $this->assertEquals('dosen', session('user.role'));
    }

    /**
     * Test that a Jabatan user accessing the Dosen dashboard
     * is NOT switched back to the Dosen guard.
     */
    public function test_jabatan_user_accessing_dosen_dashboard_is_not_switched_back_to_dosen_guard(): void
    {
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
            'is_first_login' => false,
        ]);

        $jabatan = Jabatan::create([
            'dosens_id' => $dosen->id,
            'nama_jabatan' => 'bpmi',
            'email' => 'dosen@test.com',
            'password' => Hash::make('secret123'),
            'status' => 1,
            'is_first_login' => false,
        ]);

        $response = $this->actingAs($jabatan, 'jabatan')
            ->get('/v2/dosen/dashboard');

        // Should not switch back or redirect (it should render successfully)
        $response->assertStatus(200);

        $this->assertTrue(Auth::guard('jabatan')->check());
        $this->assertFalse(Auth::guard('dosen')->check());
    }
}
