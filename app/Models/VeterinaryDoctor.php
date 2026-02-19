<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VeterinaryDoctor extends Model
{
    protected $table="veterinary_doctors";
    protected $fillable = [
        'clinic_name',
        'veterinarian_name',
        'contact_number',
        'email_address',
        'clinic_location',
        'city',
        'state',
        'pincode',
        'weekday_open',
        'weekday_close',
        'weekend_open',
        'weekend_close',
        'medical_license_number',
        'years_of_experience',
        'special_certification',
        'medical_license_upload',
        'notification_wanted',
        'clinic_picture_logo',
        'status',
    ];
}
