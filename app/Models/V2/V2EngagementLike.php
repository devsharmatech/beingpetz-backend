<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class V2EngagementLike extends Model
{
    protected $table = 'likes';

    protected $fillable = [
        'post_id',
        'parent_id',
        'liked_by_type',
        'liked_by_id',
    ];

    /**
     * Get the post that was liked.
     */
    public function post()
    {
        return $this->belongsTo(V2Post::class, 'post_id');
    }

    /**
     * Get the user who created this like.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'parent_id');
    }

    /**
     * Get the entity (user or pet) who liked.
     */
    public function likedBy()
    {
        if ($this->liked_by_type === 'pet') {
            return $this->belongsTo(\App\Models\Pet::class, 'liked_by_id');
        }
        return $this->belongsTo(\App\Models\User::class, 'liked_by_id');
    }
}
