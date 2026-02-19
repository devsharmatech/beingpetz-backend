<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Share extends Model
{
   protected $fillable = [
        'post_id', 'parent_id'
    ];
}
