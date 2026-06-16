<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $currentUser = $request->user();
        
        return [
            'id' => $this->id,
            'content' => $this->content,
            'media_url' => $this->media_path ? Storage::disk(config('filesystems.media'))->url($this->media_path) : null,
            'media_type' => $this->media_type,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count ?? $this->comments()->count(),
            'guest' => new GuestResource($this->whenLoaded('guest')),
            'is_liked' => $currentUser ? $this->likes->contains('guest_id', $currentUser->id) : false,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
