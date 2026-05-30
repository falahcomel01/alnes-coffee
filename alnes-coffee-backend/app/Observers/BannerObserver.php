<?php

namespace App\Observers;

use App\Models\Banner;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BannerObserver
{
    public function saving(Banner $banner): void
    {
        if (!$banner->isDirty('image') || !$banner->image) return;

        $path = Storage::disk('public')->path($banner->image);

        if (!file_exists($path)) return;

        try {
            $manager = new ImageManager(new Driver());
            $image   = $manager->read($path);

            if ($image->width() > 1200) {
                $image->scale(width: 1200);
            }

            $newPath = preg_replace('/\.(png|PNG|webp|WEBP)$/', '.jpg', $path);
            $image->toJpeg(75)->save($newPath);

            if ($path !== $newPath && file_exists($path)) {
                unlink($path);
                $banner->image = preg_replace(
                    '/\.(png|PNG|webp|WEBP)$/',
                    '.jpg',
                    $banner->image
                );
            }
        } catch (\Exception $e) {
            // Biarkan jika gagal
        }
    }
}