<?php

namespace App\Services;

class AttachmentService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = env('FILESYSTEM_DISK', 'local');
    }

    public function handleAttachments($model, $files, string $directory = 'uploads/loans')
    {
        foreach ($files as $file) {

            $mime = $file->getMimeType();
            $path = $file->store($directory, $this->disk);
            if (str_starts_with($mime, 'image/')) {

                $imageInfo = getimagesize($file);

                $model->images()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mime,
                    'size' => $file->getSize(),
                    'width' => $imageInfo[0] ?? null,
                    'height' => $imageInfo[1] ?? null,
                ]);

            } else {

                $model->files()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mime,
                    'size' => $file->getSize(),
                ]);
            }
        }
    }
}
