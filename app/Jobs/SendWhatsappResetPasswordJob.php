<?php

namespace App\Jobs;

use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappResetPasswordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $nomor,
        public string $code
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pesan = "Kode OTP untuk melakukan reset password akun SIAP POLSA Anda adalah: *{$this->code}*.\n\n".
                 'Kode ini berlaku selama 5 menit. Harap jangan bagikan kode ini kepada siapa pun.';

        WhatsappService::kirim($this->nomor, $pesan);
    }
}
