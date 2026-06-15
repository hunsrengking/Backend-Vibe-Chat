<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'media_path',
        'media_type',
        'is_read',
    ];

    protected $appends = ['media_url'];

    public function sender()
    {
        return $this->belongsTo(Guest::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Guest::class, 'receiver_id');
    }

    /**
     * Get the full URL for media attachments.
     */
    public function getMediaUrlAttribute()
    {
        return $this->media_path ? asset('storage/' . $this->media_path) : null;
    }
}
