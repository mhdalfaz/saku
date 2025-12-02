<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'path',
        'original_name',
        'mime_type',
        'size',
        'fileable_id',
        'fileable_type',
    ];

    protected $appends = ['public_url'];

    public function getPublicUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    public function fileable()
    {
        return $this->morphTo();
    }
}
