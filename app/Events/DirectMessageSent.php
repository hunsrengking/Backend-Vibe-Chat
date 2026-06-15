<?php

namespace App\Events;

use App\Models\DirectMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DirectMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public DirectMessage $message;

    /**
     * Create a new event instance.
     */
    public function __construct(DirectMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('direct-chat.' . $this->message->receiver_id),
            new PrivateChannel('direct-chat.' . $this->message->sender_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'content' => $this->message->content,
            'media_url' => $this->message->media_url,
            'media_type' => $this->message->media_type,
            'is_read' => (bool)$this->message->is_read,
            'created_at' => $this->message->created_at->toIso8601String(),
            'sender' => [
                'id' => $this->message->sender->id,
                'nickname' => $this->message->sender->nickname,
                'avatar_url' => $this->message->sender->avatar_url,
            ],
            'receiver' => [
                'id' => $this->message->receiver->id,
                'nickname' => $this->message->receiver->nickname,
                'avatar_url' => $this->message->receiver->avatar_url,
            ],
        ];
    }
}
