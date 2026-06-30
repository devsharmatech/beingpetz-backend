<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name','slug','logo','banner','description',
        'email','phone','address','city','state','country','is_active'
    ];
/**
 * Get full logo URL
 */
public function getLogoUrlAttribute()
{
    if (!$this->logo) return null;

    // if already full URL
    if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
        return $this->logo;
    }

    // local file
    return asset($this->logo);
}

/**
 * Get full banner URL
 */
public function getBannerUrlAttribute()
{
    if (!$this->banner) return null;

    // if already full URL
    if (filter_var($this->banner, FILTER_VALIDATE_URL)) {
        return $this->banner;
    }

    // local file
    return asset($this->banner);
}
protected $appends = ['logo_url', 'banner_url'];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}