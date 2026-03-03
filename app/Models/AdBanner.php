<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdBanner extends Model
{
   protected $table="adbanners";
   protected $fillable = [
        'image', 
        'sort',
        'start_date',
        'end_date',
        'section'
    ];

    public function getImageAttribute($value)
    {
        return $value ? asset($value) : null;
    }
}
