<?php

namespace App\Exports;

use App\Models\Record;
use App\Models\Slip;
use SimpleXMLElement;

class EADExport
{
    protected SimpleXMLElement $xml;

    public function __construct()
    {
        // EAD3 root with default namespace (no xlink needed; EAD3 uses unprefixed href/title attributes)
        $this->xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ead xmlns="http://ead3.archivists.org/schema/"></ead>');
    }

    // Export a set of slips, each with nested records
    public function export($slips): string
    {
        $this->addControl('Export des bordereaux de versement');
        $this->addArchDescForSlips($slips);
        return $this->xml->asXML();
    }

    // Export a set of records (without slips)
    public function exportRecords($records): string
    {
        $this->addControl('Export des unités documentaires');
        $this->addArchDescForRecords($records);
        return $this->xml->asXML();
    }

    protected function addControl(string $title): void
    {
        // EAD3 control section
        $control = $this->xml->addChild('control');
        $control->addAttribute('countryencoding', 'iso3166-1');
        $control->addAttribute('dateencoding', 'iso8601');
        $control->addAttribute('langencoding', 'iso639-2b');
        $control->addAttribute('repositoryencoding', 'iso15511');
        $control->addAttribute('scriptencoding', 'iso15924');

        $control->addChild('recordid', 'EAD3_Export_' . time());

        $filedesc = $control->addChild('filedesc');
        $titlestmt = $filedesc->addChild('titlestmt');
        $titlestmt->addChild('titleproper', $title);
        $titlestmt->addChild('author', 'Système d\'archivage');
        $publicationstmt = $filedesc->addChild('publicationstmt');
        $publicationstmt->addChild('publisher', 'Service d\'archives');
        $publicationstmt->addChild('date', date('Y-m-d'));

        $control->addChild('maintenancestatus')->addAttribute('value', 'new');
        $agency = $control->addChild('maintenanceagency');
        // Optional countrycode
        $agency->addChild('agencyname', 'Service d\'archives applicatif');
    }

    protected function addArchDescForSlips($slips): void
    {
        $archdesc = $this->xml->addChild('archdesc');
        $archdesc->addAttribute('level', 'collection');

        $did = $archdesc->addChild('did');
        $did->addChild('unittitle', 'Collection des bordereaux de versement');
        $did->addChild('unitdate', date('Y-m-d'));
        $did->addChild('unitid', 'COL-BV-' . date('Ymd'));

        // Components go inside a dsc
        $dsc = $archdesc->addChild('dsc');
        foreach ($slips as $slip) {
            $this->addSlipComponent($dsc, $slip);
        }
    }

    protected function addArchDescForRecords($records): void
    {
        $archdesc = $this->xml->addChild('archdesc');
        $archdesc->addAttribute('level', 'collection');

        $did = $archdesc->addChild('did');
        $did->addChild('unittitle', 'Collection d\'unités documentaires');
        $did->addChild('unitdate', date('Y-m-d'));
        $did->addChild('unitid', 'COL-UD-' . date('Ymd'));

        $dsc = $archdesc->addChild('dsc');
        foreach ($records as $record) {
            $this->addRecordComponent($dsc, $record);
        }
    }

    protected function addSlipComponent(SimpleXMLElement $parent, Slip $slip): void
    {
        $c = $parent->addChild('c');
        $c->addAttribute('level', 'file');

        $did = $c->addChild('did');
        $did->addChild('unittitle', (string)($slip->name ?? ('Bordereau ' . $slip->id)));
        if (!empty($slip->code)) {
            $did->addChild('unitid', (string)$slip->code);
        }
        if (!empty($slip->created_at)) {
            $did->addChild('unitdate', $slip->created_at->format('Y-m-d'));
        }

        if (!empty($slip->description)) {
            $scopecontent = $c->addChild('scopecontent');
            $scopecontent->addChild('p', (string)$slip->description);
        }

        // Nest records under this slip component if available
        if (method_exists($slip, 'records') && $slip->records) {
            foreach ($slip->records as $record) {
                $this->addRecordComponent($c, $record);
            }
        }
    }

    protected function addRecordComponent(SimpleXMLElement $parent, $record): void
    {
        $c = $parent->addChild('c');
        $c->addAttribute('level', $this->mapDescriptionLevel(optional($record->level)->name));

        $did = $c->addChild('did');
        $title = $record->name ?: ('Record ' . $record->id);
        $did->addChild('unittitle', (string)$title);

        if (!empty($record->code)) {
            $did->addChild('unitid', (string)$record->code);
        } else {
            $did->addChild('unitid', 'R-' . $record->id);
        }

        // Dates
        if (!empty($record->date_start) || !empty($record->date_end)) {
            $start = (string)($record->date_start ?? '');
            $end = (string)($record->date_end ?? '');
            if ($start && $end) {
                $ud = $did->addChild('unitdate', $start . ' - ' . $end);
                $ud->addAttribute('unitdatetype', 'inclusive');
                $ud->addAttribute('normal', $start . '/' . $end);
            } else {
                $dateVal = $start ?: $end;
                $ud = $did->addChild('unitdate', $dateVal);
                $ud->addAttribute('normal', $dateVal);
            }
        }

        // Containers as EAD3 containers
        if (method_exists($record, 'containers')) {
            foreach ($record->containers as $container) {
                $containerEl = $did->addChild('container', (string)($container->code ?? $container->label ?? ''));
                if (!empty($container->id)) {
                    $containerEl->addAttribute('containerid', (string)$container->id);
                }
                if (!empty($container->parent_id)) {
                    $containerEl->addAttribute('parent', 'C-' . $container->parent_id);
                }
            }
        }

        // Attachments as digital object links (dao) with href
        if (method_exists($record, 'attachments')) {
            foreach ($record->attachments as $att) {
                $dao = $did->addChild('dao');
                $dao->addAttribute('daotype', 'borndigital');
                if (!empty($att->name)) {
                    $dao->addAttribute('label', (string)$att->name);
                }
                if (!empty($att->path)) {
                    $dao->addAttribute('href', (string)$att->path);
                }
                if (!empty($att->mime_type)) {
                    // optional descriptive note for mime type
                    $dn = $dao->addChild('descriptivenote');
                    $dn->addChild('p', 'MIME: ' . $att->mime_type);
                }
            }
        }

        // Content/notes
        if (!empty($record->content)) {
            $scopecontent = $c->addChild('scopecontent');
            $scopecontent->addChild('p', (string)$record->content);
        }

        // Keywords / subjects from thesaurus
        if (method_exists($record, 'thesaurusConcepts') && $record->thesaurusConcepts && $record->thesaurusConcepts->count()) {
            $controlaccess = $c->addChild('controlaccess');
            foreach ($record->thesaurusConcepts as $concept) {
                $label = $concept->preferred_label ?? $concept->notation ?? $concept->uri;
                if (!empty($label)) {
                    $subject = $controlaccess->addChild('subject');
                    $subject->addChild('part', (string)$label);
                }
            }
        }
    }

    protected function mapDescriptionLevel(?string $levelName): string
    {
        $map = [
            'fonds' => 'fonds',
            'subfonds' => 'subfonds',
            'class' => 'class',
            'collection' => 'collection',
            'series' => 'series',
            'subseries' => 'subseries',
            'recordgrp' => 'recordgrp',
            'subgrp' => 'subgrp',
            'file' => 'file',
            'item' => 'item',
        ];
        if (empty($levelName)) {
            return 'item';
        }
        $key = strtolower(trim($levelName));
        return $map[$key] ?? 'otherlevel';
    }
}
