<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityMessageLike extends Model
{
    protected $fillable = [
        'message_id', 'member_id'
    ];
}