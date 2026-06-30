<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceCategory extends Model
{
    protected $table = 'marketplace_categories';

    protected $fillable = [
        'name','slug','image','parent_id','is_active'
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
    
    /**
     * OR customize the JSON serialization
     */
    public function toArray()
    {
        $array = parent::toArray();
        $array['image_url'] = $this->image_url;
        return $array;
    }
    public function parent()
    {
        return $this->belongsTo(MarketplaceCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MarketplaceCategory::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}