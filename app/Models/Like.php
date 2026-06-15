<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'guest_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
