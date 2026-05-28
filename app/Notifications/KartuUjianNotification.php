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

            $pesan = "*Pengajuan Kartu Ujian {$jenisUjianUpper} Disetujui*\n\n"
                ."Kartu Ujian fisik Anda sudah *selesai dicetak*.\n"
                ."Silakan ambil kartu ujian Anda di *Bagian Akademik*.\n";

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

            $pesan = "*Pengajuan Kartu Ujian {$jenisUjianUpper} Ditolak*\n\n"
                .'Alasan: '.($this->pengajuan->keterangan ?? '-')."\n"
                ."Silakan unggah kembali bukti pembayaran yang valid melalui sistem.\n";

            if (! empty($notifiable->no_telephone)) {
                WhatsappService::kirim($notifiable->no_telephone, $pesan);
            }

            return $notif;
        }

        return [];
    }
}
