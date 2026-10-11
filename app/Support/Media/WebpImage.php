<?php

namespace App\Support\Media;

use GdImage;

/**
 * Re-encodes an uploaded raster image as WebP, scaled down to a maximum
 * width (never up), with JPEG camera rotation applied and PNG/WebP
 * transparency kept. Used for content images (article covers, images
 * inserted in the editor) so pages never ship multi-megabyte photos.
 *
 * GIFs are left alone (GD would drop their animation), and anything GD
 * cannot read, or that is too large to decode safely, returns null so the
 * caller keeps the original file.
 */
class WebpImage
{
    /** Decoding cost guard: GD needs ~5 bytes per pixel. */
    private const MAX_PIXELS = 40_000_000;

    private const READERS = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'];

    /** @return array{data: string, width: int, height: int}|null */
    public static function encode(string $path, int $maxWidth, int $quality = 82): ?array
    {
        $info = @getimagesize($path);

        if (! $info || ! function_exists('imagewebp') || ! isset(self::READERS[$info['mime']]) || $info[0] * $info[1] > self::MAX_PIXELS) {
            return null;
        }

        $image = @(self::READERS[$info['mime']])($path);

        if (! $image instanceof GdImage) {
            return null;
        }

        $image = self::orient($image, $path, $info['mime']);
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxWidth) {
            $height = max(1, (int) round($height * $maxWidth / $width));
            $width = $maxWidth;
            $resized = imagecreatetruecolor($width, $height);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
            $image = $resized;
        } elseif (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagesavealpha($image, true);

        ob_start();
        $ok = imagewebp($image, null, $quality);
        $data = (string) ob_get_clean();

        return $ok && $data !== '' ? ['data' => $data, 'width' => $width, 'height' => $height] : null;
    }

    /** Apply the EXIF orientation phones write instead of rotating pixels. */
    private static function orient(GdImage $image, string $path, string $mime): GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $angle = match ((int) (@exif_read_data($path)['Orientation'] ?? 1)) {
            3 => 180, 6 => -90, 8 => 90, default => 0,
        };

        return $angle === 0 ? $image : (imagerotate($image, $angle, 0) ?: $image);
    }
}
