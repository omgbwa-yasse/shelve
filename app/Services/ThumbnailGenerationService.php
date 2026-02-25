<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class ThumbnailGenerationService
{
    /**
     * Contraintes de compression des vignettes
     */
    private const MAX_SIZE_BYTES = 10240; // 10 KB
    private const DEFAULT_DENSITY_PPI = 60; // Pixels par pouce
    private const DEFAULT_QUALITY = 60; // Qualité JPEG 0-100
    private const MAX_WIDTH = 150; // Largeur max en pixels
    private const MAX_HEIGHT = 200; // Hauteur max en pixels
    private const MIN_QUALITY = 20; // Qualité minimale

    /**
     * Générer une vignette pour un PDF avec compression
     *
     * @param string $filePath Chemin du fichier PDF
     * @param Attachment $attachment Modèle de l'attachment
     * @return string|null Chemin de la vignette générée ou null
     */
    public function generatePdfThumbnail(string $filePath, Attachment $attachment): ?string
    {
        try {
            if (!extension_loaded('imagick')) {
                Log::warning("Imagick extension not loaded for PDF thumbnail generation");
                return null;
            }

            if (!file_exists($filePath)) {
                Log::error("PDF file not found: {$filePath}");
                return null;
            }

            $imagick = new \Imagick();
            // Densité réduite pour compression
            $imagick->setResolution(self::DEFAULT_DENSITY_PPI, self::DEFAULT_DENSITY_PPI);
            $imagick->readImage($filePath . '[0]'); // Première page seulement
            $imagick->setImageFormat('jpeg');
            $imagick->thumbnailImage(self::MAX_WIDTH, self::MAX_HEIGHT, true);

            // Compression optimisée pour rester sous 10KB
            $imageBlob = $this->compressImage($imagick);
            $imagick->destroy();

            $thumbnailPath = $this->saveThumbnail($imageBlob);
            $this->updateAttachmentMetrics($attachment, $thumbnailPath, $imageBlob);

            Log::info("PDF thumbnail generated for attachment {$attachment->id} (Size: " . strlen($imageBlob) . " bytes)");
            return $thumbnailPath;
        } catch (Exception $e) {
            Log::error("Error generating PDF thumbnail for {$filePath}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Générer une vignette pour une image avec compression
     *
     * @param string $filePath Chemin du fichier image
     * @param Attachment $attachment Modèle de l'attachment
     * @return string|null Chemin de la vignette générée ou null
     */
    public function generateImageThumbnail(string $filePath, Attachment $attachment): ?string
    {
        try {
            if (!file_exists($filePath)) {
                Log::error("Image file not found: {$filePath}");
                return null;
            }

            $imagick = new \Imagick($filePath);
            // Densité réduite pour compression
            $imagick->setResolution(self::DEFAULT_DENSITY_PPI, self::DEFAULT_DENSITY_PPI);
            $imagick->thumbnailImage(self::MAX_WIDTH, self::MAX_HEIGHT, true);
            $imagick->setImageFormat('jpeg');

            // Compression optimisée pour rester sous 10KB
            $imageBlob = $this->compressImage($imagick);
            $imagick->destroy();

            $thumbnailPath = $this->saveThumbnail($imageBlob);
            $this->updateAttachmentMetrics($attachment, $thumbnailPath, $imageBlob);

            Log::info("Image thumbnail generated for attachment {$attachment->id} (Size: " . strlen($imageBlob) . " bytes)");
            return $thumbnailPath;
        } catch (Exception $e) {
            Log::error("Error generating image thumbnail for {$filePath}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Compresser l'image pour rester sous la limite de 10KB
     *
     * @param \Imagick $imagick Objet Imagick
     * @return string Blob compressé
     */
    private function compressImage(\Imagick $imagick): string
    {
        $quality = self::DEFAULT_QUALITY;
        $imagick->setImageCompressionQuality($quality);
        $imageBlob = $imagick->getImageBlob();

        // Réduire la qualité progressivement si la taille dépasse 10KB
        while (strlen($imageBlob) > self::MAX_SIZE_BYTES && $quality > self::MIN_QUALITY) {
            $quality = max(self::MIN_QUALITY, $quality - 5);
            $imagick->setImageCompressionQuality($quality);
            $imageBlob = $imagick->getImageBlob();
        }

        // Si toujours trop grand, réduire encore plus les dimensions
        if (strlen($imageBlob) > self::MAX_SIZE_BYTES) {
            $imagick->resizeImage(
                intval(self::MAX_WIDTH * 0.75),
                intval(self::MAX_HEIGHT * 0.75),
                \Imagick::FILTER_LANCZOS,
                1
            );
            $quality = self::MIN_QUALITY;
            $imagick->setImageCompressionQuality($quality);
            $imageBlob = $imagick->getImageBlob();
        }

        return $imageBlob;
    }

    /**
     * Sauvegarder la vignette dans le stockage
     *
     * @param string $imageBlob Contenu de l'image
     * @return string Chemin de la vignette
     */
    private function saveThumbnail(string $imageBlob): string
    {
        $thumbnailDir = 'thumbnails';
        $hash = hash('sha256', bin2hex(random_bytes(16)) . time());
        $filename = substr($hash, 0, 16) . '.jpg';
        $path = $thumbnailDir . '/' . $filename;

        Storage::disk('local')->put($path, $imageBlob);
        return $path;
    }

    /**
     * Mettre à jour les métriques de la vignette
     *
     * @param Attachment $attachment Modèle de l'attachment
     * @param string $thumbnailPath Chemin de la vignette
     * @param string $imageBlob Contenu de l'image
     */
    private function updateAttachmentMetrics(Attachment $attachment, string $thumbnailPath, string $imageBlob): void
    {
        $thumbnailSizeBytes = strlen($imageBlob);

        $attachment->update([
            'thumbnail_path' => $thumbnailPath,
            'thumbnail_generated_at' => now(),
            'thumbnail_error' => null,
            'thumbnail_size_bytes' => $thumbnailSizeBytes,
            'thumbnail_density_ppi' => self::DEFAULT_DENSITY_PPI,
            'thumbnail_compression_quality' => self::DEFAULT_QUALITY,
        ]);

        if ($thumbnailSizeBytes > self::MAX_SIZE_BYTES) {
            Log::warning("Thumbnail size exceeds 10KB limit for attachment {$attachment->id}: {$thumbnailSizeBytes} bytes");
        }
    }

    /**
     * Vérifier si une vignette doit être régénérée
     *
     * @param Attachment $attachment Modèle de l'attachment
     * @return bool True si régénération nécessaire
     */
    public function shouldRegenerateThumbnail(Attachment $attachment): bool
    {
        // Régénérer si pas de vignette
        if (!$attachment->thumbnail_path) {
            return true;
        }

        // Régénérer si la vignette est trop grosse (> 10KB)
        if ($attachment->thumbnail_size_bytes && $attachment->thumbnail_size_bytes > self::MAX_SIZE_BYTES) {
            return true;
        }

        // Régénérer si erreur lors de la génération
        if ($attachment->thumbnail_error) {
            return true;
        }

        return false;
    }

    /**
     * Obtenir les infos de compression d'une vignette
     *
     * @param Attachment $attachment Modèle de l'attachment
     * @return array Tableau avec les métriques
     */
    public function getThumbnailMetrics(Attachment $attachment): array
    {
        return [
            'has_thumbnail' => (bool)$attachment->thumbnail_path,
            'size_bytes' => $attachment->thumbnail_size_bytes ?? 0,
            'size_kb' => round(($attachment->thumbnail_size_bytes ?? 0) / 1024, 2),
            'max_size_kb' => self::MAX_SIZE_BYTES / 1024,
            'density_ppi' => $attachment->thumbnail_density_ppi ?? self::DEFAULT_DENSITY_PPI,
            'compression_quality' => $attachment->thumbnail_compression_quality ?? self::DEFAULT_QUALITY,
            'within_limit' => !$attachment->thumbnail_size_bytes || $attachment->thumbnail_size_bytes <= self::MAX_SIZE_BYTES,
            'generated_at' => $attachment->thumbnail_generated_at,
            'error' => $attachment->thumbnail_error,
        ];
    }

    /**
     * Obtenir les constantes de compression
     *
     * @return array Tableau avec les constantes
     */
    public static function getCompressionConstraints(): array
    {
        return [
            'max_size_bytes' => self::MAX_SIZE_BYTES,
            'max_size_kb' => self::MAX_SIZE_BYTES / 1024,
            'density_ppi' => self::DEFAULT_DENSITY_PPI,
            'compression_quality' => self::DEFAULT_QUALITY,
            'max_width' => self::MAX_WIDTH,
            'max_height' => self::MAX_HEIGHT,
        ];
    }
}
