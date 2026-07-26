<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string  $senderId,
        public string  $receiverId,
        public string  $messageId,
        public string  $body,
        public string  $createdAt,
        public ?string $senderNick    = null,
        public ?string $avatarPhotoId = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("chat.{$this->receiverId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->messageId,
            'sender_id'       => $this->senderId,
            'receiver_id'     => $this->receiverId,
            'body'            => $this->body,
            'created_at'      => $this->createdAt,
            'sender_nick'     => $this->senderNick,
            'avatar_photo_id' => $this->avatarPhotoId,
        ];
    }
}
