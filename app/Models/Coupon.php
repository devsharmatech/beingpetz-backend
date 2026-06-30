<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code','type','value','min_order_amount','max_discount',
        'usage_limit','used_count','start_date','end_date','is_active'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}