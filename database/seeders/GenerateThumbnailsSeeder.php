<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class GenerateThumbnailsSeeder extends Seeder
{
    public function run(): void
    {
        $thumbExt = function_exists('imagewebp') ? 'webp' : 'jpg';

        $this->seedProducts($thumbExt);
        $this->seedProductImages($thumbExt);
        $this->seedGallery($thumbExt);
    }

    private function seedProducts(string $thumbExt): void
    {
        $thumbDir = public_path('image/products/thumbs');
        File::ensureDirectoryExists($thumbDir);

        $count = 0;

        Product::query()->chunkById(200, function ($chunk) use (&$count, $thumbDir, $thumbExt) {
            foreach ($chunk as $product) {
                $imagePath = (string) ($product->image_path ?? '');
                if ($imagePath === '') {
                    continue;
                }

                $source = public_path($imagePath);
                if (! File::exists($source)) {
                    continue;
                }

                $thumbFile = pathinfo($imagePath, PATHINFO_FILENAME).'-thumb.'.$thumbExt;
                $target = $thumbDir.DIRECTORY_SEPARATOR.$thumbFile;
                $publicThumb = 'image/products/thumbs/'.$thumbFile;

                if (! File::exists($target)) {
                    $this->createThumbnail($source, $target);
                }

                if (File::exists($target) && $product->thumb_path !== $publicThumb) {
                    $product->forceFill(['thumb_path' => $publicThumb])->save();
                    $count++;
                }
            }
        });

        $this->command?->info("Productos: miniaturas generadas/actualizadas: {$count}");
    }

    private function seedProductImages(string $thumbExt): void
    {
        $thumbDir = public_path('image/products/thumbs');
        File::ensureDirectoryExists($thumbDir);

        $count = 0;
        ProductImage::query()->chunkById(200, function ($chunk) use (&$count, $thumbDir, $thumbExt) {
            foreach ($chunk as $img) {
                $imagePath = (string) ($img->image_path ?? '');
                if ($imagePath === '') {
                    continue;
                }

                $source = public_path($imagePath);
                if (! File::exists($source)) {
                    continue;
                }

                $thumbFile = pathinfo($imagePath, PATHINFO_FILENAME).'-thumb.'.$thumbExt;
                $target = $thumbDir.DIRECTORY_SEPARATOR.$thumbFile;
                $publicThumb = 'image/products/thumbs/'.$thumbFile;

                if (! File::exists($target)) {
                    $this->createThumbnail($source, $target);
                }

                if (File::exists($target) && $img->thumb_path !== $publicThumb) {
                    $img->forceFill(['thumb_path' => $publicThumb])->save();
                    $count++;
                }
            }
        });

        $this->command?->info("ProductImages: miniaturas generadas/actualizadas: {$count}");
    }

    private function seedGallery(string $thumbExt): void
    {
        $imageThumbDir = public_path('image/gallery/thumbs');
        $coverThumbDir = public_path('image/gallery/video-covers/thumbs');
        File::ensureDirectoryExists($imageThumbDir);
        File::ensureDirectoryExists($coverThumbDir);

        $countImages = 0;
        $countVideos = 0;

        GalleryImage::query()->chunkById(200, function ($chunk) use (&$countImages, &$countVideos, $imageThumbDir, $coverThumbDir, $thumbExt) {
            foreach ($chunk as $item) {
                $type = (string) ($item->media_type ?? 'image');

                if ($type === 'video') {
                    $coverPath = (string) ($item->video_cover_path ?: $item->image_path);
                    if ($coverPath === '') {
                        continue;
                    }
                    $source = public_path($coverPath);
                    if (! File::exists($source)) {
                        continue;
                    }
                    $thumbFile = pathinfo($coverPath, PATHINFO_FILENAME).'-thumb.'.$thumbExt;
                    $target = $coverThumbDir.DIRECTORY_SEPARATOR.$thumbFile;
                    $publicThumb = 'image/gallery/video-covers/thumbs/'.$thumbFile;

                    if (! File::exists($target)) {
                        $this->createThumbnail($source, $target);
                    }

                    if (File::exists($target)) {
                        $item->forceFill([
                            'video_cover_thumb_path' => $publicThumb,
                            'thumb_path' => $publicThumb,
                        ])->save();
                        $countVideos++;
                    }

                    continue;
                }

                $imagePath = (string) ($item->image_path ?? '');
                if ($imagePath === '') {
                    continue;
                }
                $source = public_path($imagePath);
                if (! File::exists($source)) {
                    continue;
                }
                $thumbFile = pathinfo($imagePath, PATHINFO_FILENAME).'-thumb.'.$thumbExt;
                $target = $imageThumbDir.DIRECTORY_SEPARATOR.$thumbFile;
                $publicThumb = 'image/gallery/thumbs/'.$thumbFile;

                if (! File::exists($target)) {
                    $this->createThumbnail($source, $target);
                }

                if (File::exists($target)) {
                    $item->forceFill(['thumb_path' => $publicThumb])->save();
                    $countImages++;
                }
            }
        });

        $this->command?->info("Galería: imágenes={$countImages}, videos={$countVideos} miniaturas generadas/actualizadas");
    }

    private function createThumbnail(string $sourcePath, string $thumbTargetPath, int $maxSide = 700): bool
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            return false;
        }

        $contents = @file_get_contents($sourcePath);
        if ($contents === false) {
            return false;
        }

        $sourceImage = @imagecreatefromstring($contents);
        if ($sourceImage === false) {
            return false;
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($sourceImage);
            return false;
        }

        $scale = min($maxSide / $sourceWidth, $maxSide / $sourceHeight, 1);
        $thumbWidth = max(1, (int) round($sourceWidth * $scale));
        $thumbHeight = max(1, (int) round($sourceHeight * $scale));

        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        if ($thumbImage === false) {
            imagedestroy($sourceImage);
            return false;
        }

        $isWebpTarget = str_ends_with(strtolower($thumbTargetPath), '.webp');
        if ($isWebpTarget && function_exists('imagewebp')) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 0, 0, 0, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        } else {
            $white = imagecolorallocate($thumbImage, 255, 255, 255);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $white);
        }

        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

        $ok = false;
        if ($isWebpTarget && function_exists('imagewebp')) {
            $ok = imagewebp($thumbImage, $thumbTargetPath, 80);
        } elseif (function_exists('imagejpeg')) {
            $ok = imagejpeg($thumbImage, $thumbTargetPath, 82);
        }

        imagedestroy($thumbImage);
        imagedestroy($sourceImage);

        return $ok;
    }
}

