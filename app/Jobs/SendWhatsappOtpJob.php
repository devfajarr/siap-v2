<?php

namespace App\Jobs;

use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $nomor,
        protected string $code
    ) {
        $this->queue = 'whatsapp';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pesan = "Kode OTP verifikasi nomor WhatsApp Anda adalah: *{$this->code}*. ".
                 'Kode ini berlaku selama 5 menit. Jangan bagikan kode ini kepada siapa pun.';

        WhatsappService::kirim($this->nomor, $pesan);
    }
}
