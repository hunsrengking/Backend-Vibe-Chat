<?php

namespace App\Http\Controllers;

use App\Models\DirectMessage;
use App\Models\Guest;
use Illuminate\Http\Request;

class DirectMessageController extends Controller
{
    /**
     * Get 1-on-1 message logs between auth guest and receiver.
     */
    public function index(Request $request, $receiverId)
    {
        $guestId = $request->user()->id;
        $receiverId = (int) $receiverId;

        // Mark incoming messages as read
        DirectMessage::where('sender_id', $receiverId)
            ->where('receiver_id', $guestId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = DirectMessage::with(['sender', 'receiver'])
            ->where(function ($q) use ($guestId, $receiverId) {
                $q->where('sender_id', $guestId)->where('receiver_id', $receiverId);
            })
            ->orWhere(function ($q) use ($guestId, $receiverId) {
                $q->where('sender_id', $receiverId)->where('receiver_id', $guestId);
            })
            ->orderBy('created_at', 'asc')
            ->take(100)
            ->get();

        return response()->json([
            'data' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'receiver_id' => $msg->receiver_id,
                    'content' => $msg->content,
                    'media_url' => $msg->media_url,
                    'media_type' => $msg->media_type,
                    'is_read' => (bool)$msg->is_read,
                    'created_at' => $msg->created_at->toIso8601String(),
                    'sender' => [
                        'id' => $msg->sender->id,
                        'nickname' => $msg->sender->nickname,
                        'avatar_url' => $msg->sender->avatar_url,
                    ],
                    'receiver' => [
                        'id' => $msg->receiver->id,
                        'nickname' => $msg->receiver->nickname,
                        'avatar_url' => $msg->receiver->avatar_url,
                    ],
                ];
            })
        ]);
    }

    /**
     * Post a new direct message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:guests,id',
            'content' => 'required_without:media|nullable|string|max:500',
            'media' => 'nullable|file|max:20480',
            'media_type' => 'nullable|string|in:voice,image,video',
        ]);

        $guest = $request->user();
        $receiverId = $request->input('receiver_id');
        $content = $request->input('content');
        $mediaFile = $request->file('media');

        // Profanity Check
        if ($content) {
            $spamFilter = app(\App\Services\SpamFilterService::class);
            $spamFilter->checkText($content, 'content');
        }

        $data = [
            'sender_id' => $guest->id,
            'receiver_id' => (int)$receiverId,
            'content' => $content,
        ];

        if ($mediaFile) {
            $path = $mediaFile->store('vibechat/direct_messages', env('FILESYSTEM_DISK', 'public'));
            $data['media_path'] = $path;
            
            $mime = $mediaFile->getClientMimeType();
            if (str_contains($mime, 'video')) {
                $data['media_type'] = 'video';
            } elseif (str_contains($mime, 'audio') || str_contains($mime, 'octet-stream') || str_contains($mime, 'webm')) {
                $data['media_type'] = 'voice';
            } else {
                $data['media_type'] = 'image';
            }
        }

        $message = DirectMessage::create($data);
        $message->load(['sender', 'receiver']);

        // Broadcast to receiver
        broadcast(new \App\Events\DirectMessageSent($message))->toOthers();

        return response()->json([
            'data' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'content' => $message->content,
                'media_url' => $message->media_url,
                'media_type' => $message->media_type,
                'is_read' => (bool)$message->is_read,
                'created_at' => $message->created_at->toIso8601String(),
                'sender' => [
                    'id' => $message->sender->id,
                    'nickname' => $message->sender->nickname,
                    'avatar_url' => $message->sender->avatar_url,
                ],
                'receiver' => [
                    'id' => $message->receiver->id,
                    'nickname' => $message->receiver->nickname,
                    'avatar_url' => $message->receiver->avatar_url,
                ],
            ]
        ]);
    }

    /**
     * Get the active conversations list for the sidebar.
     */
    public function conversations(Request $request)
    {
        $guestId = $request->user()->id;

        $senderIds = DirectMessage::where('receiver_id', $guestId)->pluck('sender_id')->toArray();
        $receiverIds = DirectMessage::where('sender_id', $guestId)->pluck('receiver_id')->toArray();
        
        $participantIds = array_unique(array_merge($senderIds, $receiverIds));

        $participants = Guest::whereIn('id', $participantIds)
            ->where('id', '!=', $guestId)
            ->get();

        $conversations = $participants->map(function ($participant) use ($guestId) {
            $lastMessage = DirectMessage::where(function ($q) use ($guestId, $participant) {
                $q->where('sender_id', $guestId)->where('receiver_id', $participant->id);
            })->orWhere(function ($q) use ($guestId, $participant) {
                $q->where('sender_id', $participant->id)->where('receiver_id', $guestId);
            })
            ->orderBy('created_at', 'desc')
            ->first();

            $unreadCount = DirectMessage::where('sender_id', $participant->id)
                ->where('receiver_id', $guestId)
                ->where('is_read', false)
                ->count();

            return [
                'id' => $participant->id,
                'nickname' => $participant->nickname,
                'avatar_url' => $participant->avatar_url,
                'last_message' => $lastMessage ? [
                    'content' => $lastMessage->content,
                    'media_type' => $lastMessage->media_type,
                    'created_at' => $lastMessage->created_at->toIso8601String(),
                ] : null,
                'unread_count' => $unreadCount,
            ];
        });

        $sortedConversations = $conversations->sortByDesc(function ($c) {
            return $c['last_message'] ? $c['last_message']['created_at'] : '';
        })->values();

        return response()->json(['data' => $sortedConversations]);
    }
}
