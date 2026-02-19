<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id', 'parent_id', 'message_type', 'message_text', 'media_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'parent_id');
    }
}