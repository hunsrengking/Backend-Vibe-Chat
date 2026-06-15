<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $receiverId;
    public Comment $comment;

    /**
     * Create a new event instance.
     */
    public function __construct(int $receiverId, Comment $comment)
    {
        $this->receiverId = $receiverId;
        $this->comment = $comment;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('guest-notifications.' . $this->receiverId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
            'content' => $this->comment->content,
            'commenter_nickname' => $this->comment->guest->nickname,
            'commenter_avatar_url' => $this->comment->guest->avatar_url ?: 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($this->comment->guest->nickname),
            'created_at' => $this->comment->created_at->toIso8601String(),
        ];
    }
}
