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
            $kelasNama = $krs->kelas->nama_kelas ?? '-';
            $prodiNama = $krs->prodi->nama_prodi ?? '-';
            $pesanMhs = "*SIA POLSA NOTIFICATION BOT* 🤖\n"
                ."══════════════════════════\n"
                ."🟢 *STATUS KRS: DIVERIFIKASI*\n"
                ."══════════════════════════\n"
                ."• *Mahasiswa:* {$mahasiswa->nama_lengkap}\n"
                ."• *NIM:* {$mahasiswa->nim}\n"
                ."• *Kelas:* {$kelasNama}\n"
                ."• *Prodi:* {$prodiNama}\n\n"
                ."*Status:* KRS Anda telah disetujui oleh Dosen Pembimbing Akademik. Silakan cek status lengkap di portal.\n"
                ."──────────────────────────\n"
                .'📅 _Waktu: '.now()->format('d-m-Y H:i')." WIB_\n"
                ."🌐 _Akses Portal: siapv2.polsa.ac.id_\n"
                .'_SIA POLSA - Sistem Informasi Akademik_';
            WhatsappService::kirim($mahasiswa->no_telephone, $pesanMhs);
        }

        // Notifikasi ke Admin
        $admins = Admin::all();
        foreach ($admins as $adm) {
            $adm->notify(new KRSNotification($krs));
            if (! empty($adm->no_telephone)) {
                $kelasNama = $krs->kelas->nama_kelas ?? '-';
                $prodiNama = $krs->prodi->nama_prodi ?? '-';
                $pesanAdm = "*SIA POLSA NOTIFICATION BOT* 🤖\n"
                    ."══════════════════════════\n"
                    ."🟢 *KRS SELESAI*\n"
                    ."══════════════════════════\n"
                    ."• *Mahasiswa:* {$mahasiswa->nama_lengkap}\n"
                    ."• *NIM:* {$mahasiswa->nim}\n"
                    ."• *Kelas:* {$kelasNama}\n"
                    ."• *Prodi:* {$prodiNama}\n\n"
                    ."*Status:* Data KRS mahasiswa bersangkutan telah berhasil diverifikasi oleh Dosen PA.\n"
                    ."──────────────────────────\n"
                    .'📅 _Waktu: '.now()->format('d-m-Y H:i')." WIB_\n"
                    ."🌐 _Akses Portal: siapv2.polsa.ac.id_\n"
                    .'_SIA POLSA - Sistem Informasi Akademik_';
                WhatsappService::kirim($adm->no_telephone, $pesanAdm);
            }
        }
    }
}
