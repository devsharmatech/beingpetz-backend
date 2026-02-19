<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroomingRecord extends Model
{
    protected $table = 'grooming_records';
    protected $fillable = [
        'pet_id', 'date', 'grooming_type', 'image_path',
        'reminder_date', 'reminder_time', 'bg_color','next_grooming'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}
