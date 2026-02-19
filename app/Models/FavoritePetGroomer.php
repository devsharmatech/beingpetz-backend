<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FavoritePetGroomer extends Model
{
    protected $table="favorite_pet_groomers";
   
    protected $fillable = [
        'user_id',
        'pet_groomer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function petGroomer()
    {
        return $this->belongsTo(PetGroomer::class, 'pet_groomer_id');
    }
}
