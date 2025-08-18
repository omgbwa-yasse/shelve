<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Record;
use App\Models\ThesaurusConcept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AiRecordApplyController extends Controller
{
    private const RULE_RAW = 'nullable|string|min:1';
    private const ERR_NO_DATA = 'No data provided';

    public function saveTitle(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'title' => 'nullable|string|min:1|max:255',
            'raw_text' => self::RULE_RAW,
        ]);
        $value = $data['title'] ?? $data['raw_text'] ?? null;
        if ($value === null) {
            return response()->json(['status' => 'error', 'message' => self::ERR_NO_DATA], 422);
        }
        // If raw_text provided, still enforce DB constraint via max length
        $record->name = mb_substr($value, 0, 255);
        $record->save();
        return response()->json(['status' => 'ok', 'record_id' => $record->id, 'name' => $record->name]);
    }

    public function saveSummary(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'summary' => 'nullable|string|min:1',
            'raw_text' => self::RULE_RAW,
        ]);
        $value = $data['summary'] ?? $data['raw_text'] ?? null;
        if ($value === null) {
            return response()->json(['status' => 'error', 'message' => self::ERR_NO_DATA], 422);
        }
        $record->content = $value;
        $record->save();
        return response()->json(['status' => 'ok', 'record_id' => $record->id]);
    }

    public function saveThesaurus(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'concepts' => 'nullable|array|min:1',
            'concepts.*.id' => 'nullable|integer|exists:thesaurus_concepts,id',
            'concepts.*.preferred_label' => 'nullable|string',
            'concepts.*.weight' => 'nullable|numeric|min:0|max:1',
            'raw_text' => self::RULE_RAW,
        ]);

        $attach = [];
        if (!empty($data['concepts'])) {
            $attach = $this->attachFromConceptArray($data['concepts']);
        } elseif (!empty($data['raw_text'])) {
            $attach = $this->attachFromRawLabels($data['raw_text']);
        } else {
            return response()->json(['status' => 'error', 'message' => self::ERR_NO_DATA], 422);
        }
        if (!empty($attach)) {
            $record->thesaurusConcepts()->syncWithoutDetaching($attach);
        }
        return response()->json(['status' => 'ok', 'record_id' => $record->id, 'attached' => array_keys($attach)]);
    }

    public function saveActivity(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'activity_id' => 'nullable|integer|exists:activities,id',
            'activity_name' => 'nullable|string',
            'raw_text' => self::RULE_RAW,
        ]);

        $activityId = $this->resolveActivityId($data);
        if ($activityId === null) {
            return response()->json(['status' => 'error', 'message' => 'Activity not found'], 422);
        }
        $record->activity_id = $activityId;

        $record->save();
        return response()->json(['status' => 'ok', 'record_id' => $record->id, 'activity_id' => $record->activity_id]);
    }

    private function parseList(string $text): array
    {
        $parts = preg_split('/[\n,;\t\x{2022}\*]+/u', str_replace("\r", '', $text));
        $out = [];
        $seen = [];
        foreach ($parts as $p) {
            $t = trim((string)$p);
            if ($t === '') { continue; }
            if (isset($seen[mb_strtolower($t)])) { continue; }
            $seen[mb_strtolower($t)] = true;
            $out[] = $t;
            if (count($out) >= 30) { break; }
        }
        return $out;
    }

    private function attachFromConceptArray(array $concepts): array
    {
        $attach = [];
        foreach ($concepts as $c) {
            if (!empty($c['id'])) {
                $attach[(int)$c['id']] = [
                    'weight' => isset($c['weight']) ? (float)$c['weight'] : 0.7,
                    'context' => 'ai',
                    'extraction_note' => null,
                ];
            } elseif (!empty($c['preferred_label'])) {
                $concept = $this->findConceptByLabel((string)$c['preferred_label']);
                if ($concept) {
                    $attach[$concept->id] = [
                        'weight' => isset($c['weight']) ? (float)$c['weight'] : 0.7,
                        'context' => 'ai',
                        'extraction_note' => null,
                    ];
                }
            }
        }
        return $attach;
    }

    private function attachFromRawLabels(string $raw): array
    {
        $attach = [];
        $labels = $this->parseList($raw);
        foreach ($labels as $label) {
            $concept = $this->findConceptByLabel($label);
            if ($concept) {
                $attach[$concept->id] = [
                    'weight' => 0.7,
                    'context' => 'ai',
                    'extraction_note' => null,
                ];
            }
        }
        return $attach;
    }

    /**
     * Resolve a concept by a human label using labels relation (prefLabel preferred, then altLabel), any language.
     */
    private function findConceptByLabel(string $label): ?ThesaurusConcept
    {
        $label = trim($label);
        if ($label === '') { return null; }
        $q = ThesaurusConcept::query()
            ->whereHas('labels', function ($sub) use ($label) {
                $sub->where('literal_form', $label)->where('type', 'prefLabel');
            });
        $concept = $q->first();
        if ($concept) { return $concept; }
        // Fallback to altLabel
        return ThesaurusConcept::query()
            ->whereHas('labels', function ($sub) use ($label) {
                $sub->where('literal_form', $label)->where('type', 'altLabel');
            })
            ->first();
    }

    private function resolveActivityId(array $data): ?int
    {
        $id = null;
        if (!empty($data['activity_id'])) {
            $id = (int)$data['activity_id'];
        } elseif (!empty($data['activity_name'])) {
            $activity = Activity::where('name', $data['activity_name'])->first();
            $id = $activity?->id;
        } elseif (!empty($data['raw_text'])) {
            $candidates = $this->parseList($data['raw_text']);
            foreach ($candidates as $cand) {
                $found = Activity::where('name', $cand)->first();
                if ($found) { $id = $found->id; break; }
            }
        }
        return $id;
    }
}
