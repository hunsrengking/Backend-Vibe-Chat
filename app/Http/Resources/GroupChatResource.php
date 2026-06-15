<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        
        $isMember = false;
        if ($user) {
            if ($this->creator_id === $user->id) {
                $isMember = true;
            } elseif ($this->relationLoaded('members')) {
                $isMember = $this->members->contains($user->id);
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type ?? 'public',
            'is_creator' => $user ? ($this->creator_id === $user->id) : false,
            'is_member' => $isMember,
            'request_status' => $user && $this->relationLoaded('joinRequests') ? ($this->joinRequests->first()?->status) : null,
            'creator' => new GuestResource($this->whenLoaded('creator')),
            'members_count' => $this->members_count ?? 0,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
