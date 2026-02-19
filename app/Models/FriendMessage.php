<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FriendMessage extends Model
{
   

    protected $fillable = [
        'sender_id', 'receiver_id', 'message_type', 'message_text', 'media_path', 'is_seen'
    ];

    public function sender()
    {
        return $this->belongsTo(Pet::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Pet::class, 'receiver_id');
    }
    
}