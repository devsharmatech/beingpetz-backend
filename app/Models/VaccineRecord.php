<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VaccineRecord extends Model
{
    protected $table = 'vaccine_records';
    protected $fillable = [
        'pet_id', 'date', 'vaccine_name', 'type', 'image_path',
        'reminder_date', 'reminder_time', 'bg_color','next_vaccine'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}
