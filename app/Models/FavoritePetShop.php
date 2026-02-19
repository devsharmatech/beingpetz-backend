<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoritePetShop extends Model
{
    protected $table="favorite_pet_shops";
   
    protected $fillable = [
        'user_id',
        'pet_shop_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function petShop()
    {
        return $this->belongsTo(PetShop::class, 'pet_shop_id');
    }
}
