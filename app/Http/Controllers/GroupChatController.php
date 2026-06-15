<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupChatResource;
use App\Models\GroupChat;
use App\Repositories\GroupChatRepositoryInterface;
use Illuminate\Http\Request;

class GroupChatController extends Controller
{
    protected GroupChatRepositoryInterface $groupChatRepository;

    public function __construct(GroupChatRepositoryInterface $groupChatRepository)
    {
        $this->groupChatRepository = $groupChatRepository;
    }

    /**
     * Display a listing of group chats.
     */
    public function index(Request $request)
    {
        $guest = $request->user();
        $groups = $this->groupChatRepository->getAll($guest ? $guest->id : null);
        return GroupChatResource::collection($groups);
    }

    /**
     * Create a new group chat room.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:50',
            'type' => 'required|string|in:public,protected,private',
            'passcode' => 'required_if:type,protected|nullable|string|min:3|max:20',
        ]);

        $guest = $request->user();
        
        $group = $this->groupChatRepository->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'passcode' => $validated['passcode'] ?? null,
            'creator_id' => $guest->id,
        ]);

        return new GroupChatResource($group);
    }

    /**
     * Join an existing group chat room.
     */
    public function join(Request $request, $groupId)
    {
        $validated = $request->validate([
            'passcode' => 'nullable|string',
        ]);

        $guest = $request->user();
        $result = $this->groupChatRepository->join((int) $groupId, $guest->id, $validated['passcode'] ?? null);

        if ($result['status'] === 'error') {
            return response()->json([
                'error' => $result['message']
            ], 422);
        }

        return response()->json($result);
    }

    /**
     * Get pending join requests for a group chat (Creator Only).
     */
    public function getRequests(Request $request, $groupId)
    {
        $group = GroupChat::findOrFail($groupId);
        
        // Authorization: Only the creator can view requests
        if ($group->creator_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $requests = $this->groupChatRepository->getPendingRequests((int) $groupId);

        return response()->json([
            'data' => $requests->map(function ($req) {
                return [
                    'id' => $req->id,
                    'status' => $req->status,
                    'created_at' => $req->created_at->toIso8601String(),
                    'guest' => [
                        'id' => $req->guest->id,
                        'nickname' => $req->guest->nickname,
                        'avatar_url' => $req->guest->avatar_url,
                    ]
                ];
            })
        ]);
    }

    /**
     * Handle approval or rejection of a join request (Creator Only).
     */
    public function handleRequest(Request $request, $groupId, $requestId)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:approved,rejected',
        ]);

        $group = GroupChat::findOrFail($groupId);
        
        // Authorization: Only the creator can resolve requests
        if ($group->creator_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $this->groupChatRepository->updateRequestStatus((int) $groupId, (int) $requestId, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'Request ' . $validated['status'] . ' successfully.'
        ]);
    }
}
