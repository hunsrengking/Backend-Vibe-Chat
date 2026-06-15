<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'group_chat_id',
        'content',
        'media_path',
        'media_type',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function groupChat()
    {
        return $this->belongsTo(GroupChat::class);
    }
}
