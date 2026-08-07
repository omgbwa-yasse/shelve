<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use ZipArchive;

class AiTemplateReader
{
    /**
     * Extrait le texte brut d'un fichier template pour que l'IA puisse l'utiliser.
     */
    public function read(string $path): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException('Fichier template introuvable.');
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'txt', 'md', 'markdown', 'csv', 'html', 'xml', 'json' => $this->readText($path),
            'doc', 'docx' => $this->readDocx($path),
            'xlsx', 'xls' => $this->readXlsx($path),
            'pdf' => $this->readPdf($path),
            default => throw new RuntimeException("Format de template non lisible : .{$ext}"),
        };
    }

    private function readText(string $path): string
    {
        return file_get_contents($path) ?: '';
    }

    private function readDocx(string $path): string
    {
        if (str_ends_with(strtolower($path), '.doc')) {
            throw new RuntimeException('Les fichiers .doc (ancien format) ne sont pas lisibles, convertissez-les en .docx.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Impossible de lire le fichier .docx.');
        }

        $xml = $zip->getFromName('word/document.xml') ?? '';
        $zip->close();

        $xml = preg_replace('/<w:tab[^>]*\/>/', "\t", $xml);
        $xml = preg_replace('/<w:br[^>]*\/>/', "\n", $xml);
        $xml = preg_replace('/<\/w:p>/', "\n", $xml);
        $xml = strip_tags($xml);

        return trim(html_entity_decode($xml));
    }

    private function readXlsx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Impossible de lire le fichier .xlsx.');
        }

        $shared = $zip->getFromName('xl/sharedStrings.xml') ?? '';
        $sharedStrings = [];

        if ($shared !== '') {
            preg_match_all('/<si>(.*?)<\/si>/s', $shared, $matches);
            foreach ($matches[1] as $si) {
                preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $si, $ts);
                $sharedStrings[] = implode('', $ts[1]);
            }
        }

        $texts = [];
        $sheetCount = $zip->numFiles;
        for ($i = 0; $i < $sheetCount; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                $sheetXml = $zip->getFromIndex($i);
                if ($sheetXml === false) {
                    continue;
                }
                preg_match_all('/<c[^>]*r="([^"]+)"[^>]*>(?:<v>([^<]*)<\/v>)?(?:<is>.*?<t[^>]*>(.*?)<\/t>.*?<\/is>)?/s', $sheetXml, $cells, PREG_SET_ORDER);
                foreach ($cells as $cell) {
                    if (isset($cell[3]) && $cell[3] !== '') {
                        $texts[] = html_entity_decode($cell[3]);
                    } elseif (isset($cell[2]) && is_numeric($cell[2])) {
                        $idx = (int) $cell[2];
                        $texts[] = $sharedStrings[$idx] ?? $cell[2];
                    }
                }
            }
        }

        $zip->close();

        return trim(implode("\n", $texts));
    }

    private function readPdf(string $path): string
    {
        $extractor = new \App\Services\AttachmentTextExtractor();
        try {
            return $extractor->extract($path) ?: '';
        } catch (\Throwable $e) {
            Log::warning("Lecture PDF template échouée", ['error' => $e->getMessage()]);
            return '';
        }
    }
}
