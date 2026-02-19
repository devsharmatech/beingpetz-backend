<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LostFoundReport extends Model
{
   protected $fillable = [
        'user_id',
        'phone',
        'report_type',
        'pet_type',
        'pet_name',
        'pet_gender',
        'breed',
        'pet_dob',
        'about_pet',
        'location',
        'occurred_at',
        'images',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
        'occurred_at' => 'datetime',
        'pet_dob' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
