<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceBanner extends Model
{
    protected $fillable = [
        'title','image','link_type','link_id','external_url',
        'section','position','is_active'
    ];
    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;

        // if already full URL
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        // otherwise local file
        return asset($this->image);
    }
    
    /**
     * Append accessors to JSON serialization
     */
    protected $appends = ['image_url'];
    
}