<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'path',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'imageable_id',
        'imageable_type',
    ];

    protected $appends = ['public_url'];

    public function getPublicUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    public function imageable()
    {
        return $this->morphTo();
    }
}
