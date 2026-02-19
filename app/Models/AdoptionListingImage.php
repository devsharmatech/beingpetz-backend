<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdoptionListingImage extends Model
{
   protected $fillable = ['listing_id','image_path','display_order'];
}
