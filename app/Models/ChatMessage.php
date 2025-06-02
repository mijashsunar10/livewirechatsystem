<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'message', 'reply_to'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function repliedMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to');
    }

    public function replies()
    {
        return $this->hasMany(ChatMessage::class, 'reply_to');
    }
}