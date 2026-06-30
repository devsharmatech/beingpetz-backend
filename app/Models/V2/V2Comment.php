<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class V2Comment extends Model
{
    use SoftDeletes;

    protected $table = 'comments';

    protected $fillable = [
        'post_id',
        'commented_by_type',
        'commented_by_id',
        'comment',
        'status',
        'moderation_reason',

        // NEW FIELDS (your reply system)
        'is_reply',
        'reply_to_comment_id',
        'reply_to_user_id',
    ];

    /**
     * Get the post this comment belongs to.
     */
    public function post()
    {
        return $this->belongsTo(V2Post::class, 'post_id');
    }

    /**
     * Get the user/pet who commented
     */
    public function commentedBy()
    {
        if ($this->commented_by_type === 'pet') {
            return $this->belongsTo(\App\Models\Pet::class, 'commented_by_id');
        }

        return $this->belongsTo(\App\Models\User::class, 'commented_by_id');
    }
    public function user()
    {
        if ($this->commented_by_type === 'pet') {
            return $this->belongsTo(\App\Models\Pet::class, 'parent_id');
        }

        return $this->belongsTo(\App\Models\User::class, 'parent_id');
    }
    public function commentor()
    {
        if ($this->commented_by_type === 'pet') {
            return $this->belongsTo(\App\Models\Pet::class, 'parent_id');
        }

        return $this->belongsTo(\App\Models\User::class, 'parent_id');
    }

public function likes()
{
    return $this->hasMany(CommentLike::class, 'comment_id');
}

    /**
     * Replies relation (optional but useful)
     */
    public function replies()
    {
        return $this->hasMany(self::class, 'reply_to_comment_id');
    }

    /**
     * Scope for active comments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}