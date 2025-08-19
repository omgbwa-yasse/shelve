<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Dolly;
use App\Models\Record;
use App\Models\Slip;
use App\Models\SlipRecord;
use Illuminate\Support\Facades\Auth;

class EADImportService
{
    private const EAD3_NS = 'http://ead3.archivists.org/schema/';

    /**
     * Import records from an EAD3 XML string and attach them to the provided Dolly
     * Returns number of records created.
     */
    public function importRecordsFromString(string $xmlString, Dolly $dolly): int
    {
        $xml = simplexml_load_string($xmlString);
        if (!$xml) {
            throw new \RuntimeException('Invalid EAD XML');
        }
        $xml->registerXPathNamespace('ead', self::EAD3_NS);

        $count = 0;
        $components = $xml->xpath('//ead:archdesc/ead:dsc//ead:c');
        if (!$components) { return 0; }

        foreach ($components as $c) {
            // Build record data
            $did = $c->did ?? null;
            $name = $this->stringOrNull($did?->unittitle);
            $code = $this->stringOrNull($did?->unitid);
            [$dateStart, $dateEnd] = $this->extractDates($did);
            $content = $this->stringOrNull(($c->scopecontent?->p) ?? null);

            $record = Record::create([
                'code' => $code ?: null,
                'name' => $name ?: ($code ?: 'Unité documentaire'),
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'content' => $content,
                'user_id' => Auth::id() ?? 1,
                'organisation_id' => optional(Auth::user())->current_organisation_id ?? 1,
            ]);

            $dolly->records()->attach($record->id);
            $count++;

            // Attachments from dao
            if (isset($did->dao)) {
                foreach ($did->dao as $dao) {
                    $href = (string) ($dao['href'] ?? '');
                    $label = (string) ($dao['label'] ?? '');
                    $mime = null;
                    if (isset($dao->descriptivenote) && isset($dao->descriptivenote->p)) {
                        $p = (string) $dao->descriptivenote->p;
                        if (str_starts_with($p, 'MIME:')) { $mime = trim(substr($p, 5)); }
                    }
                    if ($href !== '') {
                        $att = Attachment::create([
                            'path' => $href,
                            'name' => $label ?: basename($href),
                            'creator_id' => Auth::id() ?? 1,
                            'type' => 'record',
                            'mime_type' => $mime,
                        ]);
                        $record->attachments()->attach($att->id);
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Import slips (and nested slip records) from an EAD3 XML string and attach the slips to the Dolly.
     * Returns the number of slips created.
     */
    public function importSlipsFromString(string $xmlString, Dolly $dolly): int
    {
        $xml = simplexml_load_string($xmlString);
        if (!$xml) {
            throw new \RuntimeException('Invalid EAD XML');
        }
        $xml->registerXPathNamespace('ead', self::EAD3_NS);

        $slipComponents = $xml->xpath('//ead:archdesc/ead:dsc/ead:c');
        $created = 0;

        foreach ($slipComponents ?: [] as $c) {
            $did = $c->did ?? null;
            $slipName = $this->stringOrNull($did?->unittitle) ?: 'Bordereau';
            $slipCode = $this->stringOrNull($did?->unitid);
            $slipDesc = $this->stringOrNull(($c->scopecontent?->p) ?? null);

            $slip = Slip::create([
                'name' => $slipName,
                'code' => $slipCode,
                'description' => $slipDesc,
                'user_id' => Auth::id() ?? 1,
                'user_organisation_id' => optional(Auth::user())->current_organisation_id ?? 1,
            ]);
            $dolly->slips()->attach($slip->id);
            $created++;

            // Nested records under this slip
            $recordComponents = $c->xpath('ead:c');
            foreach ($recordComponents ?: [] as $rc) {
                $rdid = $rc->did ?? null;
                $name = $this->stringOrNull($rdid?->unittitle) ?: 'Unité documentaire';
                $code = $this->stringOrNull($rdid?->unitid);
                [$dateStart, $dateEnd] = $this->extractDates($rdid);
                $content = $this->stringOrNull(($rc->scopecontent?->p) ?? null);

                $sr = SlipRecord::create([
                    'slip_id' => $slip->id,
                    'code' => $code,
                    'name' => $name,
                    'date_start' => $dateStart,
                    'date_end' => $dateEnd,
                    'content' => $content,
                    'creator_id' => Auth::id() ?? 1,
                ]);

                // Attachments from dao
                if (isset($rdid->dao)) {
                    foreach ($rdid->dao as $dao) {
                        $href = (string) ($dao['href'] ?? '');
                        $label = (string) ($dao['label'] ?? '');
                        $mime = null;
                        if (isset($dao->descriptivenote) && isset($dao->descriptivenote->p)) {
                            $p = (string) $dao->descriptivenote->p;
                            if (str_starts_with($p, 'MIME:')) { $mime = trim(substr($p, 5)); }
                        }
                        if ($href !== '') {
                            $att = Attachment::create([
                                'path' => $href,
                                'name' => $label ?: basename($href),
                                'creator_id' => Auth::id() ?? 1,
                                'type' => 'slip_record',
                                'mime_type' => $mime,
                            ]);
                            $sr->attachments()->attach($att->id);
                        }
                    }
                }
            }
        }

        return $created;
    }

    private function extractDates($did): array
    {
        $start = null; $end = null;
        if (!$did) { return [null, null]; }
        if (isset($did->unitdate)) {
            foreach ($did->unitdate as $ud) {
                $normal = (string) ($ud['normal'] ?? '');
                $text = trim((string) $ud);
                if ($normal && str_contains($normal, '/')) {
                    [$s, $e] = explode('/', $normal, 2);
                    $start = $start ?: ($s ?: null);
                    $end = $end ?: ($e ?: null);
                } elseif ($normal) {
                    $start = $start ?: $normal;
                } elseif ($text) {
                    $start = $start ?: $text;
                }
            }
        }
        return [$start, $end];
    }

    private function stringOrNull($node): ?string
    {
        if ($node === null) { return null; }
        $s = trim((string) $node);
        return $s !== '' ? $s : null;
    }
}
