<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Banner extends Model
{
   
    protected $fillable = [
        'link',
        'mobile_image', 
        'desktop_image',
        'sort',
        'start_date',
        'end_date',
        'is_active'
    ];

   
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    // Scope for active banners
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', Carbon::today());
                    })
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', Carbon::today());
                    });
    }

    // Check if banner is currently active based on dates
    public function getIsCurrentlyActiveAttribute()
    {
        if (!$this->is_active) {
            return false;
        }

        $today = Carbon::today();

        if ($this->start_date && $this->start_date->gt($today)) {
            return false;
        }

        if ($this->end_date && $this->end_date->lt($today)) {
            return false;
        }

        return true;
    }

    public function getMobileImageAttribute($value)
    {
        return $value ? asset($value) : null;
    }

    public function getDesktopImageAttribute($value)
    {
        return $value ? asset($value) : null;
    }
}