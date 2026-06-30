<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id', 'image', 'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        if (filter_var($this->image, FILTER_VALIDATE_URL)) return $this->image;
        return asset($this->image);
    }

    protected $appends = ['image_url'];
}