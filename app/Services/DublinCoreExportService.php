<?php
namespace App\Services;

use App\Models\Record;
use Illuminate\Support\Collection;

class DublinCoreExportService
{
    /**
     * Export records as Dublin Core XML (OAI-PMH oai_dc format)
     */
    public function exportRecords(Collection $records): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd"/>' );
        foreach ($records as $record) {
            // dc:identifier (URL, code, and other IDs)
            $xml->addChild('dc:identifier', url('/records/' . $record->id), 'http://purl.org/dc/elements/1.1/');
            $xml->addChild('dc:identifier', htmlspecialchars($record->code ?: ('REC-' . $record->id), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            if ($record->other_identifier) {
                $xml->addChild('dc:identifier', htmlspecialchars($record->other_identifier, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            // dc:title
            $xml->addChild('dc:title', htmlspecialchars($record->name, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            // dc:date
            if ($record->date_exact) {
                $xml->addChild('dc:date', htmlspecialchars($record->date_exact, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            } elseif ($record->date_start) {
                $xml->addChild('dc:date', htmlspecialchars($record->date_start, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            // dc:type (level)
            if ($record->level) {
                $xml->addChild('dc:type', htmlspecialchars($record->level->name, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            // dc:format (support, extent, physical details)
            if ($record->support) {
                $xml->addChild('dc:format', htmlspecialchars($record->support->name, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->extent) {
                $xml->addChild('dc:format', htmlspecialchars($record->extent, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->physical_details) {
                $xml->addChild('dc:format', htmlspecialchars($record->physical_details, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            // dc:creator (authors)
            if ($record->authors && method_exists($record->authors, 'isNotEmpty') && $record->authors->isNotEmpty()) {
                foreach ($record->authors as $author) {
                    $xml->addChild('dc:creator', htmlspecialchars($author->name, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
                }
            }
            // dc:description (content, archival_history, notes)
            if ($record->content) {
                $xml->addChild('dc:description', htmlspecialchars(strip_tags($record->content), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->archival_history) {
                $xml->addChild('dc:description', htmlspecialchars(strip_tags($record->archival_history), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->note) {
                $xml->addChild('dc:description', htmlspecialchars(strip_tags($record->note), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            // dc:provenance (custodial_history)
            if ($record->custodial_history) {
                $xml->addChild('dc:provenance', htmlspecialchars(strip_tags($record->custodial_history), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            // dc:source (acquisition_source)
            if ($record->acquisition_source) {
                $xml->addChild('dc:source', htmlspecialchars(strip_tags($record->acquisition_source), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            // dc:rights (legal_status, access, copyright)
            if ($record->legal_status) {
                $xml->addChild('dc:rights', htmlspecialchars($record->legal_status, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->status) {
                $xml->addChild('dc:rights', htmlspecialchars($record->status->name, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->copyright) {
                $xml->addChild('dc:rights', htmlspecialchars($record->copyright, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            // dc:language
            if ($record->languages && method_exists($record->languages, 'isNotEmpty') && $record->languages->isNotEmpty()) {
                foreach ($record->languages as $lang) {
                    $langNode = $xml->addChild('dc:language', htmlspecialchars($lang->code ?? 'und', ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
                    $langNode->addAttribute('xsi:type', 'dcterms:ISO639-3', 'http://www.w3.org/2001/XMLSchema-instance');
                }
            }
            // dc:relation (containers, organisation, attachments, finding aids, originals, copies, related units, associated material, bibliography)
            if ($record->containers && method_exists($record->containers, 'isNotEmpty') && $record->containers->isNotEmpty()) {
                foreach ($record->containers as $container) {
                    $xml->addChild('dc:relation', htmlspecialchars($container->code, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
                }
            }
            if ($record->organisation) {
                $xml->addChild('dc:relation', htmlspecialchars($record->organisation->name, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->attachments && method_exists($record->attachments, 'isNotEmpty') && $record->attachments->isNotEmpty()) {
                foreach ($record->attachments as $attachment) {
                    $xml->addChild('dc:relation', url($attachment->path), 'http://purl.org/dc/elements/1.1/');
                }
            }
            if ($record->finding_aids) {
                $xml->addChild('dc:relation', htmlspecialchars(strip_tags($record->finding_aids), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->originals_location) {
                $xml->addChild('dc:relation', htmlspecialchars(strip_tags($record->originals_location), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->copies_location) {
                $xml->addChild('dc:relation', htmlspecialchars(strip_tags($record->copies_location), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->related_units) {
                $xml->addChild('dc:relation', htmlspecialchars(strip_tags($record->related_units), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->associated_material) {
                $xml->addChild('dc:relation', htmlspecialchars(strip_tags($record->associated_material), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            if ($record->bibliography) {
                $xml->addChild('dc:relation', htmlspecialchars(strip_tags($record->bibliography), ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
            // dc:subject (keywords)
            if ($record->keywords && method_exists($record->keywords, 'isNotEmpty') && $record->keywords->isNotEmpty()) {
                foreach ($record->keywords as $keyword) {
                    $xml->addChild('dc:subject', htmlspecialchars($keyword->name, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
                }
            }
            // dc:publisher (organisation)
            if ($record->organisation) {
                $xml->addChild('dc:publisher', htmlspecialchars($record->organisation->name, ENT_XML1, 'UTF-8'), 'http://purl.org/dc/elements/1.1/');
            }
        }
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        return $dom->saveXML();
    }
}
