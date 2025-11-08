<?php

namespace App\Exports;

use App\Models\RecordPhysical;
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

        // Dates (exact or ranges)
        if (!empty($record->date_exact)) {
            $ud = $did->addChild('unitdate', (string)$record->date_exact);
            $ud->addAttribute('normal', (string)$record->date_exact);
        } elseif (!empty($record->date_start) || !empty($record->date_end)) {
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

        // Repository (holding organisation)
        if (method_exists($record, 'organisation') && $record->organisation) {
            $repo = $did->addChild('repository');
            $repo->addChild('corpname', (string)($record->organisation->name ?? ''));
        }

        // Origination / producers (authors)
        if (method_exists($record, 'authors') && $record->authors && $record->authors->count()) {
            $orig = $did->addChild('origination');
            foreach ($record->authors as $author) {
                $orig->addChild('persname', (string)($author->name ?? ''));
            }
        }

        // Containers as EAD3 containers (include optional description from pivot via recordContainers)
        if (method_exists($record, 'recordContainers') && $record->recordContainers && $record->recordContainers->count()) {
            foreach ($record->recordContainers as $rc) {
                $containerEl = $did->addChild('container', (string)($rc->container->code ?? ''));
                if (!empty($rc->container_id)) {
                    $containerEl->addAttribute('containerid', (string)$rc->container_id);
                }
                if (!empty($rc->description)) {
                    $dn = $containerEl->addChild('descriptivenote');
                    $dn->addChild('p', (string)$rc->description);
                }
            }
        } elseif (method_exists($record, 'containers') && $record->containers) {
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

        // Physical description (dimensions)
        if (!empty($record->width) || !empty($record->width_description) || !empty($record->characteristic)) {
            $phys = $did->addChild('physdesc');
            if (!empty($record->width)) {
                $phys->addChild('dimensions', (string)$record->width . ' cm');
            }
            if (!empty($record->width_description)) {
                $phys->addChild('physfacet', (string)$record->width_description);
            }
            if (!empty($record->characteristic)) {
                $phys->addChild('materialspec', (string)$record->characteristic);
            }
        }

        // Language usage
        if (!empty($record->language_material)) {
            $did->addChild('langusage', (string)$record->language_material);
        }

        // Locations
        if (!empty($record->location_original)) {
            $did->addChild('physloc', (string)$record->location_original)->addAttribute('label', 'original');
        }
        if (!empty($record->location_copy)) {
            $did->addChild('physloc', (string)$record->location_copy)->addAttribute('label', 'copy');
        }

        // Content/notes
        if (!empty($record->content)) {
            $scopecontent = $c->addChild('scopecontent');
            $scopecontent->addChild('p', (string)$record->content);
        }

        if (!empty($record->biographical_history)) {
            $bh = $c->addChild('bioghist');
            $bh->addChild('p', (string)$record->biographical_history);
        }

        if (!empty($record->archival_history)) {
            $ch = $c->addChild('custodhist');
            $ch->addChild('p', (string)$record->archival_history);
        }

        if (!empty($record->acquisition_source)) {
            $acq = $c->addChild('acqinfo');
            $acq->addChild('p', (string)$record->acquisition_source);
        }

        if (!empty($record->appraisal)) {
            $ap = $c->addChild('appraisal');
            $ap->addChild('p', (string)$record->appraisal);
        }

        if (!empty($record->accrual)) {
            $ac = $c->addChild('accruals');
            $ac->addChild('p', (string)$record->accrual);
        }

        if (!empty($record->arrangement)) {
            $arr = $c->addChild('arrangement');
            $arr->addChild('p', (string)$record->arrangement);
        }

        if (!empty($record->access_conditions)) {
            $ar = $c->addChild('accessrestrict');
            $ar->addChild('p', (string)$record->access_conditions);
        }

        if (!empty($record->reproduction_conditions)) {
            $ur = $c->addChild('userestrict');
            $ur->addChild('p', (string)$record->reproduction_conditions);
        }

        if (!empty($record->finding_aids)) {
            $ofa = $c->addChild('otherfindaid');
            $ofa->addChild('p', (string)$record->finding_aids);
        }

        if (!empty($record->related_unit)) {
            $rel = $c->addChild('relatedmaterial');
            $rel->addChild('p', (string)$record->related_unit);
        }

        if (!empty($record->publication_note)) {
            $bib = $c->addChild('bibliography');
            $bib->addChild('p', (string)$record->publication_note);
        }

        // Misc/general notes (odd)
        if (!empty($record->note)) {
            $odd = $c->addChild('odd');
            $odd->addChild('p', (string)$record->note);
        }

        if (!empty($record->archivist_note)) {
            $pi = $c->addChild('processinfo');
            $pi->addChild('p', (string)$record->archivist_note);
        }

        if (!empty($record->rule_convention)) {
            $odd2 = $c->addChild('odd');
            $odd2->addChild('head', 'Rules/Conventions');
            $odd2->addChild('p', (string)$record->rule_convention);
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

        // Additional subjects: activity, support, status
        $extraSubjects = [];
        if (method_exists($record, 'activity') && $record->activity && !empty($record->activity->name)) {
            $extraSubjects[] = (string)$record->activity->name;
        }
        if (method_exists($record, 'support') && $record->support && !empty($record->support->name)) {
            $extraSubjects[] = (string)$record->support->name;
        }
        if (method_exists($record, 'status') && $record->status && !empty($record->status->name)) {
            $extraSubjects[] = 'Statut: ' . (string)$record->status->name;
        }
        if (!empty($extraSubjects)) {
            $ca2 = $c->addChild('controlaccess');
            foreach ($extraSubjects as $label) {
                $ca2->addChild('subject')->addChild('part', $label);
            }
        }

        // Nested children if present (hierarchy)
        if (method_exists($record, 'children') && $record->relationLoaded('children') && $record->children->isNotEmpty()) {
            foreach ($record->children as $child) {
                $this->addRecordComponent($c, $child);
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
