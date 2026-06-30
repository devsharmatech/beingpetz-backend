<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class V2PostImage extends Model
{
    protected $table = 'post_images';

    protected $fillable = [
        'post_id',
        'image_path',
    ];

    /**
     * Get the post that owns the image.
     */
    public function post()
    {
        return $this->belongsTo(V2Post::class, 'post_id');
    }
}
