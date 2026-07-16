<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'date',
        'is_blocked',
        'morning_active',
        'afternoon_active',
        'evening_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_blocked' => 'boolean',
        'morning_active' => 'boolean',
        'afternoon_active' => 'boolean',
        'evening_active' => 'boolean'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
