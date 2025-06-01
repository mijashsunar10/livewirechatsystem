<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageReaction extends Model
{
    protected $fillable = ['message_id', 'user_id', 'reaction'];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
        // Explicitly specify the foreign key
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}