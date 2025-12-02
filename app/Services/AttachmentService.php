<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = env('FILESYSTEM_DISK', 'local');
    }

    public function handleAttachments($loan, $files)
    {
        foreach ($files as $file) {

            $mime = $file->getMimeType();
            $path = $file->store('uploads/loans', $this->disk);
            if (str_starts_with($mime, 'image/')) {

                $imageInfo = getimagesize($file);

                $loan->images()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mime,
                    'size' => $file->getSize(),
                    'width' => $imageInfo[0] ?? null,
                    'height' => $imageInfo[1] ?? null,
                ]);

            } else {

                $loan->files()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mime,
                    'size' => $file->getSize(),
                ]);
            }
        }
    }
}
