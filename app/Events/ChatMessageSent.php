<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatMessage $message;

    /**
     * Create a new event instance.
     */
    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        if ($this->message->group_chat_id) {
            return [
                new PresenceChannel('group-chat.' . $this->message->group_chat_id),
            ];
        }
        return [
            new PresenceChannel('chat'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'group_chat_id' => $this->message->group_chat_id,
            'media_url' => $this->message->media_path ? \Storage::disk('public')->url($this->message->media_path) : null,
            'media_type' => $this->message->media_type,
            'created_at' => $this->message->created_at->toIso8601String(),
            'guest' => [
                'id' => $this->message->guest->id,
                'nickname' => $this->message->guest->nickname,
                'avatar_url' => $this->message->guest->avatar_url,
                'is_admin' => $this->message->guest->is_admin,
            ],
        ];
    }
}
