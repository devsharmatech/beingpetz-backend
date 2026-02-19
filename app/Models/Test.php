<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Test extends Model
{
   protected $table = 'test'; 
   protected $fillable = [
        'data'
    ];
}
