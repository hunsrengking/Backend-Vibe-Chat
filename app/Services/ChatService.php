<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Guest;
use App\Repositories\ChatRepositoryInterface;
use App\Events\ChatMessageSent;
use Illuminate\Support\Collection;

class ChatService
{
    protected ChatRepositoryInterface $chatRepository;
    protected SpamFilterService $spamFilter;

    public function __construct(ChatRepositoryInterface $chatRepository, SpamFilterService $spamFilter)
    {
        $this->chatRepository = $chatRepository;
        $this->spamFilter = $spamFilter;
    }

    public function getRecentMessages(int $limit = 50, ?int $groupChatId = null): Collection
    {
        return $this->chatRepository->getRecent($limit, $groupChatId);
    }

    public function sendMessage(array $data, Guest $guest, $mediaFile = null): ChatMessage
    {
        // Profanity Check (only if content is present)
        if (isset($data['content']) && !empty($data['content'])) {
            $this->spamFilter->checkText($data['content'], 'content');
        }

        $data['guest_id'] = $guest->id;

        // Process media file if uploaded
        if ($mediaFile) {
            $path = $mediaFile->store('vibechat/chat', env('FILESYSTEM_DISK', 'public'));
            $data['media_path'] = $path;
            
            // Auto detect or use provided media type
            if (!isset($data['media_type'])) {
                $mime = $mediaFile->getClientMimeType();
                if (str_contains($mime, 'video')) {
                    $data['media_type'] = 'video';
                } elseif (str_contains($mime, 'audio') || str_contains($mime, 'octet-stream') || str_contains($mime, 'webm')) {
                    // media recorders can record as video/webm or audio/webm or octet-stream
                    $data['media_type'] = 'voice';
                } else {
                    $data['media_type'] = 'image';
                }
            }
        }

        $message = $this->chatRepository->create($data);

        // Broadcast the event
        broadcast(new ChatMessageSent($message))->toOthers();

        return $message;
    }
}
