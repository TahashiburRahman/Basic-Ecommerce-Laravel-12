<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageOptimizationService
{
    public function optimize(UploadedFile $upload, string $prefix): string
    {
        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            throw new ImageOptimizationException('Image processing is unavailable because GD WebP support is not enabled.');
        }

        $prefix = preg_replace('/[^a-z0-9-]/', '', strtolower($prefix)) ?: 'image';
        $sourcePath = $upload->getRealPath();
        $imageInfo = $sourcePath ? @getimagesize($sourcePath) : false;
        $mime = $sourcePath ? (new \finfo(FILEINFO_MIME_TYPE))->file($sourcePath) : false;
        $supportedTypes = [
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_PNG => 'image/png',
            IMAGETYPE_WEBP => 'image/webp',
        ];

        if (! $imageInfo || ! isset($supportedTypes[$imageInfo[2]]) || $mime !== $supportedTypes[$imageInfo[2]]) {
            throw new ImageOptimizationException('The uploaded file is not a valid, supported JPEG, PNG, or WebP image.');
        }

        $this->assertMemoryBudget((int) $imageInfo[0], (int) $imageInfo[1]);

        $source = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
        };

        if (! $source instanceof \GdImage) {
            throw new ImageOptimizationException('The uploaded image could not be decoded.');
        }

        $temporaryPath = null;
        $working = null;

        try {
            if ($imageInfo[2] === IMAGETYPE_JPEG && extension_loaded('exif')) {
                $source = $this->applyExifOrientation($source, $sourcePath);
            }

            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);
            $maxWidth = min(2000, max(1, (int) config('image-optimization.max_width', 2000)));
            $maxHeight = min(2000, max(1, (int) config('image-optimization.max_height', 2000)));
            $scale = min(1, $maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
            $width = max(1, (int) round($sourceWidth * $scale));
            $height = max(1, (int) round($sourceHeight * $scale));

            $working = $this->resize($source, $width, $height, $imageInfo[2] !== IMAGETYPE_JPEG);
            if ($working !== $source) {
                imagedestroy($source);
                $source = null;
            }

            $directory = public_path('images');
            if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
                throw new ImageOptimizationException('The public/images directory could not be created.');
            }

            if (! is_writable($directory)) {
                throw new ImageOptimizationException('The public/images directory is not writable by PHP.');
            }

            $temporaryPath = tempnam($directory, '.image-');
            if ($temporaryPath === false) {
                throw new ImageOptimizationException('A temporary image file could not be created.');
            }

            $initialQuality = min(100, max(1, (int) config('image-optimization.initial_quality', 82)));
            $minimumQuality = min(
                $initialQuality,
                max(1, (int) config('image-optimization.minimum_quality', 42))
            );
            $qualityStep = max(1, (int) config('image-optimization.quality_step', 8));
            $maxBytes = min(2097152, max(1, (int) config('image-optimization.max_output_bytes', 2097152)));
            $minimumDimension = max(1, (int) config('image-optimization.minimum_dimension', 128));
            $dimensionScale = min(0.95, max(0.5, (float) config('image-optimization.dimension_scale', 0.85)));

            do {
                for ($quality = $initialQuality; $quality >= $minimumQuality; $quality -= $qualityStep) {
                    if (! @imagewebp($working, $temporaryPath, $quality)) {
                        throw new ImageOptimizationException('The image could not be encoded as WebP.');
                    }

                    clearstatcache(true, $temporaryPath);
                    $encodedBytes = filesize($temporaryPath);
                    if ($encodedBytes !== false && $encodedBytes < $maxBytes) {
                        $outputInfo = @getimagesize($temporaryPath);
                        if (
                            ! $outputInfo
                            || $outputInfo[2] !== IMAGETYPE_WEBP
                            || $outputInfo[0] > $maxWidth
                            || $outputInfo[1] > $maxHeight
                        ) {
                            throw new ImageOptimizationException('The processed image did not meet WebP format and dimension requirements.');
                        }

                        $filename = $prefix.'-'.Str::uuid().'.webp';
                        $relativePath = 'images/'.$filename;
                        $destination = public_path($relativePath);

                        if (! @rename($temporaryPath, $destination)) {
                            throw new ImageOptimizationException('The optimized image could not be saved to public/images.');
                        }

                        $temporaryPath = null;

                        return $relativePath;
                    }

                    if ($quality - $qualityStep < $minimumQuality && $quality !== $minimumQuality) {
                        $quality = $minimumQuality + $qualityStep;
                    }
                }

                $currentWidth = imagesx($working);
                $currentHeight = imagesy($working);
                if (max($currentWidth, $currentHeight) <= $minimumDimension) {
                    break;
                }

                $nextWidth = max(1, (int) floor($currentWidth * $dimensionScale));
                $nextHeight = max(1, (int) floor($currentHeight * $dimensionScale));
                if ($nextWidth === $currentWidth && $nextHeight === $currentHeight) {
                    break;
                }

                $smaller = $this->resize($working, $nextWidth, $nextHeight, $imageInfo[2] !== IMAGETYPE_JPEG);
                imagedestroy($working);
                $working = $smaller;
            } while (true);

            throw new ImageOptimizationException('This image could not be optimized below 2 MB. Try a smaller or simpler image.');
        } finally {
            if ($working instanceof \GdImage) {
                imagedestroy($working);
            }
            if ($source instanceof \GdImage && $source !== $working) {
                imagedestroy($source);
            }
            if ($temporaryPath && is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    public function deleteIfUnreferenced(?string $relativePath): void
    {
        if (! $this->isManagedImagePath($relativePath)) {
            return;
        }

        $isReferenced = DB::table('products')->where('image', $relativePath)->exists()
            || DB::table('categories')->where('image', $relativePath)->exists();

        if ($isReferenced) {
            return;
        }

        $this->discard($relativePath);
    }

    public function discard(?string $relativePath): void
    {
        if (! $this->isManagedImagePath($relativePath)) {
            return;
        }

        $absolutePath = public_path($relativePath);
        if (is_file($absolutePath) && ! @unlink($absolutePath)) {
            Log::warning('Unable to remove an unused optimized image.', ['path' => $relativePath]);
        }
    }

    private function isManagedImagePath(?string $relativePath): bool
    {
        return is_string($relativePath)
            && preg_match('#\Aimages/[A-Za-z0-9._-]+\.webp\z#', $relativePath) === 1;
    }

    private function resize(\GdImage $source, int $width, int $height, bool $preserveTransparency): \GdImage
    {
        if ($width === imagesx($source) && $height === imagesy($source)) {
            return $source;
        }

        $resized = imagecreatetruecolor($width, $height);
        if (! $resized) {
            throw new ImageOptimizationException('There is not enough memory to resize this image.');
        }

        if ($preserveTransparency) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        } else {
            imagefill($resized, 0, 0, imagecolorallocate($resized, 255, 255, 255));
        }

        if (! imagecopyresampled(
            $resized,
            $source,
            0,
            0,
            0,
            0,
            $width,
            $height,
            imagesx($source),
            imagesy($source)
        )) {
            imagedestroy($resized);
            throw new ImageOptimizationException('The uploaded image could not be resized.');
        }

        return $resized;
    }

    private function applyExifOrientation(\GdImage $image, string $path): \GdImage
    {
        $metadata = @exif_read_data($path);
        $orientation = (int) ($metadata['Orientation'] ?? 1);

        if (in_array($orientation, [3, 5, 6, 7, 8], true)) {
            $angle = match ($orientation) {
                3 => 180,
                5, 6 => -90,
                7, 8 => 90,
            };
            $rotated = imagerotate($image, $angle, 0);
            if ($rotated instanceof \GdImage) {
                imagedestroy($image);
                $image = $rotated;
            }
        }

        $flip = match ($orientation) {
            2, 5, 7 => IMG_FLIP_HORIZONTAL,
            4 => IMG_FLIP_VERTICAL,
            default => null,
        };
        if ($flip !== null) {
            imageflip($image, $flip);
        }

        return $image;
    }

    private function assertMemoryBudget(int $width, int $height): void
    {
        $memoryLimit = $this->memoryLimitBytes((string) ini_get('memory_limit'));
        $budget = max(0, (int) (($memoryLimit - memory_get_usage(true)) * 0.75));
        $maxWidth = min(2000, max(1, (int) config('image-optimization.max_width', 2000)));
        $maxHeight = min(2000, max(1, (int) config('image-optimization.max_height', 2000)));
        $targetScale = min(1, $maxWidth / $width, $maxHeight / $height);
        $targetPixels = (int) ceil($width * $targetScale) * (int) ceil($height * $targetScale);
        $estimatedBytes = ($width * $height * 8) + ($targetPixels * 8) + (32 * 1024 * 1024);

        if ($estimatedBytes > $budget) {
            throw new ImageOptimizationException('This image is too large to process safely with the current PHP memory limit.');
        }
    }

    private function memoryLimitBytes(string $limit): int
    {
        $limit = trim($limit);
        if ($limit === '' || $limit === '-1') {
            return 512 * 1024 * 1024;
        }

        $value = (float) $limit;
        $unit = strtolower(substr($limit, -1));
        $multiplier = match ($unit) {
            'g' => 1024 ** 3,
            'm' => 1024 ** 2,
            'k' => 1024,
            default => 1,
        };

        return (int) ($value * $multiplier);
    }
}
