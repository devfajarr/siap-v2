<?php

namespace App\Notifications;

use App\Models\PengajuanCetakKartuUjian;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KartuUjianNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected PengajuanCetakKartuUjian $pengajuan) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $jenisUjianUpper = strtoupper($this->pengajuan->jenis_ujian);

        if ($this->pengajuan->status == 1) {
            // Selesai / Siap Diambil
            $notif = [
                'notification_type' => 'kartu_ujian',
                'title' => "Kartu Ujian {$jenisUjianUpper} Siap Diambil",
                'message_content' => 'Silakan ambil kartu ujian fisik Anda di Bagian Akademik.',
                'pengajuan_id' => $this->pengajuan->id,
                'status' => $this->pengajuan->status,
                'keterangan' => $this->pengajuan->keterangan,
                'jenis_ujian' => $this->pengajuan->jenis_ujian,
            ];

            $pesan = "*SIA POLSA NOTIFICATION BOT* 🤖\n"
                ."══════════════════════════\n"
                ."🟢 *KARTU UJIAN DISETUJUI*\n"
                ."══════════════════════════\n"
                ."• *Mahasiswa:* {$notifiable->nama_lengkap}\n"
                ."• *NIM:* {$notifiable->nim}\n"
                ."• *Ujian:* Kartu Ujian {$jenisUjianUpper}\n\n"
                ."*Status:* Pengajuan disetujui dan kartu fisik telah selesai dicetak. Silakan ambil di *Bagian Akademik*.\n"
                ."──────────────────────────\n"
                .'📅 _Waktu: '.now()->format('d-m-Y H:i')." WIB_\n"
                ."🌐 _Akses Portal: siapv2.polsa.ac.id_\n"
                .'_SIA POLSA - Sistem Informasi Akademik_';

            if (! empty($notifiable->no_telephone)) {
                WhatsappService::kirim($notifiable->no_telephone, $pesan);
            }

            return $notif;
        } elseif ($this->pengajuan->status == 2) {
            // Ditolak
            $notif = [
                'notification_type' => 'kartu_ujian',
                'title' => "Pengajuan Kartu Ujian {$jenisUjianUpper} Ditolak",
                'message_content' => 'Pengajuan ditolak. Alasan: '.($this->pengajuan->keterangan ?? '-'),
                'pengajuan_id' => $this->pengajuan->id,
                'status' => $this->pengajuan->status,
                'keterangan' => $this->pengajuan->keterangan,
                'jenis_ujian' => $this->pengajuan->jenis_ujian,
            ];

            $pesan = "*SIA POLSA NOTIFICATION BOT* 🤖\n"
                ."══════════════════════════\n"
                ."🔴 *KARTU UJIAN DITOLAK*\n"
                ."══════════════════════════\n"
                ."• *Mahasiswa:* {$notifiable->nama_lengkap}\n"
                ."• *NIM:* {$notifiable->nim}\n"
                ."• *Ujian:* Kartu Ujian {$jenisUjianUpper}\n\n"
                ."*Alasan Penolakan:*\n"
                .'_"'.($this->pengajuan->keterangan ?? '-')."\"_\n\n"
                ."Silakan unggah kembali bukti pembayaran yang valid melalui portal mahasiswa.\n"
                ."──────────────────────────\n"
                .'📅 _Waktu: '.now()->format('d-m-Y H:i')." WIB_\n"
                ."🌐 _Akses Portal: siapv2.polsa.ac.id_\n"
                .'_SIA POLSA - Sistem Informasi Akademik_';

            if (! empty($notifiable->no_telephone)) {
                WhatsappService::kirim($notifiable->no_telephone, $pesan);
            }

            return $notif;
        }

        return [];
    }
}
