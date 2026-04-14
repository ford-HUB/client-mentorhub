<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAnswered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        $roomId = $this->data['roomId'];

        return [
            new PrivateChannel("call-{$roomId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call-answered';
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}