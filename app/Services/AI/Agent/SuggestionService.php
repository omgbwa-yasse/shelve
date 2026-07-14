<?php

namespace App\Services\AI\Agent;

use App\Models\Activity;
use App\Models\Keyword;
use App\Models\ThesaurusConcept;

/**
 * Suggestions de classement et d'indexation en LECTURE SEULE pour l'agent IA.
 *
 * Le scoring des activités reprend la logique de matching de
 * AiRecordApplyController::suggestActivityCandidates (code exact > nom exact >
 * inclusion > tokens) sans rien sauvegarder. Le plan de classement, les
 * mots-clés et le thésaurus sont des référentiels communs de la plateforme.
 */
class SuggestionService
{
    /**
     * Propose des activités du plan de classement pour un texte donné.
     */
    public function suggestActivities(string $text, int $limit = 10): array
    {
        $terms = $this->parseList($text);

        $tokens = [];
        $tmp = preg_split('/[^\p{L}\p{N}\-]+/u', mb_strtolower($text)) ?: [];
        foreach ($tmp as $token) {
            $token = trim($token);
            if ($token !== '' && mb_strlen($token) >= 2) {
                $tokens[$token] = true;
            }
        }

        if (empty($terms) && empty($tokens)) {
            return [];
        }

        $candidates = [];
        foreach (Activity::query()->select('id', 'code', 'name')->get() as $activity) {
            $score = 0.0;
            $name = mb_strtolower((string) $activity->name);
            $code = (string) $activity->code;
            $lowerCode = mb_strtolower($code);

            if ($code !== '') {
                foreach ($terms as $term) {
                    if (trim($term) === $code) {
                        $score += 8;
                    }
                }
                if (preg_match('/(^|\b)' . preg_quote($code, '/') . '($|\b)/iu', $text)) {
                    $score += 5;
                }
            }

            foreach ($terms as $term) {
                $lowerTerm = mb_strtolower($term);
                if ($lowerTerm === $name) {
                    $score += 6;
                } elseif ($name !== '' && str_contains($name, $lowerTerm)) {
                    $score += 3;
                }
            }

            foreach (array_keys($tokens) as $token) {
                if ($token === $name) {
                    $score += 2;
                } elseif ($name !== '' && str_contains($name, $token)) {
                    $score += 1.2;
                }
                if ($lowerCode !== '' && $token === $lowerCode) {
                    $score += 3;
                }
            }

            if ($score > 0) {
                $candidates[] = [
                    'id' => (int) $activity->id,
                    'code' => $code,
                    'name' => (string) $activity->name,
                    'score' => round($score, 2),
                ];
            }
        }

        usort($candidates, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($candidates, 0, $limit);
    }

    /**
     * Propose des mots-clés existants et des concepts du thésaurus pour un texte.
     */
    public function suggestIndexing(string $text, int $limit = 10): array
    {
        $terms = $this->parseList($text);
        if (empty($terms)) {
            $terms = array_slice(array_filter(
                preg_split('/[^\p{L}\p{N}\-]+/u', $text) ?: [],
                fn ($token) => mb_strlen(trim($token)) >= 3
            ), 0, 15);
        }

        if (empty($terms)) {
            return ['keywords' => [], 'concepts' => []];
        }

        $keywords = Keyword::query()
            ->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->orWhere('name', 'LIKE', "%{$term}%");
                }
            })
            ->limit($limit)
            ->get(['id', 'name'])
            ->map(fn (Keyword $keyword) => ['id' => $keyword->id, 'name' => $keyword->name])
            ->all();

        $concepts = ThesaurusConcept::query()
            ->with(['labels' => function ($q) use ($terms) {
                $q->where(function ($sub) use ($terms) {
                    foreach ($terms as $term) {
                        $sub->orWhere('literal_form', 'LIKE', "%{$term}%");
                    }
                });
            }])
            ->whereHas('labels', function ($q) use ($terms) {
                $q->where(function ($sub) use ($terms) {
                    foreach ($terms as $term) {
                        $sub->orWhere('literal_form', 'LIKE', "%{$term}%");
                    }
                });
            })
            ->limit($limit)
            ->get()
            ->map(function (ThesaurusConcept $concept) {
                return [
                    'id' => $concept->id,
                    'preferred_label' => $concept->preferred_label,
                    'matched_labels' => $concept->labels->pluck('literal_form')->unique()->values()->all(),
                ];
            })
            ->all();

        return ['keywords' => $keywords, 'concepts' => $concepts];
    }

    private function parseList(string $text): array
    {
        $parts = preg_split('/[\n,;\t\x{2022}\*]+/u', str_replace("\r", '', $text)) ?: [];
        $out = [];
        $seen = [];
        foreach ($parts as $part) {
            $term = trim((string) $part);
            if ($term === '' || isset($seen[mb_strtolower($term)])) {
                continue;
            }
            $seen[mb_strtolower($term)] = true;
            $out[] = $term;
            if (count($out) >= 30) {
                break;
            }
        }

        return $out;
    }
}
