<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdoptionListing extends Model
{
   protected $fillable = [
    'user_id', 'pet_name', 'slug', 'pet_type', 'breed', 'gender', 'dob',
    'description', 'about_pet', 'is_healthy', 'vaccination_done',
    'location', 'latitude', 'longitude', 'contact_phone', 'contact_email',
    'featured_image', 'status', 'published_at'
];

public function galleryImages()
{
    return $this->hasMany(AdoptionListingImage::class, 'listing_id');
}

public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

}
