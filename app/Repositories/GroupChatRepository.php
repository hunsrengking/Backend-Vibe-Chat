<?php

namespace App\Repositories;

use App\Models\GroupChat;
use App\Models\GroupChatRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GroupChatRepository implements GroupChatRepositoryInterface
{
    public function getAll(?int $guestId = null): Collection
    {
        $query = GroupChat::with('creator')
            ->withCount('members');
        
        if ($guestId) {
            $query->with(['members' => function ($q) use ($guestId) {
                $q->where('guest_id', $guestId);
            }, 'joinRequests' => function ($q) use ($guestId) {
                $q->where('guest_id', $guestId);
            }]);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function create(array $data): GroupChat
    {
        $group = GroupChat::create([
            'name' => $data['name'],
            'type' => $data['type'] ?? 'public',
            'passcode' => isset($data['passcode']) ? Hash::make($data['passcode']) : null,
            'creator_id' => $data['creator_id'],
        ]);

        // Automatically attach creator as member
        $group->members()->attach($data['creator_id']);

        return $group->load('creator')->loadCount('members');
    }

    public function join(int $groupChatId, int $guestId, ?string $passcode = null): array
    {
        $group = GroupChat::findOrFail($groupChatId);
        
        // If already a member, return success
        if ($group->members()->where('guest_id', $guestId)->exists()) {
            return [
                'status' => 'success',
                'message' => 'Already a member of this group chat.'
            ];
        }

        // Passcode protection checks
        if ($group->type === 'protected') {
            if (!$passcode || !Hash::check($passcode, $group->passcode)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid room passcode.'
                ];
            }
        }

        // Creator approval checks
        if ($group->type === 'private') {
            $request = GroupChatRequest::firstOrCreate([
                'group_chat_id' => $groupChatId,
                'guest_id' => $guestId,
            ], [
                'status' => 'pending'
            ]);

            return [
                'status' => 'pending',
                'message' => 'Request submitted. Access is pending creator approval.',
                'request_status' => $request->status
            ];
        }

        // For public and correct passcode protected: join directly
        $group->members()->attach($guestId);

        return [
            'status' => 'success',
            'message' => 'Joined group chat successfully.'
        ];
    }

    public function getPendingRequests(int $groupChatId): Collection
    {
        return GroupChatRequest::with('guest')
            ->where('group_chat_id', $groupChatId)
            ->where('status', 'pending')
            ->get();
    }

    public function updateRequestStatus(int $groupChatId, int $requestId, string $status): bool
    {
        $request = GroupChatRequest::where('group_chat_id', $groupChatId)
            ->findOrFail($requestId);

        $request->update(['status' => $status]);

        if ($status === 'approved') {
            $group = GroupChat::findOrFail($groupChatId);
            if (!$group->members()->where('guest_id', $request->guest_id)->exists()) {
                $group->members()->attach($request->guest_id);
            }
        }

        return true;
    }

    public function isMember(int $groupChatId, int $guestId): bool
    {
        return DB::table('group_chat_members')
            ->where('group_chat_id', $groupChatId)
            ->where('guest_id', $guestId)
            ->exists();
    }
}
