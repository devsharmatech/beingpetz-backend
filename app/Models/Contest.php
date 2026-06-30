<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'description',
        'banner',
        'thumbnail',
        'prize',
        'start_date',
        'end_date',
        'result_date',
        'status',
        'max_entries_per_user',
        'allowed_media',
        'entry_fee',
        'views',
        'terms'
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'result_date'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) return null;
        if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) return $this->thumbnail;
        return asset($this->thumbnail);
    }
    public function getBannerUrlAttribute()
    {
        if (!$this->banner) return null;
        if (filter_var($this->banner, FILTER_VALIDATE_URL)) return $this->banner;
        return asset($this->banner);
    }

    protected $appends = ['thumbnail_url','banner_url'];

    public function entries()
    {
        return $this->hasMany(ContestEntry::class);
    }

    public function winners()
    {
        return $this->hasMany(ContestWinner::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES (VERY IMPORTANT FOR API)
    |--------------------------------------------------------------------------
    */

    public function scopeOpen($query)
    {
        return $query->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now());
    }
}