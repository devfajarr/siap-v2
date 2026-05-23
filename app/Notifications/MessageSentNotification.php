<?php

namespace App\Notifications;

use App\Models\Kelas;
use App\Models\Matkul;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Session;

class MessageSentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
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
        $kelas = $this->message->kelas_id ? Kelas::find($this->message->kelas_id) : null;
        $matkul = $this->message->matkul_id ? Matkul::find($this->message->matkul_id) : null;
        $dari = '';
        if ($this->message->sender_type == 'App\Models\Direktur') {
            $dari = 'Direktur';
        } elseif ($this->message->sender_type == 'App\Models\Wadir') {
            $dari = 'Wakil Direktur';
        } elseif ($this->message->sender_type == 'App\Models\Kaprodi') {
            $dari = 'Kaprodi';
        }

        // Bimbingan Akademik / Perwalian (DPA)
        if (is_null($this->message->jadwal_id)) {
            return [
                'sender_name' => $this->getSenderName(),
                'notification_type' => 'bimbingan',
                'message_content' => $this->message->message,
                'class' => '-',
                'matkul' => '-',
                'title' => 'Pesan Bimbingan Akademik',
            ];
        }

        if (Session::get('user.role') == 'direktur' || Session::get('user.role') == 'wakil_direktur' || Session::get('user.role') == 'kaprodi') {
            return [
                'sender_name' => $this->getSenderName(),
                'notification_type' => 'pemberitahuan',
                'message_content' => $this->message->message,
                'class' => $kelas->nama_kelas ?? '-',
                'matkul' => $matkul->nama_matkul ?? '-',
                'title' => 'Pemberitahuan dari '.$dari,
            ];
        } else {
            return [
                'sender_name' => $this->getSenderName(),
                'notification_type' => 'pemberitahuan',
                'message_content' => $this->message->message,
                'class' => $kelas->nama_kelas ?? '-',
                'matkul' => $matkul->nama_matkul ?? '-',
                'title' => 'Membalas Pemberitahuan',
            ];
        }
    }

    private function getSenderName()
    {
        $senderType = $this->message->sender_type;
        $sender = $senderType::find($this->message->sender_id);

        return $sender->nama_lengkap ?? $sender->name ?? $sender->nama ?? 'Pengirim';
    }
}
