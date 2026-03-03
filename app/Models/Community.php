<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Community extends Model
{
     protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'latitude',
        'longitude',
        'profile',
        'cover_image',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function members()
    {
        return $this->hasMany(CommunityMembership::class, 'community_id');
    }

     public function moderators()
    {
        return $this->hasMany(CommunityModerator::class);
    }

    // Pets directly through members
   public function users()
{
    return $this->belongsToMany(User::class, 'community_memberships', 'community_id', 'parent_id')
                ->withPivot('role')
                ->withTimestamps();
}
    

public function admins()
{
    return $this->users()->wherePivot('role', 'admin');
}

public function superAdmin()
{
    return $this->users()->wherePivot('role', 'super_admin');
}

public function communityMessages()
    {
        return $this->hasMany(CommunityMessage::class);
    }

    // Get all moderators as users
    public function moderatorUsers()
    {
        return $this->belongsToMany(User::class, 'community_moderators', 'community_id', 'user_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    // Get admin (owner)
    public function admin()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProfileAttribute($value)
    {
        if (!$value) {
            return asset('assets/images/community-default.png');
        }
        return asset($value);
    }

    public function getCoverImageAttribute($value)
    {
        if (!$value) {
            return asset('assets/images/community-cover-default.png');
        }
        return asset($value);
    }
}
