<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralRecord extends Model
{
    protected $table = 'general_records';
    protected $fillable = [
        'pet_id', 'date', 'time', 'notes', 'bg_color'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}