<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestWinner extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'contest_id',
        'entry_id',
        'position',
        'prize',
        'prize_amount'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function entry()
    {
        return $this->belongsTo(ContestEntry::class);
    }
}