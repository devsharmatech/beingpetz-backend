<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class V2PostVideo extends Model
{
    protected $table = 'post_videos';

    protected $fillable = [
        'post_id',
        'video_path',
    ];

    /**
     * Get the post that owns the video.
     */
    public function post()
    {
        return $this->belongsTo(V2Post::class, 'post_id');
    }
}
