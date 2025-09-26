<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

class ActionMixerService
{
    private SearchActionService $searchService;
    private array $conversationHistory = [];

    public function __construct(SearchActionService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Analyse la réponse IA et exécute les actions appropriées
     */
    public function processAIResponse(string $aiResponse, string $userMessage, string $searchType): array
    {
        // Stocker le contexte de conversation
        $this->updateConversationHistory($userMessage, $searchType);

        // Extraire les actions identifiées par l'IA
        $actions = $this->extractActionsFromAIResponse($aiResponse);

        // Si aucune action spécifique, retourner la réponse IA brute
        if (empty($actions)) {
            return [
                'message' => $aiResponse,
                'links' => [],
                'actions_executed' => []
            ];
        }

        // Exécuter les actions et compiler les résultats
        $results = $this->executeActions($actions, $searchType);

        // Formater la réponse finale
        return $this->formatFinalResponse($aiResponse, $results, $actions);
    }

    /**
     * Extrait les actions depuis la réponse IA
     */
    private function extractActionsFromAIResponse(string $response): array
    {
        $actions = [];

        // Format: ACTION:type:params
        if (preg_match_all('/ACTION:([A-Z_]+):([^\\n]*)/m', $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $actionType = $match[1];
                $params = $this->parseActionParams($match[2]);

                $actions[] = [
                    'type' => $actionType,
                    'params' => $params
                ];
            }
        }

        return $actions;
    }

    /**
     * Parse les paramètres d'action
     */
    private function parseActionParams(string $paramString): array
    {
        $params = [];

        if (empty($paramString)) {
            return $params;
        }

        // Format: key1=value1,key2=value2
        $pairs = explode(',', $paramString);
        foreach ($pairs as $pair) {
            $parts = explode('=', $pair, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1], ' "\'');

                // Traiter les arrays
                if (strpos($value, '|') !== false) {
                    $params[$key] = explode('|', $value);
                } else {
                    $params[$key] = $value;
                }
            }
        }

        return $params;
    }

    /**
     * Exécute une liste d'actions
     */
    private function executeActions(array $actions, string $searchType): array
    {
        $allResults = [];

        foreach ($actions as $action) {
            try {
                $result = $this->executeAction($action['type'], $action['params'], $searchType);
                if ($result !== null) {
                    $allResults[$action['type']] = $result;
                }
            } catch (\Exception $e) {
                Log::error('Action execution failed', [
                    'action' => $action['type'],
                    'params' => $action['params'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $allResults;
    }

    /**
     * Exécute une action spécifique
     */
    private function executeAction(string $actionType, array $params, string $searchType)
    {
        switch ($actionType) {
            case 'COUNT_TOTAL':
                return $this->searchService->countTotal($searchType);

            case 'COUNT_FILTERED':
                $filters = $this->buildFilters($params);
                return $this->searchService->countFiltered($searchType, $filters);

            case 'SEARCH_SIMPLE':
                $keywords = $params['keywords'] ?? [];
                if (is_string($keywords)) {
                    $keywords = explode(' ', $keywords);
                }
                $limit = (int)($params['limit'] ?? 5);
                return $this->searchService->searchByKeywords($searchType, $keywords, $limit);

            case 'SEARCH_BY_AUTHOR':
                $author = $params['author'] ?? '';
                $limit = (int)($params['limit'] ?? 5);
                return $this->searchService->findByAuthor($searchType, $author, $limit);

            case 'SEARCH_BY_CODE':
                $code = $params['code'] ?? '';
                return $this->searchService->searchByExactCode($searchType, $code);

            case 'SEARCH_BY_DATE':
                $startDate = $params['start_date'] ?? '';
                $endDate = $params['end_date'] ?? '';
                $limit = (int)($params['limit'] ?? 10);
                return $this->searchService->findByDateRange($searchType, $startDate, $endDate, $limit);

            case 'LIST_RECENT':
                $limit = (int)($params['limit'] ?? 10);
                return $this->searchService->findRecent($searchType, $limit);

            case 'GET_DIRECT_LINK':
                $id = (int)($params['id'] ?? 0);
                if ($id > 0) {
                    return $this->searchService->generateDirectLink($searchType, $id);
                }
                // Si pas d'ID, chercher dans l'historique
                return $this->findLinkFromContext($searchType);

            case 'GET_STATS':
                return $this->searchService->getStats($searchType);

            case 'COUNT_BY_AUTHOR':
                $author = $params['author'] ?? '';
                return $this->searchService->countByAuthor($searchType, $author);

            case 'COUNT_BY_DATE':
                $startDate = $params['start_date'] ?? '';
                $endDate = $params['end_date'] ?? '';
                return $this->searchService->countByDateRange($searchType, $startDate, $endDate);

            default:
                Log::warning('Unknown action type', ['action' => $actionType]);
                return null;
        }
    }

    /**
     * Construit les filtres depuis les paramètres
     */
    private function buildFilters(array $params): array
    {
        $filters = [];

        if (!empty($params['keywords'])) {
            $filters['keywords'] = is_array($params['keywords'])
                ? $params['keywords']
                : explode(' ', $params['keywords']);
        }

        if (!empty($params['author'])) {
            $filters['author'] = $params['author'];
        }

        if (!empty($params['start_date'])) {
            $filters['date_from'] = $params['start_date'];
        }

        if (!empty($params['end_date'])) {
            $filters['date_to'] = $params['end_date'];
        }

        return $filters;
    }

    /**
     * Trouve un lien depuis le contexte de conversation
     */
    private function findLinkFromContext(string $searchType): ?string
    {
        // Chercher le dernier élément mentionné dans l'historique
        $lastQuery = $this->getLastSearchQuery();

        if (!$lastQuery) {
            return null;
        }

        // Effectuer une recherche rapide
        $results = $this->searchService->searchByKeywords($searchType, $lastQuery, 1);

        if (!empty($results[0]['url'])) {
            return $results[0]['url'];
        }

        return null;
    }

    /**
     * Formate la réponse finale
     */
    private function formatFinalResponse(string $aiResponse, array $results, array $actions): array
    {
        // Nettoyer la réponse des commandes ACTION
        $cleanResponse = preg_replace('/ACTION:[A-Z_]+:[^\\n]*\\n?/m', '', $aiResponse);
        $cleanResponse = trim($cleanResponse);

        // Compiler les liens depuis les résultats de recherche
        $links = [];
        foreach ($results as $actionType => $result) {
            if (is_array($result) && isset($result[0]['url'])) {
                // Résultats de recherche
                $links = array_merge($links, $result);
            } elseif (is_string($result) && filter_var($result, FILTER_VALIDATE_URL)) {
                // Lien direct
                $links[] = ['url' => $result, 'title' => 'Lien direct', 'type' => 'direct'];
            }
        }

        // Enrichir la réponse avec les données des résultats
        $enrichedResponse = $this->enrichResponseWithData($cleanResponse, $results);

        return [
            'message' => $enrichedResponse,
            'links' => $links,
            'actions_executed' => array_keys($results),
            'total_actions' => count($actions)
        ];
    }

    /**
     * Enrichit la réponse avec les données des résultats
     */
    private function enrichResponseWithData(string $response, array $results): string
    {
        foreach ($results as $actionType => $result) {
            switch ($actionType) {
                case 'COUNT_TOTAL':
                case 'COUNT_FILTERED':
                case 'COUNT_BY_AUTHOR':
                case 'COUNT_BY_DATE':
                    if (is_numeric($result)) {
                        $response = str_replace('{{COUNT}}', $result, $response);
                        $response = str_replace('{{count}}', $result, $response);
                    }
                    break;

                case 'GET_STATS':
                    if (is_array($result) && isset($result['total'])) {
                        $response = str_replace('{{TOTAL}}', $result['total'], $response);
                        $response = str_replace('{{total}}', $result['total'], $response);
                    }
                    break;
            }
        }

        return $response;
    }

    /**
     * Met à jour l'historique de conversation
     */
    private function updateConversationHistory(string $userMessage, string $searchType): void
    {
        $this->conversationHistory[] = [
            'message' => $userMessage,
            'type' => $searchType,
            'timestamp' => now()
        ];

        // Garder seulement les 5 derniers échanges
        if (count($this->conversationHistory) > 5) {
            $this->conversationHistory = array_slice($this->conversationHistory, -5);
        }
    }

    /**
     * Récupère la dernière requête de recherche
     */
    private function getLastSearchQuery(): ?array
    {
        if (empty($this->conversationHistory)) {
            return null;
        }

        $lastEntry = end($this->conversationHistory);

        // Extraire des mots-clés basiques du dernier message
        $message = strtolower($lastEntry['message']);
        $stopWords = ['le', 'la', 'les', 'de', 'du', 'des', 'un', 'une', 'et', 'ou', 'mais', 'dans', 'sur', 'avec', 'pour', 'trouve', 'cherche', 'document', 'mail'];

        $words = preg_split('/\s+/', $message);
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return !in_array($word, $stopWords) && strlen($word) > 2;
        });

        return array_values($keywords);
    }
}