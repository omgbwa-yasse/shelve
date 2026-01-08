<?php

namespace App\Jobs;

use App\Models\RecordDigitalDocument;
use App\Models\Attachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class GenerateDocumentThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $attachment;
    protected $maxAttempts = 3;
    protected $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Attachment $attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Vérifier que l'attachment est un document
            if ($this->attachment->type !== Attachment::TYPE_DIGITAL_DOCUMENT) {
                Log::info("Skipping thumbnail generation for non-document attachment {$this->attachment->id}");
                return;
            }

            // Vérifier que l'extension imagick est disponible
            if (!extension_loaded('imagick')) {
                Log::warning("Imagick extension not loaded, skipping thumbnail generation for attachment {$this->attachment->id}");
                return;
            }

            $filePath = storage_path('app/' . $this->attachment->path);

            // Vérifier que le fichier existe
            if (!file_exists($filePath)) {
                Log::error("File not found for attachment {$this->attachment->id}: {$filePath}");
                return;
            }

            // Générer la vignette selon le type de fichier
            $mimeType = $this->attachment->mime_type ?? $this->guessMimeType($filePath);

            if (strpos($mimeType, 'pdf') !== false) {
                $this->generatePdfThumbnail($filePath);
            } elseif (strpos($mimeType, 'image/') === 0) {
                $this->generateImageThumbnail($filePath);
            } else {
                Log::info("No thumbnail generation available for mime type {$mimeType}");
            }
        } catch (Exception $e) {
            Log::error("Error generating thumbnail for attachment {$this->attachment->id}: {$e->getMessage()}");
            $this->recordError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate thumbnail for PDF files
     */
    private function generatePdfThumbnail(string $filePath): void
    {
        try {
            $imagick = new \Imagick();
            $imagick->setResolution(300, 300);
            $imagick->readImage($filePath . '[0]'); // Premier page seulement
            $imagick->setImageFormat('jpeg');
            $imagick->thumbnailImage(200, 300, true); // 200x300, aspect ratio preserved

            $thumbnailPath = $this->saveThumbnail($imagick->getImageBlob());
            $this->updateAttachmentThumbnail($thumbnailPath);
            Log::info("PDF thumbnail generated for attachment {$this->attachment->id}");
        } catch (Exception $e) {
            Log::error("Error generating PDF thumbnail: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Generate thumbnail for image files
     */
    private function generateImageThumbnail(string $filePath): void
    {
        try {
            $imagick = new \Imagick($filePath);
            $imagick->thumbnailImage(200, 300, true); // 200x300, aspect ratio preserved
            $imagick->setImageFormat('jpeg');

            $thumbnailPath = $this->saveThumbnail($imagick->getImageBlob());
            $this->updateAttachmentThumbnail($thumbnailPath);
            Log::info("Image thumbnail generated for attachment {$this->attachment->id}");
        } catch (Exception $e) {
            Log::error("Error generating image thumbnail: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Save thumbnail to storage
     */
    private function saveThumbnail(string $imageBlob): string
    {
        $thumbnailDir = 'thumbnails';
        $hash = hash('sha256', $this->attachment->id . time());
        $filename = substr($hash, 0, 16) . '.jpg';
        $path = $thumbnailDir . '/' . $filename;

        Storage::disk('local')->put($path, $imageBlob);
        return $path;
    }

    /**
     * Update attachment with thumbnail path
     */
    private function updateAttachmentThumbnail(string $thumbnailPath): void
    {
        $this->attachment->update([
            'thumbnail_path' => $thumbnailPath,
            'thumbnail_generated_at' => now(),
            'thumbnail_error' => null,
        ]);
    }

    /**
     * Record thumbnail generation error
     */
    private function recordError(string $errorMessage): void
    {
        $this->attachment->update([
            'thumbnail_generated_at' => now(),
            'thumbnail_error' => substr($errorMessage, 0, 1000),
        ]);
    }

    /**
     * Guess MIME type from file extension
     */
    private function guessMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Failed job handler
     */
    public function failed(Exception $exception): void
    {
        Log::error("Thumbnail generation job failed for attachment {$this->attachment->id}: {$exception->getMessage()}");
        $this->recordError("Job failed: {$exception->getMessage()}");
    }
}
