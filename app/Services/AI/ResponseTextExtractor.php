<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

class ResponseTextExtractor
{
    /**
     * Extrait le texte d'une réponse AI quelle que soit son format
     * Gère les formats de Claude, Ollama, OpenAI, etc.
     */
    public static function extract($response): string
    {
        try {
            // Si la réponse est déjà une string, on la retourne directement
            if (is_string($response)) {
                return $response;
            }

            // Conversion en array si c'est un objet
            if (is_object($response)) {
                $response = (array) $response;
            }

            // Si ce n'est pas un array maintenant, on fait de notre mieux
            if (!is_array($response)) {
                return (string) $response;
            }

            // Format Claude: content peut être un array d'objets avec propriété 'text'
            if (!empty($response['content'])) {
                $content = $response['content'];
                
                // Si content est un array d'objets
                if (is_array($content)) {
                    $texts = [];
                    foreach ($content as $item) {
                        if (is_array($item) && isset($item['text'])) {
                            $texts[] = $item['text'];
                        } elseif (is_string($item)) {
                            $texts[] = $item;
                        } elseif (is_object($item) && isset($item->text)) {
                            $texts[] = $item->text;
                        }
                    }
                    if (!empty($texts)) {
                        return implode('', $texts);
                    }
                }
                
                // Si content est une string
                if (is_string($content)) {
                    return $content;
                }
                
                // Si content est un objet avec une propriété text
                if (is_object($content) && isset($content->text)) {
                    return (string) $content->text;
                }
            }

            // Format standard message/content (OpenAI, etc.)
            if (!empty($response['message']['content'])) {
                return (string) $response['message']['content'];
            }

            // Format choices (OpenAI API format)
            if (!empty($response['choices'][0]['message']['content'])) {
                return (string) $response['choices'][0]['message']['content'];
            }

            // Format avec text direct
            if (!empty($response['text'])) {
                return (string) $response['text'];
            }

            // Format delta pour streaming
            if (!empty($response['delta']['content'])) {
                return (string) $response['delta']['content'];
            }

            // Si aucun format reconnu, log pour debug et retourne JSON
            Log::debug('ResponseTextExtractor: Format de réponse non reconnu', [
                'response_keys' => array_keys($response),
                'response_sample' => array_slice($response, 0, 3, true)
            ]);

            return json_encode($response);

        } catch (\Throwable $e) {
            Log::error('ResponseTextExtractor: Erreur lors de l\'extraction', [
                'error' => $e->getMessage(),
                'response_type' => gettype($response)
            ]);

            // Fallback: essayer de convertir en string
            return is_string($response) ? $response : json_encode($response);
        }
    }
}
