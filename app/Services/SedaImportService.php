<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Dolly;
use App\Models\Record;
use App\Models\Slip;
use App\Models\SlipRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class SedaImportService
{
    private const NS = 'fr:gouv:culture:archivesdefrance:seda:v2.1';

    public function importRecordsFromZip(string $zipPath, Dolly $dolly): int
    {
        [$xml, $extractDir] = $this->extractZip($zipPath);
        try {
            $map = $this->buildDataObjectMap($xml);
            $count = 0;
            foreach ($xml->xpath('//s:DataObjectPackage/s:DescriptiveMetadata/s:ArchiveUnit') as $unit) {
                $record = $this->createRecordFromUnit($unit);
                $dolly->records()->attach($record->id);
                $this->attachUnitFiles($unit, $record, $map, $extractDir, 'record');
                $count++;
            }
            return $count;
        } finally {
            $this->cleanup($extractDir);
        }
    }

    public function importRecordsFromString(string $xmlString, Dolly $dolly): int
    {
        $xml = $this->loadXml($xmlString);
        $map = $this->buildDataObjectMap($xml);
        $count = 0;
        foreach ($xml->xpath('//s:DataObjectPackage/s:DescriptiveMetadata/s:ArchiveUnit') as $unit) {
            $record = $this->createRecordFromUnit($unit);
            $dolly->records()->attach($record->id);
            $this->attachUnitFiles($unit, $record, $map, null, 'record');
            $count++;
        }
        return $count;
    }

    public function importSlipFromZip(string $zipPath, Dolly $dolly): int
    {
        [$xml, $extractDir] = $this->extractZip($zipPath);
        try {
            $slip = Slip::create([
                'name' => 'SEDA Import ' . date('Y-m-d H:i:s'),
                'description' => 'Imported from SEDA package',
                'user_id' => Auth::id() ?? 1,
                'user_organisation_id' => optional(Auth::user())->current_organisation_id ?? 1,
            ]);
            $dolly->slips()->attach($slip->id);

            $map = $this->buildDataObjectMap($xml);
            $count = 0;
            foreach ($xml->xpath('//s:DataObjectPackage/s:DescriptiveMetadata/s:ArchiveUnit') as $unit) {
                $sr = $this->createSlipRecordFromUnit($slip, $unit);
                $this->attachUnitFiles($unit, $sr, $map, $extractDir, 'slip_record');
                $count++;
            }
            return $count; // number of slip records
        } finally {
            $this->cleanup($extractDir);
        }
    }

    public function importSlipFromString(string $xmlString, Dolly $dolly): int
    {
        $xml = $this->loadXml($xmlString);
        $slip = Slip::create([
            'name' => 'SEDA Import ' . date('Y-m-d H:i:s'),
            'description' => 'Imported from SEDA manifest',
            'user_id' => Auth::id() ?? 1,
            'user_organisation_id' => optional(Auth::user())->current_organisation_id ?? 1,
        ]);
        $dolly->slips()->attach($slip->id);

        $map = $this->buildDataObjectMap($xml);
        $count = 0;
        foreach ($xml->xpath('//s:DataObjectPackage/s:DescriptiveMetadata/s:ArchiveUnit') as $unit) {
            $sr = $this->createSlipRecordFromUnit($slip, $unit);
            $this->attachUnitFiles($unit, $sr, $map, null, 'slip_record');
            $count++;
        }
        return $count;
    }

    private function loadXml(string $xmlString): \SimpleXMLElement
    {
        $xml = simplexml_load_string($xmlString);
        if (!$xml) {
            throw new \RuntimeException('Invalid SEDA XML');
        }
        $xml->registerXPathNamespace('s', self::NS);
        return $xml;
    }

    private function extractZip(string $zipPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Unable to open ZIP');
        }
        $extractDir = storage_path('app/temp_import_' . uniqid());
        @mkdir($extractDir, 0775, true);
        $zip->extractTo($extractDir);
        $zip->close();
        $manifestPath = $extractDir . DIRECTORY_SEPARATOR . 'manifest.xml';
        if (!is_file($manifestPath)) {
            // try common alternatives
            $candidates = glob($extractDir . DIRECTORY_SEPARATOR . '*.xml');
            $manifestPath = $candidates[0] ?? null;
        }
        if (!$manifestPath || !is_file($manifestPath)) {
            throw new \RuntimeException('manifest.xml not found in ZIP');
        }
        $xml = simplexml_load_file($manifestPath);
        if (!$xml) {
            throw new \RuntimeException('Invalid manifest.xml');
        }
        $xml->registerXPathNamespace('s', self::NS);
        return [$xml, $extractDir];
    }

    private function cleanup(?string $dir): void
    {
        if (!$dir || !is_dir($dir)) { return; }
        try {
            $it = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    @rmdir($file->getRealPath());
                } else {
                    @unlink($file->getRealPath());
                }
            }
            @rmdir($dir);
        } catch (\Throwable $e) {
            Log::warning('Cleanup failed: ' . $e->getMessage());
        }
    }

    private function buildDataObjectMap(\SimpleXMLElement $xml): array
    {
        $map = [
            'group' => [], // id => [bdoId,...]
            'bdo' => [],   // id => ['filename'=>?, 'uri'=>?, 'digest'=>?]
        ];
        foreach ($xml->xpath('//s:DataObjectPackage/s:DataObjectGroup') as $group) {
            $gid = (string) ($group['id'] ?? '');
            $map['group'][$gid] = [];
            foreach ($group->xpath('s:BinaryDataObject') as $bdo) {
                $bid = (string) ($bdo['id'] ?? '');
                $filename = (string) ($bdo->FileInfo->Filename ?? '');
                $uri = (string) ($bdo->Uri ?? '');
                $digest = (string) ($bdo->MessageDigest ?? '');
                $map['bdo'][$bid] = [
                    'filename' => $filename,
                    'uri' => $uri,
                    'digest' => $digest,
                ];
                $map['group'][$gid][] = $bid;
            }
        }
        return $map;
    }

    private function createRecordFromUnit($unit): Record
    {
        $content = $unit->Content ?? null;
        $title = (string) ($content->Title ?? 'Unité documentaire');
        $code = (string) ($content->TransferringAgencyArchiveUnitIdentifier ?? $content->SystemId ?? '');
        $start = (string) ($content->StartDate ?? '');
        $end = (string) ($content->EndDate ?? '');
        $desc = (string) ($content->Description ?? '');

        return Record::create([
            'code' => $code ?: null,
            'name' => $title,
            'date_start' => $start ?: null,
            'date_end' => $end ?: null,
            'content' => $desc ?: null,
            'user_id' => Auth::id() ?? 1,
            'organisation_id' => optional(Auth::user())->current_organisation_id ?? 1,
        ]);
    }

    private function createSlipRecordFromUnit(Slip $slip, $unit): SlipRecord
    {
        $content = $unit->Content ?? null;
        $title = (string) ($content->Title ?? 'Unité documentaire');
        $code = (string) ($content->TransferringAgencyArchiveUnitIdentifier ?? $content->SystemId ?? '');
        $start = (string) ($content->StartDate ?? '');
        $end = (string) ($content->EndDate ?? '');
        $desc = (string) ($content->Description ?? '');

        return SlipRecord::create([
            'slip_id' => $slip->id,
            'code' => $code ?: null,
            'name' => $title,
            'date_start' => $start ?: null,
            'date_end' => $end ?: null,
            'content' => $desc ?: null,
            'creator_id' => Auth::id() ?? 1,
        ]);
    }

    private function attachUnitFiles($unit, $ownerModel, array $map, ?string $extractDir, string $type): void
    {
        $refIds = [];
        foreach ($unit->xpath('s:DataObjectReference/s:DataObjectReferenceId') as $ref) {
            $refIds[] = (string) $ref;
        }
        $bdoIds = [];
        foreach ($refIds as $rid) {
            if (isset($map['bdo'][$rid])) {
                $bdoIds[] = $rid;
            } elseif (isset($map['group'][$rid])) {
                $bdoIds = array_merge($bdoIds, $map['group'][$rid]);
            }
        }
        $bdoIds = array_unique($bdoIds);

        foreach ($bdoIds as $bid) {
            $info = $map['bdo'][$bid] ?? null;
            if (!$info) { continue; }
            $filename = $info['filename'] ?: basename($info['uri']);
            $path = $info['uri'] ?: ($filename ? ('objects/' . $filename) : null);
            $mime = null; $size = null; $sha512 = null;

            if ($extractDir && $filename) {
                $source = $extractDir . DIRECTORY_SEPARATOR . 'objects' . DIRECTORY_SEPARATOR . $filename;
                if (is_file($source)) {
                    $targetName = $filename;
                    $target = 'public/attachments/' . $targetName;
                    @mkdir(storage_path('app/public/attachments'), 0775, true);
                    // move/copy file
                    copy($source, storage_path('app/' . $target));
                    $size = filesize($source) ?: null;
                    $sha512 = hash_file('sha512', $source);
                    $path = 'attachments/' . $targetName;
                }
            }

            $att = Attachment::create([
                'path' => $path,
                'name' => $filename ?: basename((string)$path),
                'crypt_sha512' => $sha512,
                'size' => $size,
                'creator_id' => Auth::id() ?? 1,
                'type' => $type,
                'mime_type' => $mime,
            ]);

            // Polymorphic: supports ->attachments() on both Record and SlipRecord
            $ownerModel->attachments()->attach($att->id);
        }
    }
}
