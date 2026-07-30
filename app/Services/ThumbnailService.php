<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ThumbnailService
{
    /**
     * Genera un thumbnail de una imagen y lo guarda en disco público.
     * Retorna la ruta pública relativa del thumbnail.
     */
    public static function generate(
        string $sourcePath,
        string $diskSource = 'private',
        int $width  = 400,
        int $height = 400
    ): ?string {
        try {
            $disk = Storage::disk($diskSource);

            if (! $disk->exists($sourcePath)) {
                return null;
            }

            $imageData = $disk->get($sourcePath);
            $srcImg    = imagecreatefromstring($imageData);

            if (! $srcImg) {
                return null;
            }

            $origW = imagesx($srcImg);
            $origH = imagesy($srcImg);

            // Calcular dimensiones manteniendo proporción
            $ratio   = min($width / $origW, $height / $origH);
            $newW    = (int) round($origW * $ratio);
            $newH    = (int) round($origH * $ratio);

            $thumb = imagecreatetruecolor($newW, $newH);

            // Preservar transparencia para PNG
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);

            imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($srcImg);

            // Generar path del thumbnail en disco público
            $thumbPath = 'thumbs/' . dirname($sourcePath) . '/' .
                         pathinfo(basename($sourcePath), PATHINFO_FILENAME) . '_thumb.jpg';

            // Capturar output del JPEG
            ob_start();
            imagejpeg($thumb, null, 82); // calidad 82 = buen balance tamaño/calidad
            $jpegData = ob_get_clean();
            imagedestroy($thumb);

            Storage::disk('public')->put($thumbPath, $jpegData);

            return $thumbPath;

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ThumbnailService error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retorna la URL del thumbnail si existe, o la URL del serve como fallback.
     */
    public static function url(int|string $photoId, ?string $thumbPath): string
    {
        if ($thumbPath && Storage::disk('public')->exists($thumbPath)) {
            return asset('storage/' . $thumbPath);
        }
        return route('photos.serve', $photoId);
    }
}
