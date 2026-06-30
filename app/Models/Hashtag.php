<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    protected $table = 'hashtags';

    protected $fillable = [
        'name',
        'usage_count'
    ];

    // 🔗 Relationship: Hashtag -> Posts (Many to Many)
    public function posts()
    {
        return $this->belongsToMany(
            \App\Models\V2\V2Post::class,
            'post_hashtags',
            'hashtag_id',
            'post_id'
        )->withTimestamps();
    }

    // 🔥 Auto format before saving
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($hashtag) {
            $hashtag->name = strtolower($hashtag->name);
        });
    }
}