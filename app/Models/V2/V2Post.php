<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
class V2Post extends Model
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active_not_deleted', function ($builder) {
            // According to USER: deleted_at = 1 means active, deleted_at = 0 means deleted.
            $builder->where('deleted_at', 1)->where('status', 'active');
        });
    }

    protected $table = 'posts';

    protected $fillable = [
        'parent_id',
        'slug',
        'posted_by_type',
        'posted_by_id',
        'content',
        'is_public',
        'pet_id',
        'featured_image',
        'featured_video',
        'media_urls',
        'status',
        'moderation_reason',
        'feeling',
        'activity',
    ];

    protected $casts = [
        'media_urls' => 'array',
    ];


public function hashtags()
{
    return $this->belongsToMany(
        \App\Models\Hashtag::class,
        'post_hashtags',
        'post_id',
        'hashtag_id'
    )->withTimestamps();
}

    /**
     * Get the user who created this post.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'parent_id');
    }

    /**
     * Get the pet associated with this post.
     */
    public function pet()
    {
        return $this->belongsTo(\App\Models\Pet::class, 'posted_by_id');
    }

    /**
     * Legacy alias for user relationship used in V1.
     */
    public function parent()
    {
        return $this->user();
    }

    /**
     * Get the repost related to this post.
     */
    public function repost()
    {
        // Bypass global scope for original posts in case they have deleted_at=0 
        // but were still reposted.
        return $this->belongsTo(V2Post::class, 'repost_id')->withoutGlobalScope('active_not_deleted');
    }

    /**
     * Get the entity (user or pet) who posted this.
     */
    public function postedBy()
    {
        // Use standard parent_id for user posts
        if ($this->posted_by_type === 'pet') {
            return $this->belongsTo(\App\Models\Pet::class, 'posted_by_id');
        }
        return $this->belongsTo(\App\Models\User::class, 'parent_id');
    }

    /**
     * Get comments for this post.
     */
    public function comments()
    {
        return $this->hasMany(V2Comment::class, 'post_id');
    }

    /**
     * Get active comments for this post.
     */
    public function activeComments()
    {
        return $this->hasMany(V2Comment::class, 'post_id')->where('status', 'active');
    }

    /**
     * Get likes for this post.
     */
    public function likes()
    {
        return $this->hasMany(V2EngagementLike::class, 'post_id');
    }

    /**
     * Get shares for this post.
     */
    public function shares()
    {
        return $this->hasMany(V2EngagementShare::class, 'post_id');
    }

    /**
     * Get reposts for this post.
     */
    public function reposts()
    {
        return $this->hasMany(V2Post::class, 'repost_id');
    }

    /**
     * Get images for this post.
     */
    public function images()
    {
        return $this->hasMany(V2PostImage::class, 'post_id');
    }

    /**
     * Get videos for this post.
     */
    public function videos()
    {
        return $this->hasMany(V2PostVideo::class, 'post_id');
    }

    /**
     * Get tagged users for this post.
     */
    public function taggedUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'post_tags', 'post_id', 'tagged_user_id');
    }

    /**
     * Scope for active posts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for posts by parent.
     */
    // public function scopeByParent($query, $parentId)
    // {
    //     return $query->where('posted_by_type', 'parent')->where('posted_by_id', $parentId);
    // }

    /**
     * Scope for posts by pet.
     */
    // public function scopeByPet($query, $petId)
    // {
    //     return $query->where('posted_by_type', 'pet')->where('posted_by_id', $petId);
    // }
}
