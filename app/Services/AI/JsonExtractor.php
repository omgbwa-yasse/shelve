<?php

namespace App\Services\AI;

/**
 * Extraction robuste d'un objet JSON depuis une réponse de modèle IA en texte
 * libre : retire les balises markdown, puis isole le premier objet complet
 * par équilibrage d'accolades (tolère du texte parasite avant/après, ou
 * plusieurs objets à la suite).
 */
class JsonExtractor
{
    public static function firstJsonObject(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);

        $decoded = json_decode($clean, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return self::extractBalanced($clean);
    }

    private static function extractBalanced(string $text): ?array
    {
        $start = strpos($text, '{');
        if ($start === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $escaped = false;

        for ($i = $start, $length = strlen($text); $i < $length; $i++) {
            $char = $text[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === '"') {
                    $inString = false;
                }
                continue;
            }

            if ($char === '"') {
                $inString = true;
            } elseif ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    $decoded = json_decode(substr($text, $start, $i - $start + 1), true);

                    return is_array($decoded) ? $decoded : null;
                }
            }
        }

        return null;
    }
}
