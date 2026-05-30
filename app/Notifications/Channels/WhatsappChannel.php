<?php

namespace App\Notifications\Channels;

use App\Services\WhatsappService;
use Illuminate\Notifications\Notification;

class WhatsappChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     */
    public function send($notifiable, Notification $notification): void
    {
        // Pastikan penerima memiliki nomor telepon dan sudah diverifikasi WhatsApp-nya
        if (empty($notifiable->whatsapp_verified_at) || empty($notifiable->no_telephone)) {
            return;
        }

        $message = null;

        // Cek apakah notifikasi mendukung metode toWhatsapp
        if (method_exists($notification, 'toWhatsapp')) {
            $message = $notification->toWhatsapp($notifiable);
        } elseif (method_exists($notification, 'toArray')) {
            // Fallback: gunakan message_content dari representation array jika ada
            $data = $notification->toArray($notifiable);
            if (is_array($data)) {
                $title = $data['title'] ?? 'Pemberitahuan';
                $content = $data['message_content'] ?? '';
                if (! empty($content)) {
                    $message = "*{$title}*\n\n{$content}";
                }
            }
        }

        if (! empty($message)) {
            WhatsappService::kirim($notifiable->no_telephone, $message);
        }
    }
}
