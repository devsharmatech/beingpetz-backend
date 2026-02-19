<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FriendRequest extends Model
{
  protected $fillable = [
        'from_parent_id',
        'to_parent_id',
        'status',
    ];
    public function fromParent()
    {
        return $this->belongsTo(User::class, 'from_parent_id');
    }

    public function toParent()
    {
        return $this->belongsTo(User::class, 'to_parent_id');
    }
}
