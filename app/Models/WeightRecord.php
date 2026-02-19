<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeightRecord extends Model
{
    protected $table = 'weight_records';
    protected $fillable = [
        'pet_id', 'date', 'weight', 'bg_color'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}