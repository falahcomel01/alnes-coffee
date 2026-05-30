<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;

class CompressImages extends Command
{
    protected $signature   = 'images:compress';
    protected $description = 'Kompres semua gambar di storage';

    public function handle(): void
    {
        $manager = new ImageManager(new Driver());
        $folders = ['banners', 'products', 'settings'];

        foreach ($folders as $folder) {
            $files = Storage::disk('public')->files($folder);

            foreach ($files as $file) {
                if (!preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)) continue;

                $path       = Storage::disk('public')->path($file);
                $sizeBefore = filesize($path);

                try {
                    $image = $manager->decode($path);

                    if ($image->width() > 1200) {
                        $image->scale(width: 1200);
                    }

                    $newPath = preg_replace('/\.(png|PNG|webp|WEBP)$/', '.jpg', $path);

                    $encoded = $image->encode(new JpegEncoder(75));
                    file_put_contents($newPath, $encoded);

                    if ($path !== $newPath && file_exists($path)) {
                        unlink($path);
                    }

                    $sizeAfter = filesize($newPath);
                    $saved     = round(($sizeBefore - $sizeAfter) / 1024);

                    $this->info("✓ {$file} — hemat {$saved}KB");
                } catch (\Exception $e) {
                    $this->error("✗ {$file} — {$e->getMessage()}");
                }
            }
        }

        $this->info('Selesai!');
    }
}