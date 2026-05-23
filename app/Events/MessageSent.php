<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->message->jadwal_id) {
            return [
                new PresenceChannel('chat.'.$this->message->jadwal_id),
            ];
        }

        $studentId = $this->message->sender_type === 'App\Models\Mahasiswa'
            ? $this->message->sender_id
            : $this->message->receiver_id;

        return [
            new PresenceChannel('guidance.'.$studentId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // Pastikan kita memuat relasi sender agar di Vue bisa menampilkan nama
        return [
            'message' => $this->message->load('sender'),
        ];
    }
}
