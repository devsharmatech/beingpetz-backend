<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityPollVote extends Model
{
    
public $timestamps = false;
    protected $fillable = ['poll_id', 'option_id', 'parent_id'];
}