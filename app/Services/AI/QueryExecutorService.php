<?php

namespace App\Services\AI;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class QueryExecutorService
{
    public function executeQuery(array $instructions): array
    {
        if (!$instructions['success']) {
            return $instructions;
        }

        $action = $instructions['action'];
        $table = $instructions['table'] ?? 'records';

        try {
            switch ($action) {
                case 'search':
                    return $this->executeSearch($instructions);

                case 'count':
                    return $this->executeCount($instructions);

                case 'filter':
                    return $this->executeFilter($instructions);

                case 'list':
                    return $this->executeList($instructions);

                case 'show':
                    return $this->executeShow($instructions);

                case 'date_range':
                    return $this->executeDateRange($instructions);

                case 'advanced':
                    return $this->executeAdvanced($instructions);

                default:
                    return [
                        'success' => false,
                        'error' => "Action non supportée: {$action}",
                        'data' => []
                    ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur exécution: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    private function executeSearch(array $instructions): array
    {
        $table = $instructions['table'] ?? 'records';
        $keywords = $instructions['keywords'] ?? [];
        $fields = $instructions['fields'] ?? ['name', 'description'];
        $limit = $instructions['limit'] ?? 10;

        $query = $this->getTableQuery($table);

        if (!empty($keywords)) {
            // Mapper les champs incorrects vers les bons champs
            $fields = $this->mapFieldNames($fields);

            $query->where(function ($q) use ($keywords, $fields) {
                foreach ($keywords as $keyword) {
                    foreach ($fields as $field) {
                        $q->orWhere($field, 'LIKE', "%{$keyword}%");
                    }
                }
            });
        }

        $results = $query->limit($limit)->get();

        return [
            'success' => true,
            'action' => 'search',
            'data' => json_decode(json_encode($results), true),
            'count' => $results->count(),
            'message' => "Trouvé {$results->count()} résultat(s) pour: " . implode(', ', $keywords)
        ];
    }

    private function executeCount(array $instructions): array
    {
        $table = $instructions['table'] ?? 'records';
        $filters = $instructions['filters'] ?? [];

        $query = $this->getTableQuery($table);
        $query = $this->applyFilters($query, $filters, $table);

        $count = $query->count();

        $message = "Il y a {$count} élément(s)";
        if (!empty($filters)) {
            $filterDesc = [];
            foreach ($filters as $key => $value) {
                $filterDesc[] = "{$key}: {$value}";
            }
            $message .= " avec les critères: " . implode(', ', $filterDesc);
        }

        return [
            'success' => true,
            'action' => 'count',
            'data' => ['count' => $count],
            'count' => $count,
            'message' => $message
        ];
    }

    private function executeFilter(array $instructions): array
    {
        $table = $instructions['table'] ?? 'records';
        $filters = $instructions['filters'] ?? [];
        $limit = $instructions['limit'] ?? 10;

        $query = $this->getTableQuery($table);
        $query = $this->applyFilters($query, $filters, $table);

        $results = $query->limit($limit)->get();

        return [
            'success' => true,
            'action' => 'filter',
            'data' => json_decode(json_encode($results), true),
            'count' => $results->count(),
            'message' => "Trouvé {$results->count()} résultat(s) avec les filtres appliqués"
        ];
    }

    private function executeList(array $instructions): array
    {
        $table = $instructions['table'] ?? 'records';
        $limit = $instructions['limit'] ?? 10;
        $order = $instructions['order'] ?? 'desc';

        $query = $this->getTableQuery($table);

        if ($order === 'desc') {
            $query->orderBy('records.created_at', 'desc');
        } else {
            $query->orderBy('records.created_at', 'asc');
        }

        $results = $query->limit($limit)->get();

        return [
            'success' => true,
            'action' => 'list',
            'data' => json_decode(json_encode($results), true),
            'count' => $results->count(),
            'message' => "Voici les {$results->count()} éléments les plus récents"
        ];
    }

    private function executeShow(array $instructions): array
    {
        $table = $instructions['table'] ?? 'records';
        $id = $instructions['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'error' => 'ID manquant pour afficher un élément',
                'data' => []
            ];
        }

        $query = $this->getTableQuery($table);
        $result = $query->where('records.id', $id)->first();

        if (!$result) {
            return [
                'success' => false,
                'error' => "Élément #{$id} non trouvé",
                'data' => []
            ];
        }

        return [
            'success' => true,
            'action' => 'show',
            'data' => json_decode(json_encode($result), true),
            'count' => 1,
            'message' => "Détails de l'élément #{$id}"
        ];
    }

    private function executeAdvanced(array $instructions): array
    {
        $table = $instructions['table'] ?? 'records';
        $filters = $instructions['filters'] ?? [];
        $keywords = $instructions['keywords'] ?? [];
        $fields = $instructions['fields'] ?? $this->getDefaultFields($table);
        $limit = $instructions['limit'] ?? 15;

        $query = $this->getTableQuery($table);

        // Appliquer les filtres
        $query = $this->applyFilters($query, $filters, $table);

        // Appliquer la recherche par mots-clés si présents
        if (!empty($keywords)) {
            $fields = $this->mapFieldNames($fields, $table);
            $query->where(function ($q) use ($keywords, $fields) {
                foreach ($keywords as $keyword) {
                    foreach ($fields as $field) {
                        $q->orWhere($field, 'LIKE', "%{$keyword}%");
                    }
                }
            });
        }

        $query->orderBy($this->getTablePrefix($table) . '.created_at', 'desc');
        $results = $query->limit($limit)->get();

        return [
            'success' => true,
            'action' => 'advanced',
            'data' => json_decode(json_encode($results), true),
            'count' => $results->count(),
            'message' => "Recherche avancée: {$results->count()} résultat(s) trouvé(s)"
        ];
    }

    private function getTableQuery(string $table): Builder
    {
        switch ($table) {
            case 'records':
                return DB::table('records')
                    ->leftJoin('activities', 'records.activity_id', '=', 'activities.id')
                    ->leftJoin('record_statuses', 'records.status_id', '=', 'record_statuses.id')
                    ->select(
                        'records.*',
                        'activities.name as activity_name',
                        'record_statuses.name as status_name'
                    );

            case 'mails':
                return DB::table('mails')
                    ->leftJoin('mail_priorities', 'mails.priority_id', '=', 'mail_priorities.id')
                    ->leftJoin('mail_types', 'mails.mail_type_id', '=', 'mail_types.id')
                    ->leftJoin('mail_typologies', 'mails.mail_typology_id', '=', 'mail_typologies.id')
                    ->select(
                        'mails.*',
                        'mail_priorities.name as priority_name',
                        'mail_types.name as mail_type_name',
                        'mail_typologies.name as typology_name'
                    );

            case 'communications':
                return DB::table('communications')
                    ->leftJoin('users as operators', 'communications.operator_id', '=', 'operators.id')
                    ->leftJoin('users as comm_users', 'communications.user_id', '=', 'comm_users.id')
                    ->select(
                        'communications.*',
                        'operators.name as operator_name',
                        'comm_users.name as user_name'
                    );

            case 'slips':
                return DB::table('slips')
                    ->leftJoin('slip_statuses', 'slips.status_id', '=', 'slip_statuses.id')
                    ->leftJoin('users as officers', 'slips.officer_id', '=', 'officers.id')
                    ->leftJoin('users as slip_users', 'slips.user_id', '=', 'slip_users.id')
                    ->select(
                        'slips.*',
                        'slip_statuses.name as status_name',
                        'officers.name as officer_name',
                        'slip_users.name as user_name'
                    );

            default:
                return DB::table($table);
        }
    }

    private function executeDateRange(array $instructions): array
    {
        $table = $instructions['table'] ?? 'records';
        $filters = $instructions['filters'] ?? [];
        $limit = $instructions['limit'] ?? 20;

        $query = $this->getTableQuery($table);
        $query = $this->applyFilters($query, $filters, $table);
        $query->orderBy('records.created_at', 'desc');

        $results = $query->limit($limit)->get();

        return [
            'success' => true,
            'action' => 'date_range',
            'data' => json_decode(json_encode($results), true),
            'count' => $results->count(),
            'message' => "Trouvé {$results->count()} résultat(s) dans la période spécifiée"
        ];
    }

    private function applyFilters(Builder $query, array $filters, string $table = 'records'): Builder
    {
        $tablePrefix = $this->getTablePrefix($table);

        foreach ($filters as $key => $value) {
            switch ($key) {
                // Filtres de dates communs
                case 'year':
                    $query->whereYear("{$tablePrefix}.created_at", $value);
                    break;

                case 'month':
                    $query->whereMonth("{$tablePrefix}.created_at", $value);
                    break;

                case 'date_from':
                    $query->whereDate("{$tablePrefix}.created_at", '>=', $value);
                    break;

                case 'date_to':
                    $query->whereDate("{$tablePrefix}.created_at", '<=', $value);
                    break;

                // Filtres spécifiques aux RECORDS
                case 'author':
                    if ($table === 'records') {
                        $query->whereExists(function ($q) use ($value) {
                            $q->select(DB::raw(1))
                              ->from('record_authors')
                              ->whereColumn('record_authors.record_id', 'records.id')
                              ->join('authors', 'record_authors.author_id', '=', 'authors.id')
                              ->where(function ($subQuery) use ($value) {
                                  $subQuery->where('authors.name', 'LIKE', "%{$value}%")
                                           ->orWhere('authors.first_name', 'LIKE', "%{$value}%")
                                           ->orWhere('authors.last_name', 'LIKE', "%{$value}%");
                              });
                        });
                    }
                    break;

                case 'activity':
                    if ($table === 'records') {
                        $query->where('activities.name', 'LIKE', "%{$value}%");
                    }
                    break;

                case 'term':
                    if ($table === 'records') {
                        $query->whereExists(function ($q) use ($value) {
                            $q->select(DB::raw(1))
                              ->from('record_terms')
                              ->whereColumn('record_terms.record_id', 'records.id')
                              ->join('terms', 'record_terms.term_id', '=', 'terms.id')
                              ->where('terms.name', 'LIKE', "%{$value}%");
                        });
                    }
                    break;

                case 'container':
                    if ($table === 'records') {
                        $query->whereExists(function ($q) use ($value) {
                            $q->select(DB::raw(1))
                              ->from('record_containers')
                              ->whereColumn('record_containers.record_id', 'records.id')
                              ->join('containers', 'record_containers.container_id', '=', 'containers.id')
                              ->where(function ($subQuery) use ($value) {
                                  $subQuery->where('containers.name', 'LIKE', "%{$value}%")
                                           ->orWhere('containers.code', 'LIKE', "%{$value}%");
                              });
                        });
                    } elseif (in_array($table, ['mails', 'slips'])) {
                        $query->where("{$tablePrefix}.container_id", $value);
                    }
                    break;

                // Filtres spécifiques aux MAILS
                case 'priority':
                    if ($table === 'mails') {
                        $query->where('mail_priorities.name', 'LIKE', "%{$value}%");
                    }
                    break;

                case 'mail_type':
                    if ($table === 'mails') {
                        $query->where('mail_types.name', 'LIKE', "%{$value}%");
                    }
                    break;

                case 'typology':
                    if ($table === 'mails') {
                        $query->where('mail_typologies.name', 'LIKE', "%{$value}%");
                    }
                    break;

                case 'attachment_content':
                    if ($table === 'mails') {
                        $query->whereExists(function ($q) use ($value) {
                            $q->select(DB::raw(1))
                              ->from('mail_attachments')
                              ->whereColumn('mail_attachments.mail_id', 'mails.id')
                              ->where('mail_attachments.content', 'LIKE', "%{$value}%");
                        });
                    }
                    break;

                // Filtres spécifiques aux COMMUNICATIONS
                case 'operator':
                    if ($table === 'communications') {
                        $query->where('operators.name', 'LIKE', "%{$value}%");
                    }
                    break;

                case 'user':
                    if (in_array($table, ['communications', 'slips'])) {
                        $userTable = $table === 'communications' ? 'comm_users' : 'slip_users';
                        $query->where("{$userTable}.name", 'LIKE', "%{$value}%");
                    }
                    break;

                case 'return_date':
                    if ($table === 'communications') {
                        $query->whereDate('communications.return_date', $value);
                    }
                    break;

                case 'return_effective':
                    if ($table === 'communications') {
                        $query->whereDate('communications.return_effective', $value);
                    }
                    break;

                // Filtres spécifiques aux SLIPS
                case 'slip_status':
                    if ($table === 'slips') {
                        $query->where('slip_statuses.name', 'LIKE', "%{$value}%");
                    }
                    break;

                case 'officer':
                    if ($table === 'slips') {
                        $query->where('officers.name', 'LIKE', "%{$value}%");
                    }
                    break;

                case 'received_date':
                    if (in_array($table, ['mails', 'slips'])) {
                        $query->whereDate("{$tablePrefix}.received_date", $value);
                    }
                    break;

                case 'approved_date':
                    if ($table === 'slips') {
                        $query->whereDate('slips.approved_date', $value);
                    }
                    break;

                case 'integrated_date':
                    if ($table === 'slips') {
                        $query->whereDate('slips.integrated_date', $value);
                    }
                    break;

                // Filtres génériques de statut
                case 'status':
                    if ($table === 'records') {
                        $query->where('record_statuses.name', 'LIKE', "%{$value}%");
                    } elseif ($table === 'communications') {
                        $query->where('communications.status', 'LIKE', "%{$value}%");
                    }
                    break;

                default:
                    // Filtre générique
                    if (strpos($key, '.') === false) {
                        $query->where("{$tablePrefix}.{$key}", $value);
                    } else {
                        $query->where($key, $value);
                    }
                    break;
            }
        }

        return $query;
    }

    private function mapFieldNames(array $fields, string $table = 'records'): array
    {
        $tablePrefix = $this->getTablePrefix($table);

        $fieldMappings = [
            'records' => [
                'title' => 'records.name',
                'name' => 'records.name',
                'description' => 'records.archivist_note',
                'content' => 'records.archivist_note',
                'archivist_note' => 'records.archivist_note',
                'code' => 'records.code',
            ],
            'mails' => [
                'title' => 'mails.name',
                'name' => 'mails.name',
                'subject' => 'mails.name',
                'objet' => 'mails.name',
                'content' => 'mails.content',
                'code' => 'mails.code',
            ],
            'communications' => [
                'name' => 'communications.name',
                'content' => 'communications.content',
                'code' => 'communications.code',
            ],
            'slips' => [
                'name' => 'slips.name',
                'description' => 'slips.description',
                'code' => 'slips.code',
            ]
        ];

        $mapping = $fieldMappings[$table] ?? [];

        return array_map(function($field) use ($mapping, $tablePrefix) {
            return $mapping[$field] ?? "{$tablePrefix}.{$field}";
        }, $fields);
    }

    private function getTablePrefix(string $table): string
    {
        return $table;
    }

    private function getDefaultFields(string $table): array
    {
        $defaultFields = [
            'records' => ['name', 'archivist_note', 'content'],
            'mails' => ['name', 'content'],
            'communications' => ['name', 'content'],
            'slips' => ['name', 'description']
        ];

        return $defaultFields[$table] ?? ['name'];
    }
}