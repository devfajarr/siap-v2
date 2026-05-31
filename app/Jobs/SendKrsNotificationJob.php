<?php

namespace App\Jobs;

use App\Models\Admin;
use App\Models\Krs;
use App\Notifications\KRSNotification;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendKrsNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $krsId;

    /**
     * Create a new job instance.
     */
    public function __construct($krsId)
    {
        $this->krsId = $krsId;
        $this->queue = 'whatsapp';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $krs = Krs::with('mahasiswa', 'kelas', 'prodi')->find($this->krsId);

        if (! $krs || ! $krs->mahasiswa || $krs->status_krs != 1) {
            return;
        }

        $mahasiswa = $krs->mahasiswa;

        // Notifikasi ke Mahasiswa
        $mahasiswa->notify(new KRSNotification($krs));
        if (! empty($mahasiswa->no_telephone)) {
            $pesanMhs = "*KRS Berhasil Diverifikasi*\n\n"
                ."Nama : *{$mahasiswa->nama_lengkap}*\n"
                ."Kelas : {$krs->kelas->nama_kelas}\n"
                ."Prodi : {$krs->prodi->nama_prodi}\n\n"
                .'KRS kamu sudah *disetujui*. Silakan cek status di sistem.';
            WhatsappService::kirim($mahasiswa->no_telephone, $pesanMhs);
        }

        // Notifikasi ke Admin
        $admins = Admin::all();
        foreach ($admins as $adm) {
            $adm->notify(new KRSNotification($krs));
            if (! empty($adm->no_telephone)) {
                $pesanAdm = "*KRS Diverifikasi*\n\n"
                    ."{$mahasiswa->nama_lengkap}\n"
                    ."{$krs->kelas->nama_kelas} • {$krs->prodi->nama_prodi}\n"
                    .'Status: *Disetujui*';
                WhatsappService::kirim($adm->no_telephone, $pesanAdm);
            }
        }
    }
}
