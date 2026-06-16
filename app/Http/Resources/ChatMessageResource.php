<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'group_chat_id' => $this->group_chat_id,
            'media_url' => $this->media_path ? Storage::disk(config('filesystems.media'))->url($this->media_path) : null,
            'media_type' => $this->media_type,
            'guest' => new GuestResource($this->whenLoaded('guest')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
