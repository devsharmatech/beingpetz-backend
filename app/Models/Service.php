<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'providers_count',
        'location',
        'icon',
        'status'
    ];

    protected $casts = [
        'providers_count' => 'integer'
    ];

    public function providers()
    {
        return $this->hasMany(Provider::class);
    }
}