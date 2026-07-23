<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'name',
        'email',
        'phone',
        'address',
        'is_active',
        'business_name',
        'legal_name',
        'services',
        'area',
        'experience_years',
        'start_pricing',
        'consultation_fee',
        'accepted_pet_types',
        'accepted_pet_sizes',
        'emergency_contact_number',
        'weekly_schedule',
        'primary_gov_doc',
        'alternate_id_doc',
        'proof_of_expertise',
        'work_gallery',
        'video_walkthrough',
        'dpdp_consent',
        'service_specific_data'
    ];

    protected $casts = [
        'services' => 'array',
        'accepted_pet_types' => 'array',
        'accepted_pet_sizes' => 'array',
        'weekly_schedule' => 'array',
        'proof_of_expertise' => 'array',
        'work_gallery' => 'array',
        'service_specific_data' => 'array',
        'dpdp_consent' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function providerServices()
    {
        return $this->hasMany(ProviderService::class, 'provider_id')->where(function ($q) {
            $q->where('is_active', 1)->orWhereNull('is_active');
        });
    }

    public static function findForUser($user)
    {
        if (!$user) return null;

        $provider = static::where('user_id', $user->id)
            ->when($user->email, function ($q) use ($user) { $q->orWhere('email', $user->email); })
            ->when($user->phone, function ($q) use ($user) { $q->orWhere('phone', $user->phone); })
            ->first();

        if ($provider && !$provider->user_id) {
            $provider->user_id = $user->id;
            $provider->save();
        }

        return $provider;
    }
}