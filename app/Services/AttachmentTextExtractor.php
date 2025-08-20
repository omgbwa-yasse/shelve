<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Facades\Log;
// No external process manager; we use proc_open / escapeshellarg to keep deps minimal
use thiagoalessio\TesseractOCR\TesseractOCR;

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
                // If PDF is likely scanned (empty text), try OCR fallback when enabled
                if (!$result && $this->isTesseractEnabled()) {
                    $ocr = $this->fromPdfWithTesseract($absolutePath);
                    if ($ocr) {
                        $result = $ocr;
                    }
                }
            } elseif (in_array($ext, ['docx'], true)) {
                $result = $this->fromDocx($absolutePath);
            } elseif (in_array($ext, ['txt', 'csv', 'log', 'md'], true)) {
                $result = $this->fromText($absolutePath);
            } elseif (in_array($ext, ['htm', 'html'], true)) {
                $result = $this->fromHtml($absolutePath);
            } elseif ($ext === 'rtf') {
                $result = $this->fromRtf($absolutePath);
            } elseif (in_array($ext, ['png','jpg','jpeg','tif','tiff','bmp','gif','webp'], true)) {
                // Image OCR via Tesseract when available
                if ($this->isTesseractEnabled()) {
                    $result = $this->fromImageWithTesseract($absolutePath);
                }
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

    /* ============================
     | Tesseract OCR integration |
     ============================ */

    protected function isTesseractEnabled(): bool
    {
        $bin = config('services.tesseract.bin');
        return is_string($bin) && $bin !== '';
    }

    protected function tesseractLang(): string
    {
        return (string) (config('services.tesseract.lang') ?? 'eng');
    }

    protected function fromImageWithTesseract(string $path): ?string
    {
        $bin = (string) config('services.tesseract.bin');
        if (!$bin || !is_file($path)) {
            return null;
        }

        try {
            $text = (new TesseractOCR($path))
                ->executable($bin)
                ->lang($this->tesseractLang())
                ->run();
            $text = is_string($text) ? trim($text) : '';
            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            Log::warning('Tesseract OCR failed', ['path' => $path, 'error' => $e->getMessage()]);
            return null;
        }
    }

    protected function fromPdfWithTesseract(string $path): ?string
    {
        $tessBin = (string) config('services.tesseract.bin');
        $pdftoppm = (string) (config('services.tesseract.pdftoppm_bin') ?? 'pdftoppm');

        $result = null;
        if (!$tessBin || !is_file($path)) {
            return $result;
        }

        // Convert PDF to images using pdftoppm (Poppler)
        $tmpBase = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'pdfocr_' . uniqid();
        $code = $this->runCommand([$pdftoppm, '-png', '-r', '200', $path, $tmpBase], 180);
        if ($code !== 0) {
            Log::info('pdftoppm failed or not available; skipping PDF OCR', ['path' => $path, 'code' => $code]);
            return $result;
        }

        // Collect generated PNG pages
        $glob = glob($tmpBase . '-*.png') ?: [];
        if (empty($glob)) {
            return $result;
        }

        $allText = [];
        foreach ($glob as $img) {
            $text = $this->fromImageWithTesseract($img);
            if ($text) {
                $allText[] = $text;
            }
            @unlink($img);
        }

    return $allText ? trim(implode("\n\n", $allText)) : null;
    }

    /**
     * Run an external command with basic escaping and timeout.
     * Returns the exit code.
     */
    private function runCommand(array $args, int $timeout = 120): int
    {
        $cmd = implode(' ', array_map('escapeshellarg', $args));
        $descriptors = [
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];
        $process = @proc_open($cmd, $descriptors, $pipes, null, null, ['bypass_shell' => true]);
        if (!is_resource($process)) {
            return 1;
        }
        // Read output to avoid blocking (discard)
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $start = time();
        while (true) {
            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }
            if ((time() - $start) > $timeout) {
                @proc_terminate($process);
                break;
            }
            usleep(100000);
        }
        foreach ($pipes as $p) { @fclose($p); }
        $code = proc_close($process);
        return is_int($code) ? $code : 1;
    }
}

