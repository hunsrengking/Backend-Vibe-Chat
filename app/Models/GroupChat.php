<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'passcode',
        'creator_id',
    ];

    protected $hidden = [
        'passcode',
    ];

    public function creator()
    {
        return $this->belongsTo(Guest::class, 'creator_id');
    }

    public function members()
    {
        return $this->belongsToMany(Guest::class, 'group_chat_members', 'group_chat_id', 'guest_id')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function joinRequests()
    {
        return $this->hasMany(GroupChatRequest::class);
    }
}
