<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupChatRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_chat_id',
        'guest_id',
        'status', // pending, approved, rejected
    ];

    public function groupChat()
    {
        return $this->belongsTo(GroupChat::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
