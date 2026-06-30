<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityMessage extends Model
{
    protected $fillable = [
        'community_id', 'parent_id','message_id','isReply', 'message_type', 'message_text', 'media_path'
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'parent_id');
    }
    public function old_message()
    {
        return $this->belongsTo(CommunityMessage::class,'message_id');
    }
    public function poll()
    {
        return $this->hasOne(CommunityPoll::class,'message_id');
    }
    public function likes()
    {
      return $this->hasMany(CommunityMessageLike::class, 'message_id');
    }

}