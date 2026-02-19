<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'name',
        'email',
        'phone',
        'address',
        'is_active'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}