<?php

namespace App\Services\AI;

use App\Models\Activity;
use App\Models\Record;

class AiRecordContextBuilder
{
    public function buildActivitiesListText(): string
    {
        $rows = Activity::query()->orderBy('id')->get(['id','code','name']);
        return $rows->map(fn($r) => $r->id . ' | ' . (string)($r->code ?? '') . ' | ' . (string)($r->name ?? ''))->implode("\n");
    }

    public function buildRecordsActivityContext(array $ids): string
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        if (empty($ids)) { return ''; }
        $recs = Record::query()->whereIn('id', $ids)->get(['id','name','content','activity_id']);
        return $recs->map(function ($r) {
            $name = trim((string)($r->name ?? ''));
            $content = trim((string)($r->content ?? ''));
            if ($content !== '') { $content = mb_substr(preg_replace('/\s+/u',' ', $content), 0, 300); }
            return "Record #{$r->id}: " . ($name !== '' ? $name : '[sans titre]') . ($content !== '' ? " — " . $content : '');
        })->implode("\n");
    }

    public function buildRecordsSummaryText(array $ids): string
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        if (empty($ids)) { return ''; }

        $records = $this->fetchRecordsForSummary($ids);
        $parts = [];
        foreach ($records as $r) {
            $parts[] = implode("\n", $this->buildSummaryLines($r));
        }
        return implode("\n\n---\n\n", $parts);
    }

    public function fetchRecordsForSummary(array $ids)
    {
        return Record::query()
            ->with([
                'activity:id,name', 'level:id,name', 'support:id,name', 'status:id,name', 'authors:id,name',
                'thesaurusConcepts' => function ($q) {
                    $q->with(['labels' => function ($q2) { $q2->where('type', 'prefLabel')->where('language', 'fr-fr'); }]);
                },
            ])
            ->whereIn('id', $ids)
            ->get([
                'id','code','name','date_format','date_start','date_end','date_exact',
                'biographical_history','archival_history','acquisition_source','content','appraisal','accrual','arrangement',
                'access_conditions','reproduction_conditions','language_material','characteristic','finding_aids',
                'location_original','location_copy','related_unit','publication_note','note','archivist_note','rule_convention'
            ]);
    }

    public function buildSummaryLines($r): array
    {
        $lines = [];
        $lines[] = 'Enregistrement #' . $r->id;
        if (!empty($r->name)) { $lines[] = 'Titre: ' . $r->name; }
        if (!empty($r->code)) { $lines[] = 'Cote: ' . $r->code; }
        $dateStr = $this->formatDate($r);
        if ($dateStr) { $lines[] = 'Dates: ' . $dateStr; }

        if ($r->activity?->name) { $lines[] = 'Activité: ' . $r->activity->name; }
        if ($r->level?->name) { $lines[] = 'Niveau: ' . $r->level->name; }
        if ($r->support?->name) { $lines[] = 'Support: ' . $r->support->name; }
        if ($r->status?->name) { $lines[] = 'Statut: ' . $r->status->name; }
        $authors = $this->formatAuthors($r);
        if ($authors) { $lines[] = 'Auteurs: ' . $authors; }
        $labels = $this->collectThesaurusLabels($r, 12);
        if ($labels) { $lines[] = 'Thésaurus: ' . implode(', ', $labels); }

        $this->appendSummaryLongFields($lines, $r);
        return $lines;
    }

    public function formatDate($r): string
    {
        if (!empty($r->date_exact) && $r->date_exact && !empty($r->date_start)) {
            return (string)$r->date_start;
        }
        $start = $r->date_start ? (string)$r->date_start : '';
        $end = $r->date_end ? (string)$r->date_end : '';
        return ($start || $end) ? trim($start . ' - ' . $end, ' -') : '';
    }

    public function formatAuthors($r): ?string
    {
        if ($r->authors && $r->authors->count()) {
            return implode(', ', $r->authors->pluck('name')->filter()->take(8)->all());
        }
        return null;
    }

    public function collectThesaurusLabels($r, int $limit): array
    {
        $labels = [];
        if ($r->thesaurusConcepts && $r->thesaurusConcepts->count()) {
            foreach ($r->thesaurusConcepts as $c) {
                $lbl = $c->labels->first();
                if ($lbl && !empty($lbl->literal_form)) { $labels[] = $lbl->literal_form; }
                if (count($labels) >= $limit) { break; }
            }
        }
        return $labels;
    }

    public function appendSummaryLongFields(array &$lines, $r): void
    {
        $longFields = [
            'biographical_history' => 'Historique biographique',
            'archival_history' => 'Historique de la conservation',
            'acquisition_source' => "Source d'acquisition",
            'content' => 'Contenu',
            'appraisal' => 'Évaluation',
            'accrual' => 'Accroissements',
            'arrangement' => 'Mode de classement',
            'access_conditions' => "Conditions d'accès",
            'reproduction_conditions' => 'Reproduction',
            'language_material' => 'Langue(s)',
            'characteristic' => 'Caractéristiques matérielles',
            'finding_aids' => 'Instruments de recherche',
            'location_original' => 'Localisation originaux',
            'location_copy' => 'Localisation copies',
            'related_unit' => 'Unités liées',
            'publication_note' => 'Publication',
            'note' => 'Note',
            'archivist_note' => "Note de l'archiviste",
            'rule_convention' => 'Règles et conventions',
        ];
        foreach ($longFields as $field => $label) {
            $val = (string) ($r->{$field} ?? '');
            if ($val !== '') { $lines[] = $label . ': ' . $this->softTrim($val, 1200); }
        }
    }

    public function buildRecordsThesaurusText(array $ids): string
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        if (count($ids) > 1) { $ids = [ $ids[0] ]; }
        if (empty($ids)) { return ''; }

        $records = $this->fetchRecordsForThesaurus($ids);
        $parts = [];
        foreach ($records as $r) {
            $parts[] = implode("\n", $this->buildThesaurusLines($r));
        }
        $out = implode("\n\n---\n\n", $parts);
        return $this->softTrim($out, 4800);
    }

    public function fetchRecordsForThesaurus(array $ids)
    {
        return Record::query()
            ->with([
                'authors:id,name',
                'thesaurusConcepts' => function ($q) { $q->with(['labels' => function ($q2) { $q2->where('type', 'prefLabel')->where('language', 'fr-fr'); }]); },
            ])
            ->whereIn('id', $ids)
            ->get(['id','code','name','date_start','date_end','date_exact','content','note','archivist_note','finding_aids']);
    }

    public function buildThesaurusLines($r): array
    {
        $lines = [];
        $lines[] = 'Enregistrement #' . $r->id;
        if (!empty($r->name)) { $lines[] = 'Titre: ' . $r->name; }
        if (!empty($r->code)) { $lines[] = 'Cote: ' . $r->code; }
        $dateStr = $this->formatDate($r);
        if ($dateStr) { $lines[] = 'Dates: ' . $dateStr; }

        $authors = $this->formatAuthors($r);
        if ($authors) { $lines[] = 'Auteurs: ' . $authors; }
        $labels = $this->collectThesaurusLabels($r, 12);
        if ($labels) { $lines[] = 'Thésaurus existants: ' . implode(', ', $labels); }

        foreach (['content' => 'Contenu', 'finding_aids' => 'Instruments', 'note' => 'Note', 'archivist_note' => "Note de l'archiviste"] as $field => $label) {
            $val = (string) ($r->{$field} ?? '');
            if ($val !== '') { $lines[] = $label . ': ' . $this->softTrim($val, 800); }
        }
        return $lines;
    }

    public function softTrim(string $text, int $max): string
    {
        $text = trim($text);
        if (mb_strlen($text) <= $max) { return $text; }
        $snippet = mb_substr($text, 0, $max);
        $pos = max(mb_strrpos($snippet, '.'), mb_strrpos($snippet, '!'), mb_strrpos($snippet, '?'));
        if ($pos !== false && $pos > ($max * 0.6)) { $snippet = mb_substr($snippet, 0, $pos + 1); }
        return rtrim($snippet) . '…';
    }
}
