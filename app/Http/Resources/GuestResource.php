<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'guest_token' => $this->guest_token,
            'nickname' => $this->nickname,
            'avatar_url' => $this->avatar_url ?: 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($this->nickname),
            'is_admin' => $this->is_admin,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
