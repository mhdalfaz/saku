<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        $disk = env('FILESYSTEM_DISK', 'local');
        if ($disk === 'public') {
            return asset('storage/' . $this->path);
        }
        try {
            if ($disk === 's3') {
                $s3 = Storage::disk('s3');
                return $s3->temporaryUrl($this->path, now()->addMinutes(10));
            }
        } catch (\Exception $e) {
            \Log::error($e);
            return null;
        }
    }

    public function fileable()
    {
        return $this->morphTo();
    }
}
