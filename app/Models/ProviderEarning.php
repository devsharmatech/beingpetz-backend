<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderEarning extends Model
{
    protected $fillable = [
        'provider_id',
        'service_booking_id',
        'amount',
        'status'
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function booking()
    {
        return $this->belongsTo(ServiceBooking::class, 'service_booking_id');
    }
}
