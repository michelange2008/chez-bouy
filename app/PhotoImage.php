<?php

namespace App;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PhotoImage
{
    public static function thumbnailPath(string $filename): string
    {
        return 'photos/thumbnails/'.basename($filename);
    }

    public function store(UploadedFile $file): string
    {
        if (! function_exists('imagewebp')) {
            throw ValidationException::withMessages(['image' => __('photos.conversion_unavailable')]);
        }
        $source = @imagecreatefromstring($file->get());
        if (! $source) {
            throw ValidationException::withMessages(['image' => __('photos.conversion_failed')]);
        }
        $filename = 'photos/'.Str::uuid().'.webp';
        try {
            $main = $this->encode($source, config('photos.max_dimension'));
            $thumbnail = $this->encode($source, config('photos.thumbnail_dimension'));
            if (! Storage::disk('local')->put($filename, $main)
                || ! Storage::disk('local')->put(self::thumbnailPath($filename), $thumbnail)) {
                throw ValidationException::withMessages(['image' => __('photos.storage_error')]);
            }
        } catch (Throwable $exception) {
            $this->delete($filename);
            throw $exception;
        } finally {
            unset($source);
        }

        return $filename;
    }

    private function encode(GdImage $source, int $maxDimension): string
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $ratio = min(1, $maxDimension / max($width, $height));
        $quality = config('photos.quality');
        do {
            $targetWidth = max(1, (int) round($width * $ratio));
            $targetHeight = max(1, (int) round($height * $ratio));
            $target = imagecreatetruecolor($targetWidth, $targetHeight);
            imagealphablending($target, false);
            imagesavealpha($target, true);
            imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
            ob_start();
            try {
                $success = imagewebp($target, null, $quality);
                $bytes = ob_get_contents();
            } finally {
                ob_end_clean();
                unset($target);
            }
            if (! $success || ! $bytes) {
                throw ValidationException::withMessages(['image' => __('photos.conversion_failed')]);
            }
            if (strlen($bytes) <= config('photos.max_upload_kb') * 1024) {
                return $bytes;
            }
            $ratio *= 0.8;
            $quality = max(60, $quality - 5);
        } while ($targetWidth > 320 || $targetHeight > 320);

        throw ValidationException::withMessages(['image' => __('photos.conversion_failed')]);
    }

    public function delete(string $filename): void
    {
        Storage::disk('local')->delete([$filename, self::thumbnailPath($filename)]);
    }
}
