<?php

namespace App\Services\AI;

class ResponseFormatterService
{
    public function formatResponse(array $executionResult, string $searchType = 'records'): array
    {
        if (!$executionResult['success']) {
            return [
                'success' => false,
                'response' => $executionResult['error'] ?? 'Une erreur est survenue',
                'data' => []
            ];
        }

        $action = $executionResult['action'];
        $data = $executionResult['data'] ?? [];
        $count = $executionResult['count'] ?? 0;

        switch ($action) {
            case 'search':
                return $this->formatSearchResponse($data, $count, $searchType);

            case 'count':
                return $this->formatCountResponse($data, $executionResult['message'] ?? '');

            case 'filter':
            case 'list':
                return $this->formatListResponse($data, $count, $searchType, $action);

            case 'show':
                return $this->formatShowResponse($data, $searchType);

            default:
                return [
                    'success' => true,
                    'response' => $executionResult['message'] ?? 'Opération réussie',
                    'data' => $data
                ];
        }
    }

    private function formatSearchResponse(array $data, int $count, string $searchType): array
    {
        if ($count === 0) {
            return [
                'success' => true,
                'response' => "Aucun résultat trouvé pour votre recherche.",
                'results' => [],
                'data' => []
            ];
        }

        $response = "J'ai trouvé {$count} résultat(s) :";
        $results = [];

        foreach ($data as $item) {
            $title = $this->getItemTitle($item);
            $id = $item['id'] ?? 0;
            $url = $this->generateUrl($searchType, $id);
            $extraInfo = $this->getExtraInfo($item, $searchType);

            $results[] = [
                'title' => $title,
                'url' => $url,
                'description' => $extraInfo,
                'id' => $id,
                'type' => $searchType
            ];
        }

        return [
            'success' => true,
            'response' => $response,
            'results' => $results,
            'data' => $data
        ];
    }

    private function formatCountResponse(array $data, string $message): array
    {
        $count = $data['count'] ?? 0;

        return [
            'success' => true,
            'response' => $message ?: "Nombre total : {$count}",
            'data' => $data
        ];
    }

    private function formatListResponse(array $data, int $count, string $searchType, string $action): array
    {
        if ($count === 0) {
            return [
                'success' => true,
                'response' => "Aucun élément trouvé.",
                'results' => [],
                'data' => []
            ];
        }

        $actionText = match($action) {
            'list' => 'derniers éléments',
            'filter' => 'résultats filtrés',
            'date_range' => 'éléments dans la période',
            default => 'résultats'
        };

        $response = "Voici les {$count} {$actionText} :";
        $results = [];

        foreach ($data as $item) {
            $title = $this->getItemTitle($item);
            $id = $item['id'] ?? 0;
            $url = $this->generateUrl($searchType, $id);
            $extraInfo = $this->getExtraInfo($item, $searchType);

            $results[] = [
                'title' => $title,
                'url' => $url,
                'description' => $extraInfo,
                'id' => $id,
                'type' => $searchType
            ];
        }

        return [
            'success' => true,
            'response' => $response,
            'results' => $results,
            'data' => $data
        ];
    }

    private function formatShowResponse(array $data, string $searchType): array
    {
        $title = $this->getItemTitle($data);
        $id = $data['id'] ?? 0;
        $url = $this->generateUrl($searchType, $id);

        $response = "**[{$title}]({$url})**\n\n";

        // Ajouter les détails selon le type
        if ($searchType === 'records') {
            if (!empty($data['description'])) {
                $response .= "**Description :** " . $data['description'] . "\n\n";
            }
            if (!empty($data['author_name'])) {
                $response .= "**Auteur :** " . $data['author_name'] . "\n";
            }
            if (!empty($data['activity_name'])) {
                $response .= "**Activité :** " . $data['activity_name'] . "\n";
            }
            if (!empty($data['status_name'])) {
                $response .= "**Statut :** " . $data['status_name'] . "\n";
            }
        }

        // Date de création
        if (!empty($data['created_at'])) {
            $response .= "**Créé le :** " . date('d/m/Y', strtotime($data['created_at'])) . "\n";
        }

        return [
            'success' => true,
            'response' => trim($response),
            'data' => $data
        ];
    }

    private function getItemTitle(array $item): string
    {
        // Essayer plusieurs champs pour le titre
        $titleFields = ['name', 'title', 'subject', 'reference'];

        foreach ($titleFields as $field) {
            if (!empty($item[$field])) {
                return $item[$field];
            }
        }

        // Fallback sur l'ID
        return 'Élément #' . ($item['id'] ?? 'N/A');
    }

    private function getExtraInfo(array $item, string $searchType): string
    {
        $info = [];

        if ($searchType === 'records') {
            if (!empty($item['reference'])) {
                $info[] = $item['reference'];
            }
            if (!empty($item['author_name'])) {
                $info[] = $item['author_name'];
            }
        }

        // Date si disponible
        if (!empty($item['created_at'])) {
            $info[] = date('d/m/Y', strtotime($item['created_at']));
        }

        return implode(' • ', $info);
    }

    private function generateUrl(string $searchType, int $id): string
    {
        $routePatterns = [
            'records' => '/repositories/records/',
            'mails' => '/mails/incoming/',
            'communications' => '/communications/transactions/',
            'slips' => '/transferrings/slips/'
        ];

        $pattern = $routePatterns[$searchType] ?? "/{$searchType}/";
        return url($pattern . $id);
    }
}