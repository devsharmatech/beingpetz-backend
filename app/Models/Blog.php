<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Blog extends Model
{
  use HasFactory;

    protected $fillable = [
        'admin_id',
        'category_id',
        'title',
        'short_description',
        'slug',
        'content',
        'image',
        'author_name',
        'author_link',
        'published_at',
    ];

     protected $casts = [
        'published_at' => 'datetime',
    ];
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the blog author's name.
     * Defaults to "Being Petz Admin" if author_name is null/empty.
     */
    public function getAuthorNameAttribute($value)
    {
        return $value ?: 'Being Petz Admin';
    }
}
