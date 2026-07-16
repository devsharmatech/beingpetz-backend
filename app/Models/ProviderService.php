<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderService extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'category',
        'name',
        'description',
        'price',
        'duration_minutes',
        'cover_image',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'duration_minutes' => 'integer'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
