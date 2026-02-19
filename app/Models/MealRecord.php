<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealRecord extends Model
{
    protected $table = 'meal_records';
    protected $fillable = [
        'pet_id', 'meal_time', 'reminder_date',
        'reminder_time', 'bg_color'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}