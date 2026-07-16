<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'message',
        'is_from_admin'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
