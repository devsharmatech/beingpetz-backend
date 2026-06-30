<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContestEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contest_id',
        'user_id',
        'pet_id',
        'media',
        'caption',
        'votes',
        'score',
        'rank',
        'is_winner',
        'status',
        'rejected_reason',
        'reviewed_at'
    ];

    protected $casts = [
        'is_winner' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
public function getMediaUrlAttribute()
    {
        if (!$this->media) return null;
        if (filter_var($this->media, FILTER_VALIDATE_URL)) return $this->media;
        return asset($this->media);
    }

    protected $appends = ['media_url'];
    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function votes()
    {
        return $this->hasMany(ContestVote::class, 'entry_id');
    }

    public function winner()
    {
        return $this->hasOne(ContestWinner::class, 'entry_id');
    }
}