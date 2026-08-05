<?php

namespace App\Services;

use App\Exports\SEDAExport;
use App\Models\Record;
use App\Models\Slip;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class SedaZipException extends \RuntimeException {}

class SedaZipBuilder
{
    public function __construct(private readonly SEDAExport $exporter)
    {
    }

    public function buildForRecord(Record $record): array
    {
        $record->loadMissing(['attachments', 'containers', 'level', 'parent', 'thesaurusConcepts']);
        $xml = $this->exporter->exportRecords(collect([$record]));
        $files = $record->attachments ? $record->attachments->all() : [];
        return $this->buildZip($xml, $files);
    }

    public function buildForSlip(Slip $slip): array
    {
        $slip->loadMissing(['records.attachments', 'records.containers', 'records.level', 'records.parent', 'records.thesaurusConcepts']);
        $xml = $this->exporter->export(collect([$slip]));
        $files = [];
        foreach ($slip->records ?? [] as $record) {
            foreach ($record->attachments ?? [] as $att) {
                $files[] = $att;
            }
        }
        return $this->buildZip($xml, $files);
    }

    private function buildZip(string $xml, array $attachments): array
    {
        $hash = hash('sha512', $xml);
        $dir = storage_path('app/exports/seda');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $zipPath = $dir . DIRECTORY_SEPARATOR . $hash . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new SedaZipException('Unable to create ZIP at ' . $zipPath);
        }

        // Add manifest
        $zip->addFromString('manifest.xml', $xml);

        // Prepare a list file and add attachments
        $listLines = [];
        foreach ($attachments as $att) {
            $sourcePath = $this->resolveAttachmentPath($att->path ?? null);
            $filename = $att->name ?: ('file_' . ($att->id ?? uniqid()) );
            $targetPath = 'objects/' . $filename;
            if ($sourcePath && is_file($sourcePath)) {
                $zip->addFile($sourcePath, $targetPath);
                $digest = $att->crypt_sha512 ?: hash_file('sha512', $sourcePath);
                $listLines[] = $targetPath . "\t" . ($att->mime_type ?? '') . "\t" . ($att->size ?? filesize($sourcePath)) . "\t" . $digest;
            } else {
                $msg = 'Attachment not found for ZIP: ' . ($att->path ?? '[null]');
                Log::warning($msg);
                $listLines[] = $targetPath . "\tMISSING";
            }
        }

        $zip->addFromString('files.txt', implode("\n", $listLines) . (count($listLines) ? "\n" : ''));
        $zip->close();

        return [$zipPath, $hash . '.zip'];
    }

    private function resolveAttachmentPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        // Absolute path
        if (is_file($path)) {
            return $path;
        }
        // Common bases
        $candidates = [
            storage_path('app/' . ltrim($path, '/\\')),
            storage_path('app/public/' . ltrim($path, '/\\')),
            public_path(ltrim($path, '/\\')),
            base_path(ltrim($path, '/\\')),
        ];
        $found = null;
        foreach ($candidates as $cand) {
            if (is_file($cand)) {
                $found = $cand;
                break;
            }
        }
        return $found;
    }
}
