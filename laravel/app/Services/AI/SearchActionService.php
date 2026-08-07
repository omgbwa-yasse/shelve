<?php

namespace App\Services\AI;

use App\Models\RecordPhysical;
use App\Models\Mail;
use App\Models\Communication;
use App\Models\Slip;
use Illuminate\Support\Facades\Log;

class SearchActionService
{
    // Liste des actions possibles par type d'élément
    private const ACTIONS = [
        'COUNT_TOTAL' => 'Compter tous les éléments',
        'COUNT_FILTERED' => 'Compter avec critères',
        'SEARCH_SIMPLE' => 'Recherche simple par mots-clés',
        'SEARCH_BY_AUTHOR' => 'Recherche par auteur',
        'SEARCH_BY_DATE' => 'Recherche par date',
        'SEARCH_BY_CODE' => 'Recherche par code',
        'GET_DIRECT_LINK' => 'Obtenir lien direct',
        'LIST_RECENT' => 'Lister les plus récents',
        'GREET' => 'Saluer l\'utilisateur',
        'EXPLAIN_CAPABILITIES' => 'Expliquer les capacités',
    ];

    // Configuration spécifique par type d'élément
    private const TYPE_CONFIG = [
        'records' => [
            'model' => RecordPhysical::class,
            'route_prefix' => 'records',
            'name_fr' => 'documents',
            'name_single' => 'document',
            'icon' => 'bi-folder',
            'search_fields' => ['name', 'code', 'content', 'note', 'archivist_note'],
            'relations' => ['authors', 'activity', 'keywords', 'containers']
        ],
        'mails' => [
            'model' => Mail::class,
            'route_prefix' => 'mails',
            'name_fr' => 'mails',
            'name_single' => 'mail',
            'icon' => 'bi-envelope',
            'search_fields' => ['name', 'code', 'description', 'object', 'expeditor', 'recipient'],
            'relations' => ['author', 'typology', 'priority', 'container']
        ],
        'communications' => [
            'model' => Communication::class,
            'route_prefix' => 'communications',
            'name_fr' => 'communications',
            'name_single' => 'communication',
            'icon' => 'bi-chat-dots',
            'search_fields' => ['name', 'code', 'content', 'description'],
            'relations' => ['records', 'user']
        ],
        'slips' => [
            'model' => Slip::class,
            'route_prefix' => 'slips',
            'name_fr' => 'transferts',
            'name_single' => 'transfert',
            'icon' => 'bi-arrow-left-right',
            'search_fields' => ['name', 'code', 'description', 'note'],
            'relations' => ['officer', 'user', 'records', 'status']
        ]
    ];

    public function getTypeConfig(string $type): array
    {
        return self::TYPE_CONFIG[$type] ?? [];
    }

    public function getAvailableActions(): array
    {
        return self::ACTIONS;
    }

    // ============ FONCTIONS DE COMPTAGE ============

    public function countTotal(string $type): int
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return 0;

        return $config['model']::count();
    }

    public function countFiltered(string $type, array $filters): int
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return 0;

        $query = $config['model']::query();

        // Appliquer les filtres selon le type
        if (!empty($filters['keywords'])) {
            $query = $this->applyKeywordFilters($query, $filters['keywords'], $config);
        }

        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $query = $this->applyDateFilters($query, $filters, $type);
        }

        if (!empty($filters['author'])) {
            $query = $this->applyAuthorFilters($query, $filters['author'], $config);
        }

        return $query->count();
    }

    public function countByDateRange(string $type, string $startDate, string $endDate): int
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return 0;

        $dateField = $this->getDateFieldForType($type);

        return $config['model']::whereBetween($dateField, [$startDate, $endDate])->count();
    }

    public function countByAuthor(string $type, string $authorName): int
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return 0;

        $query = $config['model']::query();
        return $this->applyAuthorFilters($query, $authorName, $config)->count();
    }

    // ============ FONCTIONS DE RECHERCHE ============

    public function searchByKeywords(string $type, array $keywords, int $limit = 5): array
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return [];

        $query = $config['model']::query();
        $query = $this->applyKeywordFilters($query, $keywords, $config);

        $results = $query->limit($limit)->get();

        return $this->formatResults($results, $config);
    }

    public function searchByExactCode(string $type, string $code): array
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return [];

        $result = $config['model']::where('code', $code)->first();

        if (!$result) return [];

        return $this->formatResults(collect([$result]), $config);
    }

    public function findByAuthor(string $type, string $authorName, int $limit = 5): array
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return [];

        $query = $config['model']::query();
        $query = $this->applyAuthorFilters($query, $authorName, $config);

        $results = $query->limit($limit)->get();

        return $this->formatResults($results, $config);
    }

    public function findRecent(string $type, int $limit = 10): array
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return [];

        $results = $config['model']::latest()->limit($limit)->get();

        return $this->formatResults($results, $config);
    }

    public function findByDateRange(string $type, string $startDate, string $endDate, int $limit = 10): array
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return [];

        $dateField = $this->getDateFieldForType($type);
        $results = $config['model']::whereBetween($dateField, [$startDate, $endDate])
                                   ->limit($limit)
                                   ->get();

        return $this->formatResults($results, $config);
    }

    // ============ FONCTIONS DE LIENS ET FORMATAGE ============

    public function generateDirectLink(string $type, int $id): string
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return '#';

        // Utiliser la même logique que generateCorrectUrl
        switch ($type) {
            case 'records':
                return route('records.show', $id);
            case 'mails':
                return route('mails.incoming.show', $id);
            case 'communications':
                return route('communications.transactions.show', $id);
            case 'slips':
                return route('slips.show', $id);
            default:
                return '#';
        }
    }

    public function formatResults($results, array $config): array
    {
        if ($results->isEmpty()) return [];

        return $results->map(function ($item) use ($config) {
            return [
                'id' => $item->id,
                'title' => $this->getItemTitle($item, $config),
                'url' => $this->generateCorrectUrl($item, $config),
                'icon' => $config['icon'],
                'description' => $this->getItemDescription($item, $config),
                'type' => $config['name_single']
            ];
        })->toArray();
    }

    /**
     * Génère l'URL correcte selon le type d'entité
     */
    private function generateCorrectUrl($item, array $config): string
    {
        switch ($config['route_prefix']) {
            case 'records':
                return route('records.show', $item->id);
            case 'mails':
                // Par défaut on utilise incoming, mais on pourrait améliorer avec une logique
                return route('mails.incoming.show', $item->id);
            case 'communications':
                // Les communications utilisent transactions.show
                return route('communications.transactions.show', $item->id);
            case 'slips':
                return route('slips.show', $item->id);
            default:
                return '#';
        }
    }

    // ============ FONCTIONS UTILITAIRES PRIVÉES ============

    private function applyKeywordFilters($query, array $keywords, array $config)
    {
        return $query->where(function ($q) use ($keywords, $config) {
            foreach ($keywords as $keyword) {
                // Recherche dans les champs principaux
                foreach ($config['search_fields'] as $field) {
                    $q->orWhere($field, 'LIKE', "%{$keyword}%");
                }

                // Recherche dans les relations selon le type
                $this->applyRelationFilters($q, $keyword, $config);
            }
        });
    }

    private function applyRelationFilters($query, string $keyword, array $config)
    {
        foreach ($config['relations'] as $relation) {
            switch ($relation) {
                case 'authors':
                case 'author':
                    $query->orWhereHas($relation, function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%{$keyword}%");
                    });
                    break;

                case 'activity':
                    $query->orWhereHas('activity', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%{$keyword}%")
                          ->orWhere('code', 'LIKE', "%{$keyword}%");
                    });
                    break;

                case 'keywords':
                    $query->orWhereHas('keywords', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%{$keyword}%");
                    });
                    break;

                case 'typology':
                    $query->orWhereHas('typology', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%{$keyword}%");
                    });
                    break;

                case 'officer':
                case 'user':
                    $query->orWhereHas($relation, function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%{$keyword}%");
                    });
                    break;
            }
        }
    }

    private function applyDateFilters($query, array $filters, string $type)
    {
        $dateField = $this->getDateFieldForType($type);

        if (!empty($filters['date_from'])) {
            $query->whereDate($dateField, '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate($dateField, '<=', $filters['date_to']);
        }

        return $query;
    }

    private function applyAuthorFilters($query, string $authorName, array $config)
    {
        // Adapter selon le type d'élément
        if (in_array('authors', $config['relations'])) {
            // Records: relation many-to-many
            return $query->whereHas('authors', function ($q) use ($authorName) {
                $q->where('name', 'LIKE', "%{$authorName}%");
            });
        } elseif (in_array('author', $config['relations'])) {
            // Mails: relation belongs-to
            return $query->whereHas('author', function ($q) use ($authorName) {
                $q->where('name', 'LIKE', "%{$authorName}%");
            });
        }

        return $query;
    }

    private function getDateFieldForType(string $type): string
    {
        return match($type) {
            'records' => 'date_start',
            'mails' => 'date',
            'communications' => 'created_at',
            'slips' => 'created_at',
            default => 'created_at'
        };
    }

    private function getItemTitle($item, array $config): string
    {
        $title = $item->name ?: $item->code ?: $item->object ?: '';

        if (empty($title)) {
            $title = ucfirst($config['name_single']) . ' sans titre';
        }

        return $title;
    }

    private function getItemDescription($item, array $config): string
    {
        $description = '';

        // Priorité selon le type d'élément
        if ($item->content) {
            $description = $item->content;
        } elseif ($item->description) {
            $description = $item->description;
        } elseif ($item->note) {
            $description = $item->note;
        }

        return $description ? substr($description, 0, 100) . '...' : '';
    }

    public function getStats(string $type): array
    {
        $config = $this->getTypeConfig($type);
        if (empty($config)) return [];

        $total = $this->countTotal($type);
        $recent = $this->findRecent($type, 3);

        return [
            'total' => $total,
            'type_name' => $config['name_fr'],
            'type_single' => $config['name_single'],
            'recent_examples' => $recent
        ];
    }
}