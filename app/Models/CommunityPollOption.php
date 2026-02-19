<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityPollOption extends Model
{
    public $timestamps = false;
    protected $fillable = ['poll_id', 'option_text'];

    public function votes()
    {
        return $this->hasMany(CommunityPollVote::class, 'option_id');
    }
}