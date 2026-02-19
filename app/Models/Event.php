<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Event extends Model
{
  use HasFactory;

    protected $fillable = [
        'admin_id',
        'category_id',
        'title',
        'slug',
        'description',
        'event_date',
        'location',
        'image',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
