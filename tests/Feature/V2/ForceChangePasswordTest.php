<?php

namespace Tests\Feature\V2;

use App\Models\Dosen;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForceChangePasswordTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test that user with is_first_login = true is redirected to force change password page.
     */
    public function test_user_with_first_login_true_is_redirected_to_force_change_password_page(): void
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
            'is_first_login' => true,
        ]);

        $response = $this->actingAs($dosen, 'dosen')
            ->get('/v2/dosen/dashboard');

        $response->assertRedirect(route('v2.force-change-password'));
    }

    /**
     * Test that user with is_first_login = false can access dashboard normally.
     */
    public function test_user_with_first_login_false_can_access_dashboard_normally(): void
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

        $response = $this->actingAs($dosen, 'dosen')
            ->get('/v2/dosen/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test that user cannot access force change password page if they already changed their password.
     */
    public function test_user_cannot_access_force_change_password_page_if_already_changed(): void
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

        $response = $this->actingAs($dosen, 'dosen')
            ->get('/v2/force-change-password');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test that weak passwords are rejected.
     */
    public function test_user_cannot_set_weak_password_on_first_login(): void
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
            'is_first_login' => true,
        ]);

        $response = $this->actingAs($dosen, 'dosen')
            ->post('/v2/force-change-password', [
                'password' => '12345',
                'password_confirmation' => '12345',
            ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertTrue((bool) $dosen->fresh()->is_first_login);
    }

    /**
     * Test that user can set strong password and transition seamlessly.
     */
    public function test_user_can_set_strong_password_on_first_login_and_is_logged_in_seamlessly(): void
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
            'is_first_login' => true,
        ]);

        // Mock session role to simulate proper auth state
        session(['user' => ['role' => 'dosen']]);

        $response = $this->actingAs($dosen, 'dosen')
            ->post('/v2/force-change-password', [
                'password' => 'K9#mX2!zL4$pQ7*wT5_Unique1842',
                'password_confirmation' => 'K9#mX2!zL4$pQ7*wT5_Unique1842',
            ]);

        $response->assertRedirect(route('v2.dosen.dashboard'));
        $this->assertFalse((bool) $dosen->fresh()->is_first_login);
        $this->assertTrue(Hash::check('K9#mX2!zL4$pQ7*wT5_Unique1842', $dosen->fresh()->password));
    }
}
