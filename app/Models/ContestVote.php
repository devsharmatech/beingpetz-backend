<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestVote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entry_id',
        'user_id',
        'device_id',
        'pet_id',
        'contest_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function entry()
    {
        return $this->belongsTo(ContestEntry::class, 'entry_id');
    }

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function pet()
{
    return $this->belongsTo(Pet::class, 'pet_id');
}
    
}