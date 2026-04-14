<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingCall implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        $receiverType = $this->data['receiverType'];
        $receiverId = $this->data['receiverId'];

        return [
            new PrivateChannel("user-{$receiverType}-{$receiverId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'incoming-call';
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}