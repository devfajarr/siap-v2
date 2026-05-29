<?php

namespace App\Notifications;

use App\Models\Admin;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Session;

class PembayaranNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $pembayaran;

    public function __construct($pembayaran)
    {
        $this->pembayaran = $pembayaran;
    }

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
        if (Session::get('user.role') == 'mahasiswa') {
            $notif = [
                'notification_type' => 'pembayaran',
                'title' => 'Konfirmasi Pembayaran Diperlukan',
                'message_content' => 'Menunggu Konfirmasi',
                'name' => $this->pembayaran->mahasiswa->nama_lengkap,
                'class' => $this->pembayaran->mahasiswa->kelas->nama_kelas,
                'prodi' => $this->pembayaran->prodi->nama_prodi,
                'pembayaran_id' => $this->pembayaran->id,
                'status' => $this->pembayaran->status_pembayaran,
                'keterangan' => $this->pembayaran->keterangan,
            ];

            $pesan = "*Konfirmasi Pembayaran Baru*\n\n"
                ."Nama   : *{$notif['name']}*\n"
                ."Kelas  : {$notif['class']}\n"
                ."Prodi  : {$notif['prodi']}\n"
                .'Mohon segera lakukan konfirmasi.';

            $receiver = Admin::all();
            foreach ($receiver as $admin) {
                if (! empty($admin->no_telephone)) {
                    WhatsappService::kirim($admin->no_telephone, $pesan);
                }
            }

            return $notif;
        } elseif (Session::get('user.role') == 'admin') {
            if ($this->pembayaran->keterangan == 'Belum' && $this->pembayaran->status_pembayaran == 0) {
                $notif = [
                    'notification_type' => 'pembayaran',
                    'title' => 'Pembayaran Ditolak',
                    'message_content' => 'Segera Hubungi Akademik',
                    'pembayaran_id' => $this->pembayaran->id,
                    'status' => $this->pembayaran->status_pembayaran,
                    'keterangan' => $this->pembayaran->keterangan,
                ];

                $pesan = "*Pembayaran Ditolak*\n\n"
                    ."Silakan segera hubungi *Bagian Akademik* untuk klarifikasi.\n";

                WhatsappService::kirim($this->pembayaran->mahasiswa->no_telephone, $pesan);

                return $notif;
            } elseif ($this->pembayaran->keterangan == 'Sudah' && $this->pembayaran->status_pembayaran == 1) {
                $notif = [
                    'notification_type' => 'pembayaran',
                    'title' => 'Pembayaran Berhasil Diterima',
                    'message_content' => 'Segera lakukan Pengajuan KRS',
                    'pembayaran_id' => $this->pembayaran->id,
                    'status' => $this->pembayaran->status_pembayaran,
                    'keterangan' => $this->pembayaran->keterangan,
                ];

                $pesan = "*Pembayaran Berhasil Diterima*\n\n"
                    ."Pembayaran sudah *berhasil diverifikasi*.\n"
                    ."Silakan segera lakukan *Pengajuan KRS*.\n";

                WhatsappService::kirim($this->pembayaran->mahasiswa->no_telephone, $pesan);

                return $notif;
            }
        }

        return [];
    }
}
