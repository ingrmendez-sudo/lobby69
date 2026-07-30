<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class VideoSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly int     $toUserId,
        public readonly int     $fromUserId,
        public readonly string  $type,
        public readonly mixed   $payload,
        public readonly ?string $token = null,
        public readonly ?int    $maxDurationSeconds = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("video.user.{$this->toUserId}")];
    }

    public function broadcastAs(): string
    {
        return 'VideoSignal';
    }

    public function broadcastWith(): array
    {
        return [
            'from_user_id'         => $this->fromUserId,
            'type'                 => $this->type,
            'payload'              => $this->payload,
            'token'                => $this->token,
            'max_duration_seconds' => $this->maxDurationSeconds,
        ];
    }
}
