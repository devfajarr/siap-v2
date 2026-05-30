<?php

namespace Tests\Feature\V2;

use App\Models\ContactVerification;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Semester;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class WhatsappVerificationTest extends TestCase
{
    use DatabaseTransactions;

    protected Mahasiswa $student;

    protected function setUp(): void
    {
        parent::setUp();

        $semester = Semester::firstOrCreate(['semester' => 1], ['status' => 1]);
        $kelas = Kelas::firstOrCreate(
            ['kode_kelas' => '99999'],
            [
                'nama_kelas' => 'Test Kelas',
                'jenis_kelas' => 'Reguler',
                'id_prodi' => 1,
                'id_semester' => $semester->id,
            ]
        );

        $this->student = Mahasiswa::create([
            'nama_lengkap' => 'Student Test WA',
            'nim' => '999010099',
            'nisn' => '9991234599',
            'nik' => '9991011234567899',
            'email' => 'studentwa@test.com',
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Test WA',
            'no_telephone' => '089900000099',
            'tanggal_lahir' => '2004-01-01',
            'tempat_lahir' => 'Jakarta',
            'nama_ibu' => 'Ibu Kandung',
            'jenis_kelamin' => 'Laki-Laki',
            'kelas_id' => $kelas->id,
            'status_krs' => 1,
            'tahun_masuk' => '2024',
            'is_first_login' => false,
            'whatsapp_verified_at' => null,
        ]);
    }

    /**
     * Test requesting OTP is successful and rate limited to 5 per minute.
     */
    public function test_requesting_otp_is_successful_and_rate_limited(): void
    {
        $this->actingAs($this->student, 'mahasiswa');

        // Clear rate limiter first to avoid failures from previous runs
        $key = 'send-otp:'.$this->student->id.':'.get_class($this->student);
        RateLimiter::clear($key);

        // First 5 requests should succeed
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson(route('v2.whatsapp.send-otp'), [
                'no_telephone' => '089900000099',
            ]);
            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
                'message' => 'Kode OTP berhasil dikirim ke WhatsApp Anda.',
            ]);
        }

        // 6th request should fail with 429
        $response = $this->postJson(route('v2.whatsapp.send-otp'), [
            'no_telephone' => '089900000099',
        ]);
        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * Test verification works with a correct OTP and fails with incorrect OTP.
     */
    public function test_otp_verification_flow(): void
    {
        $this->actingAs($this->student, 'mahasiswa');

        // Generate and save a mock OTP in the database
        ContactVerification::create([
            'verifiable_id' => $this->student->id,
            'verifiable_type' => get_class($this->student),
            'type' => 'whatsapp',
            'contact' => '6289900000099',
            'code' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
        ]);

        // 1. Verify with incorrect OTP should fail
        $response = $this->postJson(route('v2.whatsapp.verify-otp'), [
            'code' => '654321',
        ]);
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Kode OTP yang dimasukkan salah.',
        ]);

        // 2. Verify with correct OTP should succeed
        $response = $this->postJson(route('v2.whatsapp.verify-otp'), [
            'code' => '123456',
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil diverifikasi!',
            'no_telephone' => '6289900000099',
        ]);

        // Ensure database state is updated
        $this->assertNotNull($this->student->fresh()->whatsapp_verified_at);
        $this->assertEquals('6289900000099', $this->student->fresh()->no_telephone);
    }

    /**
     * Test action gating middleware blocks unverified users and allows verified users.
     */
    public function test_action_gating_middleware(): void
    {
        // 1. Unverified student tries to submit KRS
        $this->actingAs($this->student, 'mahasiswa');

        $response = $this->postJson(route('v2.mahasiswa.krs-pembayaran.pengajuan'));
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Tindakan dibatalkan. Nomor WhatsApp Anda belum terverifikasi. Silakan verifikasi nomor Anda di menu Profil terlebih dahulu.',
        ]);

        // 2. Mark student as verified
        $this->student->update([
            'whatsapp_verified_at' => now(),
        ]);

        // Try submitting again. The route might return redirect/render or validation errors depending on inputs,
        // but it should not return 403 (unauthorized due to verification).
        $response = $this->postJson(route('v2.mahasiswa.krs-pembayaran.pengajuan'));
        $this->assertNotEquals(403, $response->status());
    }
}
