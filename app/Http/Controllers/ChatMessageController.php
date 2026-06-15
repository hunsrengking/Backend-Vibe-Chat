<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateChatMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get recent chat messages.
     */
    public function index(Request $request)
    {
        $limit = $request->query('limit', 50);
        $groupChatId = $request->query('group_chat_id');

        if ($groupChatId) {
            $groupChatRepo = app(\App\Repositories\GroupChatRepositoryInterface::class);
            if (!$groupChatRepo->isMember((int)$groupChatId, $request->user()->id)) {
                return response()->json(['error' => 'Forbidden. You are not a member of this group chat.'], 403);
            }
        }

        $messages = $this->chatService->getRecentMessages($limit, $groupChatId ? (int)$groupChatId : null);
        
        return ChatMessageResource::collection($messages);
    }

    /**
     * Post a chat message.
     */
    public function store(CreateChatMessageRequest $request)
    {
        $guest = $request->user();
        $groupChatId = $request->input('group_chat_id');

        if ($groupChatId) {
            $groupChatRepo = app(\App\Repositories\GroupChatRepositoryInterface::class);
            if (!$groupChatRepo->isMember((int)$groupChatId, $guest->id)) {
                return response()->json(['error' => 'Forbidden. You are not a member of this group chat.'], 403);
            }
        }

        $mediaFile = $request->file('media');
        $message = $this->chatService->sendMessage($request->validated(), $guest, $mediaFile);
        
        return new ChatMessageResource($message);
    }
}
