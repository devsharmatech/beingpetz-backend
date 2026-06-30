<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'company_id','category_id','name','slug','description',
        'base_price','sale_price','stock',
        'is_featured','is_trending','is_best_seller','is_new','is_active'
    ];
    protected $casts = [
        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_new' => 'boolean',
        'is_active' => 'boolean',
        'base_price' => 'float',
        'sale_price' => 'float'
    ];
  
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(MarketplaceCategory::class, 'category_id');
    }

    

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    // Get primary image URL
    public function getImageUrlAttribute()
    {
        $primaryImage = $this->images()->where('is_primary', true)->first();
        
        if ($primaryImage && $primaryImage->image) {
            if (filter_var($primaryImage->image, FILTER_VALIDATE_URL)) {
                return $primaryImage->image;
            }
            return asset($primaryImage->image);
        }
        
        return null;
    }

    // Get all gallery images
    public function getGalleryAttribute()
    {
        return $this->images()->orderBy('is_primary', 'desc')->get();
    }

    protected $appends = ['image_url', 'gallery'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}