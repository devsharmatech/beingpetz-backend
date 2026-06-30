<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
class V2Repost extends Model
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active_not_deleted', function ($builder) {
            // According to USER: deleted_at = 1 means active, deleted_at = 0 means deleted.
            $builder->where('deleted_at', 1);
        });
    }

    protected $table = 'posts';

    protected $fillable = [
        'slug', 
        'pet_id',
        'is_public', 
        'status',
        'moderation_reason', 
        'repost_id', 
        'parent_id', 
        'posted_by_type', 
        'posted_by_id',
        'content',
    ];

    /**
     * Get the original post.
     */
    public function originalPost()
    {
        return $this->belongsTo(V2Post::class, 'repost_id');
    }

    /**
     * Get the user who created this repost.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'parent_id');
    }

    /**
     * Get the entity (user or pet) who reposted.
     */
    public function repostedBy()
    {
        if ($this->posted_by_type === 'pet') {
            return $this->belongsTo(\App\Models\Pet::class, 'posted_by_id');
        }
        return $this->belongsTo(\App\Models\User::class, 'posted_by_id');
    }
}
