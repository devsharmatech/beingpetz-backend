<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoritePetResortOwner extends Model
{
    protected $table="favorite_pet_resort_owners";
   
   protected $fillable = [
        'user_id',
        'pet_resort_owner_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function petResortOwner()
    {
        return $this->belongsTo(PetResort::class, 'pet_resort_owner_id');
    }
}
