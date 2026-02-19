<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
   protected $fillable = [
        'post_id', 'parent_id','comment'
    ];
    public function user(){
        return $this->belongsTo(User::class,'parent_id');
    }
}
