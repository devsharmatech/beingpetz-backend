<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoritePetTrainer extends Model
{
    protected $table="favorite_pet_trainers";
    
    protected $fillable = [
        'user_id',
        'pet_trainer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function petTrainer()
    {
        return $this->belongsTo(PetTrainer::class, 'pet_trainer_id');
    }
}
