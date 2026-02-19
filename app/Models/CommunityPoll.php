<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityPoll extends Model
{
    

    protected $fillable = ['message_id', 'question'];

    public function options()
    {
        return $this->hasMany(CommunityPollOption::class, 'poll_id');
    }
}