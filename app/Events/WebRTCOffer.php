<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCOffer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $roomId;

    public array $offer;

    public int $from;

    public ?string $sessionId = null;

    public function __construct(string $roomId, array $offer, int $from, ?string $sessionId = null)
    {
        $this->roomId = $roomId;
        $this->offer = $offer;
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
        return 'webrtc-offer';
    }

    public function broadcastWith(): array
    {
        $payload = [
            'roomId' => $this->roomId,
            'offer' => $this->offer,
            'from' => $this->from,
        ];

        if ($this->sessionId !== null && $this->sessionId !== '') {
            $payload['sessionId'] = $this->sessionId;
        }

        return $payload;
    }
}