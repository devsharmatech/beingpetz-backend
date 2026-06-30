<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostHashtag extends Model
{
    protected $table = 'post_hashtags';

    protected $fillable = [
        'post_id',
        'hashtag_id'
    ];

    // 🔗 Relationships
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class);
    }
}