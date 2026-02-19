<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoritePetSitter extends Model
{
    protected $table="favorite_pet_sitters";
    
    protected $fillable = [
        'user_id',
        'pet_sitter_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function petSitter()
    {
        return $this->belongsTo(PetSitter::class, 'pet_sitter_id');
    }
}
