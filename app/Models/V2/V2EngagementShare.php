<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class V2EngagementShare extends Model
{
    protected $table = 'shares';

    protected $fillable = [
        'post_id',
        'parent_id',
        'shared_by_type',
        'shared_by_id',
        'platform',
    ];

    /**
     * Get the post that was shared.
     */
    public function post()
    {
        return $this->belongsTo(V2Post::class, 'post_id');
    }

    /**
     * Get the user who created this share.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'parent_id');
    }

    /**
     * Get the entity (user or pet) who shared.
     */
    public function sharedBy()
    {
        if ($this->shared_by_type === 'pet') {
            return $this->belongsTo(\App\Models\Pet::class, 'shared_by_id');
        }
        return $this->belongsTo(\App\Models\User::class, 'shared_by_id');
    }

    /**
     * Scope by platform.
     */
    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }
}
