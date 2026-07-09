<?php

namespace App\Http\Traits;

use Illuminate\Http\UploadedFile;

trait HandlesUploads
{
    protected function storeUpload(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, 'public');
    }

    protected function attachmentType(UploadedFile $file): string
    {
        $mime = $file->getMimeType();
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'file';
    }
}
