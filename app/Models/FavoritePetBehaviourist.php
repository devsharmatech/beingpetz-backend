<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FavoritePetBehaviourist extends Model
{
    protected $table="favorite_pet_behaviourists";
    
    protected $fillable = [
        'user_id',
        'pet_behaviourist_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function petBehaviourist()
    {
        return $this->belongsTo(PetBehaviourist::class, 'pet_behaviourist_id');
    }
}
