<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id','sku','price','sale_price','stock'
    ];
     protected $casts = [
        'price' => 'float',
        'sale_price' => 'float',
        'stock' => 'integer'
    ];

   
 public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        if (filter_var($this->image, FILTER_VALIDATE_URL)) return $this->image;
        return asset($this->image);
    }

    protected $appends = ['image_url'];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'variant_attribute_values',
            'variant_id',
            'attribute_value_id'
        );
    }
}