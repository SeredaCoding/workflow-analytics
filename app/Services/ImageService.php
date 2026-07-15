<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageService
{
    protected ImageManager $manager;
    protected string $disk;

    public function __construct(?ImageManager $manager = null)
    {
        $this->manager = $manager ?? new ImageManager(new Driver());
        $this->disk = 'local';
    }

    public function store(UploadedFile $file, string $subdirectory = '', array $options = []): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $directory = $subdirectory ? trim($subdirectory, '/') . '/' : '';
        $path = $directory . $filename;

        Storage::disk($this->disk)->putFileAs(
            $directory,
            $file,
            $filename
        );

        return $path;
    }

    public function optimize(string $path, array $options = []): string
    {
        $quality = $options['quality'] ?? 80;
        $fullPath = Storage::disk($this->disk)->path($path);

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("File not found: {$fullPath}");
        }

        $image = $this->manager->read($fullPath);
        $info = pathinfo($fullPath);
        $webpPath = $info['dirname'] . '/' . $info['filename'] . '.webp';
        $relativeWebpPath = dirname($path) . '/' . $info['filename'] . '.webp';

        $image->toWebp($quality)->save($webpPath);

        unlink($fullPath);

        return $relativeWebpPath;
    }

    public function delete(string $path): bool
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }
        return false;
    }

    public function serve(string $path): BinaryFileResponse
    {
        $fullPath = Storage::disk($this->disk)->path($path);
        return response()->file($fullPath);
    }

    public function fullPath(string $path): string
    {
        return Storage::disk($this->disk)->path($path);
    }
}
