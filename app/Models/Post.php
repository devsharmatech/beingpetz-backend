<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    protected $table="posts";
    
    protected $fillable = [
        'parent_id',
        'slug', 
        'content',
        'is_public',
        'pet_id',
        'post_type',
        'featured_image',
        'repost_id',
        'featured_video',
        'forward_to_user_id',
        'deleted_at'
    ];
    protected $casts = [
    'target_locations' => 'array',
    'target_pet_types' => 'array',
    'target_breeds' => 'array',
];
public function hashtags()
{
    return $this->belongsToMany(
        Hashtag::class,
        'post_hashtags',
        'post_id',
        'hashtag_id'
    )->withTimestamps();
}
    public function images()
    {
        return $this->hasMany(PostImage::class,'post_id');
    }

    public function videos()
    {
        return $this->hasMany(PostVideo::class,'post_id');
    }
    public function pet()
    {
        return $this->belongsTo(Pet::class,'pet_id');
    }
    public function repost()
    {
        return $this->belongsTo(Post::class,'repost_id');
    }
    public function parent()
    {
        return $this->belongsTo(User::class,'parent_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class,'post_id');
    }

    public function reposts()
    {
        return $this->hasMany(Post::class,'repost_id');
    }

    public function shares()
    {
        return $this->hasMany(Share::class,'post_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class,'post_id');
    }
    public function taggedUsers()
    {
        return $this->belongsToMany(User::class, 'post_tags', 'post_id', 'tagged_user_id');
    }
}
