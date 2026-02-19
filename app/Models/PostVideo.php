<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostVideo extends Model
{
    protected $table="post_videos";
   protected $fillable = ['post_id', 'video_path','display_order'];

    public function post()
    {
        return $this->belongsTo(Post::class,'post_id');
    }

    // public function post()
    // {
    //     return $this->belongsTo(Post::class);
    // }
}
