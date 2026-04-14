<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCAnswer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $roomId;

    public array $answer;

    public int $from;

    public ?string $sessionId = null;

    public function __construct(string $roomId, array $answer, int $from, ?string $sessionId = null)
    {
        $this->roomId = $roomId;
        $this->answer = $answer;
        $this->from = $from;
        $this->sessionId = $sessionId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("call-{$this->roomId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'webrtc-answer';
    }

    public function broadcastWith(): array
    {
        $payload = [
            'roomId' => $this->roomId,
            'answer' => $this->answer,
            'from' => $this->from,
        ];

        if ($this->sessionId !== null && $this->sessionId !== '') {
            $payload['sessionId'] = $this->sessionId;
        }

        return $payload;
    }
}