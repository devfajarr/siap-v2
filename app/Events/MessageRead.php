<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageIds;

    public $jadwalId;

    public $readerId;

    public $readerType;

    public $studentId;

    /**
     * Create a new event instance.
     */
    public function __construct($messageIds, $jadwalId, $readerId, $readerType, $studentId = null)
    {
        $this->messageIds = $messageIds;
        $this->jadwalId = $jadwalId;
        $this->readerId = $readerId;
        $this->readerType = $readerType;
        $this->studentId = $studentId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->jadwalId) {
            return [
                new PresenceChannel('chat.'.$this->jadwalId),
            ];
        }

        return [
            new PresenceChannel('guidance.'.$this->studentId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'MessageRead';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message_ids' => $this->messageIds,
            'reader_id' => $this->readerId,
            'reader_type' => $this->readerType,
        ];
    }
}
