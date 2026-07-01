<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'gender',
        'type',
        'breed',
        'dob',
        'bio',
        'avatar',
        // V2 enhanced fields
        'pet_unique_id',
        'age',
        'blood_group',
        'microchip_number',
        'insurance_number',
        'insurance_provider',
        'govt_license_number',
    ];
    
    public function user()
    {
    return $this->belongsTo(User::class,'user_id');
    }
   
}
