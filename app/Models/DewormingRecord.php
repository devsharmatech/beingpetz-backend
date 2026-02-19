<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DewormingRecord extends Model
{
    protected $table = 'deworming_records';
    protected $fillable = [
        'pet_id', 'date', 'deworming_type', 'image_path',
        'reminder_date', 'reminder_time', 'bg_color'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}
