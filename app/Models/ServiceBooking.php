<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
        'service_id',
        'scheduled_at',
        'status',
        'total_amount',
        'payment_status',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'payment_gateway_order_id',
        'notes',
        'pet_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'total_amount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function providerService()
    {
        return $this->belongsTo(ProviderService::class, 'service_id');
    }

    public function review()
    {
        return $this->hasOne(ProviderReview::class, 'service_booking_id');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}
