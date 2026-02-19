<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeletedCommunityMessage extends Model
{
    protected $fillable = [
        'user_id', 'community_message_id'
    ];
}