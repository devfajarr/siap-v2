<?php

namespace Tests\Feature\V2\Auth;

use App\Jobs\SendWhatsappResetPasswordJob;
use App\Models\ContactVerification;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Halaman lupa password dapat diakses oleh guest.
     */
    public function test_forgot_password_page_can_be_rendered(): void
    {
        $response = $this->get(route('v2.forgot-password'));

        $response->assertStatus(200);
    }

    /**
     * User dengan nomor WhatsApp terdaftar dapat meminta kode OTP.
     */
    public function test_user_can_request_otp_with_valid_credentials(): void
    {
        Queue::fake();

        $mahasiswa = $this->createMahasiswa([
            'nim' => '22010101',
            'no_telephone' => '081234567890',
        ]);

        $response = $this->postJson(route('v2.forgot-password.send-otp'), [
            'username' => '22010101',
            'role' => 'mahasiswa',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kode OTP pemulihan berhasil dikirim ke nomor WhatsApp Anda.',
                'masked_phone' => '0812*****890',
            ]);

        $this->assertDatabaseHas('contact_verifications', [
            'verifiable_id' => $mahasiswa->id,
            'verifiable_type' => Mahasiswa::class,
            'type' => 'reset_password',
            'contact' => '081234567890',
        ]);

        Queue::assertPushed(SendWhatsappResetPasswordJob::class, function ($job) {
            return $job->nomor === '081234567890';
        });
    }

    /**
     * User tidak dapat meminta OTP jika nomor telepon kosong.
     */
    public function test_user_cannot_request_otp_if_phone_not_registered(): void
    {
        $this->createMahasiswa([
            'nim' => '22010102',
            'no_telephone' => '',
        ]);

        $response = $this->postJson(route('v2.forgot-password.send-otp'), [
            'username' => '22010102',
            'role' => 'mahasiswa',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Nomor WhatsApp belum terdaftar di sistem. Silakan hubungi admin akademik untuk melakukan reset password.',
            ]);
    }

    /**
     * User dapat menyetel ulang password dengan OTP yang valid.
     */
    public function test_user_can_reset_password_with_valid_otp(): void
    {
        $mahasiswa = $this->createMahasiswa([
            'nim' => '22010103',
            'no_telephone' => '081234567890',
            'password' => Hash::make('oldpassword'),
        ]);

        ContactVerification::create([
            'verifiable_id' => $mahasiswa->id,
            'verifiable_type' => Mahasiswa::class,
            'type' => 'reset_password',
            'contact' => '081234567890',
            'code' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson(route('v2.forgot-password.verify-and-reset'), [
            'username' => '22010103',
            'role' => 'mahasiswa',
            'code' => '123456',
            'password' => 'PasswordNew123!',
            'password_confirmation' => 'PasswordNew123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kata sandi berhasil diubah.',
            ]);

        $mahasiswa->refresh();
        $this->assertTrue(Hash::check('PasswordNew123!', $mahasiswa->password));
        $this->assertFalse((bool) $mahasiswa->is_first_login);

        $this->assertDatabaseMissing('contact_verifications', [
            'verifiable_id' => $mahasiswa->id,
            'verifiable_type' => Mahasiswa::class,
        ]);
    }

    /**
     * User gagal melakukan reset jika OTP salah atau kadaluwarsa.
     */
    public function test_user_cannot_reset_password_with_invalid_otp(): void
    {
        $mahasiswa = $this->createMahasiswa([
            'nim' => '22010104',
            'no_telephone' => '081234567890',
            'password' => Hash::make('oldpassword'),
        ]);

        ContactVerification::create([
            'verifiable_id' => $mahasiswa->id,
            'verifiable_type' => Mahasiswa::class,
            'type' => 'reset_password',
            'contact' => '081234567890',
            'code' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson(route('v2.forgot-password.verify-and-reset'), [
            'username' => '22010104',
            'role' => 'mahasiswa',
            'code' => '999999', // salah OTP
            'password' => 'PasswordNew123!',
            'password_confirmation' => 'PasswordNew123!',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Kode OTP yang dimasukkan salah.',
            ]);

        // Verifikasi OTP kadaluwarsa
        ContactVerification::where('verifiable_id', $mahasiswa->id)->update([
            'expires_at' => now()->subMinutes(1),
        ]);

        $response = $this->postJson(route('v2.forgot-password.verify-and-reset'), [
            'username' => '22010104',
            'role' => 'mahasiswa',
            'code' => '123456',
            'password' => 'PasswordNew123!',
            'password_confirmation' => 'PasswordNew123!',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Kode OTP telah kadaluwarsa. Silakan minta kode baru.',
            ]);
    }

    /**
     * Helper untuk membuat objek mahasiswa pengujian.
     */
    protected function createMahasiswa(array $attributes = []): Mahasiswa
    {
        $prodi = Prodi::create([
            'nama_prodi' => 'Teknik Informatika',
            'singkatan' => 'TI',
            'kode_prodi' => 'TI01',
            'jenjang' => 'D3',
            'alias_nama' => 'TI Alias',
            'alias_jenjang' => 'D3 Alias',
        ]);

        $semester = Semester::create([
            'semester' => 1,
            'status' => 1,
        ]);

        $kelas = Kelas::create([
            'kode_kelas' => 'TI22A',
            'nama_kelas' => 'TI 2022 A',
            'jenis_kelas' => 'Reguler',
            'id_prodi' => $prodi->id,
            'id_semester' => $semester->id,
        ]);

        return Mahasiswa::create(array_merge([
            'nama_lengkap' => 'Student Test',
            'tahun_masuk' => '2022',
            'nim' => '22010101',
            'nisn' => '12345678',
            'nik' => '3301010101010001',
            'email' => 'student@test.com',
            'password' => Hash::make('password'),
            'alamat' => 'Alamat Test',
            'no_telephone' => '081234567890',
            'tanggal_lahir' => '2004-01-01',
            'tempat_lahir' => 'Purworejo',
            'nama_ibu' => 'Mother Test',
            'jenis_kelamin' => 'Laki-Laki',
            'kelas_id' => $kelas->id,
            'is_first_login' => true,
        ], $attributes));
    }
}
