<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $fillable = [
        'user_id', 'name', 'gender', 'type', 'breed', 'dob', 'bio', 'avatar',
    ];
    
    public function user()
    {
    return $this->belongsTo(User::class,'user_id');
    }
   
}
