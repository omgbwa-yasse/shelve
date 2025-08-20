<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Facades\Log;

class AttachmentTextExtractor
{
    /**
     * Extract plain text from a file by mimetype or extension.
     */
    public function extract(string $absolutePath, ?string $mime = null, ?string $name = null): ?string
    {
        $detectedMime = $mime ?: $this->guessMime($absolutePath);
        $ext = strtolower(pathinfo($name ?: $absolutePath, PATHINFO_EXTENSION));

        try {
            if (!$detectedMime && !$ext) {
                return null;
            }

            $result = null;
            if ((is_string($detectedMime) && str_contains($detectedMime, 'pdf')) || $ext === 'pdf') {
                $result = $this->fromPdf($absolutePath);
            } elseif (in_array($ext, ['docx'], true)) {
                $result = $this->fromDocx($absolutePath);
            } elseif (in_array($ext, ['txt', 'csv', 'log', 'md'], true)) {
                $result = $this->fromText($absolutePath);
            } elseif (in_array($ext, ['htm', 'html'], true)) {
                $result = $this->fromHtml($absolutePath);
            } elseif ($ext === 'rtf') {
                $result = $this->fromRtf($absolutePath);
            }

            return $result ? trim(preg_replace('/\s+/u', ' ', $result)) : null;
        } catch (\Throwable $e) {
            Log::warning('Text extraction failed', [
                'path' => $absolutePath,
                'mime' => $detectedMime,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function guessMime(string $path): ?string
    {
        if (function_exists('mime_content_type')) {
            return @mime_content_type($path) ?: null;
        }
        return null;
    }

    protected function fromPdf(string $path): ?string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        return trim($pdf->getText());
    }

    protected function fromDocx(string $path): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            if ($xml) {
                $xml = preg_replace('/<\/(w:p|w:br)[^>]*>/i', "\n", $xml);
                $text = strip_tags($xml);
                return trim(html_entity_decode($text));
            }
        }
        return null;
    }

    protected function fromText(string $path): ?string
    {
        return @trim(file_get_contents($path));
    }

    protected function fromHtml(string $path): ?string
    {
        $html = @file_get_contents($path);
        if ($html === false) {
            return null;
        }
        $text = strip_tags($html);
        return trim(html_entity_decode($text));
    }

    protected function fromRtf(string $path): ?string
    {
        $rtf = @file_get_contents($path);
        if ($rtf === false) {
            return null;
        }
        $text = preg_replace('/\\\\[a-z]+(?:-?\d+)?\s?/i', ' ', $rtf);
        $text = str_replace(['{', '}'], ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }
}

