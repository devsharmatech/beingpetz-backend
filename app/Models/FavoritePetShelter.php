<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoritePetShelter extends Model
{
    protected $table="favorite_pet_shelters";
   
    protected $fillable = [
        'user_id',
        'pet_shelter_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function petShelter()
    {
        return $this->belongsTo(PetShelter::class, 'pet_shelter_id');
    }
}
