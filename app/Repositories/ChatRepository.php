<?php

namespace App\Repositories;

use App\Models\ChatMessage;
use Illuminate\Support\Collection;

class ChatRepository implements ChatRepositoryInterface
{
    public function getRecent(int $limit = 50, ?int $groupChatId = null): Collection
    {
        $query = ChatMessage::with('guest');
        
        if ($groupChatId) {
            $query->where('group_chat_id', $groupChatId);
        } else {
            $query->whereNull('group_chat_id');
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values(); // Re-index keys
    }

    public function create(array $data): ChatMessage
    {
        $message = ChatMessage::create($data);
        return $message->load('guest');
    }
}
