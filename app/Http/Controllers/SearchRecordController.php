<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecordPhysical;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use App\Models\Activity;
use App\Models\Author;
use App\Models\Building;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Floor;
use App\Models\Container;
use App\Models\RecordStatus;

use App\Models\SlipStatus;
use App\Models\Organisation;
use App\Models\RecordSupport;
use App\Models\User;
use App\Models\RecordLevel;
use App\Models\ThesaurusConcept;
use App\Models\Keyword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchRecordController extends Controller
{
    private const OP_STARTS_WITH = 'commence par';
    private const OP_CONTAINS = 'contient';
    private const OP_NOT_CONTAINS = 'ne contient pas';

    public function form()
    {
        $data = [
            'rooms' => Room::select('id', 'name', 'code')->get(),
            'shelve' => Shelf::select('id', 'code')->get(),
            'activities' => Activity::select('id', 'name')->get(),
            'terms' => ThesaurusConcept::with('labels')->get()->map(function($concept) {
                return [
                    'id' => $concept->id,
                    'name' => $concept->preferred_label
                ];
            }),
            'authors' => Author::select('id', 'name')->get(),
            'creators' => User::select('id', 'name')->get(),
            'statues' => RecordStatus::select('id', 'name')->get(),
            'containers' => Container::select('id', 'code')->get(),
            'keywords' => Keyword::select('id', 'name')->orderBy('name')->get(),
            'folderTypes' => \App\Models\RecordDigitalFolderType::select('id', 'name', 'code')->orderBy('name')->get(),
            'documentTypes' => \App\Models\RecordDigitalDocumentType::select('id', 'name', 'code')->orderBy('name')->get(),
        ];

        return view('search.record.advanced', compact('data'));
    }

    public function advanced(Request $request)
    {
        $fields = $request->input('field');
        $operators = $request->input('operator');
        $values = $request->input('value');

        // Recherche dans RecordPhysical
        $queryPhysical = RecordPhysical::query();

        // Recherche dans RecordDigitalFolder
        $queryFolders = RecordDigitalFolder::query();

        // Recherche dans RecordDigitalDocument
        $queryDocuments = RecordDigitalDocument::query();

        if ($fields && $operators && $values) {
            foreach ($fields as $index => $field) {
                $operator = $operators[$index];
                $value = $values[$index];

                switch ($field) {
                    case 'code':
                    case 'name':
                    case 'content':
                    case 'attachment':
                    case 'attachment_content':
                        $this->applyTextSearch($queryPhysical, $field, $operator, $value);
                        $this->applyTextSearchDigital($queryFolders, $field, $operator, $value);
                        $this->applyTextSearchDigital($queryDocuments, $field, $operator, $value);
                        break;

                    case 'date_start':
                    case 'date_end':
                    case 'date_exact':
                    case 'date_creation':
                    case 'dua':
                    case 'dul':
                        $this->applyDateSearch($queryPhysical, $field, $operator, $value);
                        $this->applyDateSearchDigital($queryFolders, $field, $operator, $value);
                        $this->applyDateSearchDigital($queryDocuments, $field, $operator, $value);
                        break;

                    case 'room':
                    case 'shelf':
                    case 'activity':
                    case 'term':
                    case 'author':
                    case 'creator':
                    case 'container':
                    case 'status':
                    case 'keyword':
                        $this->applyRelationSearch($queryPhysical, $field, $operator, $value);
                        // Les dossiers et documents numériques n'ont pas de localisation physique
                        if (!in_array($field, ['room', 'shelf', 'container'])) {
                            $this->applyRelationSearchDigital($queryFolders, $field, $operator, $value);
                            $this->applyRelationSearchDigital($queryDocuments, $field, $operator, $value);
                        }
                        break;

                    default:
                        $queryPhysical->where($field, '=', $value);
                        break;
                }
            }
        }

        // Récupération des résultats des 3 types
        $physicalRecords = $queryPhysical->with([
            'status',
            'support',
            'level',
            'activity',
            'containers',
            'user',
            'authors',
            'thesaurusConcepts'
        ])->get()->map(function($record) {
            $record->record_type = 'physical';
            $record->type_label = 'Dossier Physique';
            return $record;
        });

        $folders = $queryFolders->with([
            'parent',
            'children',
            'documents',
            'creator'
        ])->get()->map(function($folder) {
            $folder->record_type = 'folder';
            $folder->type_label = 'Dossier Numérique';
            return $folder;
        });

        $documents = $queryDocuments->with([
            'folder',
            'type',
            'attachment',
            'creator'
        ])->get()->map(function($document) {
            $document->record_type = 'document';
            $document->type_label = 'Document Numérique';
            return $document;
        });

        // Fusion et tri des résultats
        $allResults = $physicalRecords->concat($folders)->concat($documents);

        // Tri par date de création décroissante
        $allResults = $allResults->sortByDesc('created_at');

        // Pagination manuelle
        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $allResults->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $records = new LengthAwarePaginator(
            $currentItems,
            $allResults->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        // Données additionnelles pour la vue
        $viewData = [
            'records' => $records,
            'statuses' => RecordStatus::all(),
            'slipStatuses' => SlipStatus::all(),
            'terms' => ThesaurusConcept::with('labels')->get(),
            'users' => User::select('id', 'name')->get(),
            'organisations' => Organisation::select('id', 'name')->get(),
        ];

        return view('records.index', $viewData);
    }

    private function applyTextSearch($query, $field, $operator, $value)
    {
        // Special handling for searching inside attachments content
        if ($field === 'attachment' || $field === 'attachment_content') {
            if ($operator === self::OP_STARTS_WITH) {
                $query->whereHas('attachments', function ($q) use ($value) {
                    $q->where('attachments.content_text', 'like', $value . '%');
                });
            } elseif ($operator === self::OP_CONTAINS) {
                $query->whereHas('attachments', function ($q) use ($value) {
                    $q->where('attachments.content_text', 'like', '%' . $value . '%');
                });
            } elseif ($operator === self::OP_NOT_CONTAINS) {
                $query->whereDoesntHave('attachments', function ($q) use ($value) {
                    $q->where('attachments.content_text', 'like', '%' . $value . '%');
                });
            }
            return;
        }

        // Combine record content + attachments content when field is 'content'
        if ($field === 'content') {
            if ($operator === self::OP_STARTS_WITH) {
                $query->where(function ($q) use ($value) {
                    $q->where('content', 'like', $value . '%')
                      ->orWhereHas('attachments', function ($qa) use ($value) {
                          $qa->where('attachments.content_text', 'like', $value . '%');
                      });
                });
            } elseif ($operator === self::OP_CONTAINS) {
                $query->where(function ($q) use ($value) {
                    $q->where('content', 'like', '%' . $value . '%')
                      ->orWhereHas('attachments', function ($qa) use ($value) {
                          $qa->where('attachments.content_text', 'like', '%' . $value . '%');
                      });
                });
            } elseif ($operator === self::OP_NOT_CONTAINS) {
                $query->where(function ($q) use ($value) {
                    $q->where('content', 'not like', '%' . $value . '%')
                      ->whereDoesntHave('attachments', function ($qa) use ($value) {
                          $qa->where('attachments.content_text', 'like', '%' . $value . '%');
                      });
                });
            }
            return;
        }

        // Default text search on the given field
        switch ($operator) {
            case self::OP_STARTS_WITH:
                $query->where($field, 'like', $value . '%');
                break;
            case self::OP_CONTAINS:
                $query->where($field, 'like', '%' . $value . '%');
                break;
            case self::OP_NOT_CONTAINS:
                $query->where($field, 'not like', '%' . $value . '%');
                break;
            default:
                break;
        }
    }

    /**
     * Recherche textuelle pour les dossiers et documents numériques
     */
    private function applyTextSearchDigital($query, $field, $operator, $value)
    {
        // Pour les dossiers/documents numériques, on cherche dans name et description
        $searchFields = ['name', 'description'];

        if ($field === 'code') {
            $searchFields = ['code'];
        } elseif ($field === 'name') {
            $searchFields = ['name'];
        } elseif ($field === 'content' || $field === 'attachment' || $field === 'attachment_content') {
            $searchFields = ['description'];
        }

        switch ($operator) {
            case self::OP_STARTS_WITH:
                $query->where(function($q) use ($searchFields, $value) {
                    foreach ($searchFields as $searchField) {
                        $q->orWhere($searchField, 'like', $value . '%');
                    }
                });
                break;
            case self::OP_CONTAINS:
                $query->where(function($q) use ($searchFields, $value) {
                    foreach ($searchFields as $searchField) {
                        $q->orWhere($searchField, 'like', '%' . $value . '%');
                    }
                });
                break;
            case self::OP_NOT_CONTAINS:
                $query->where(function($q) use ($searchFields, $value) {
                    foreach ($searchFields as $searchField) {
                        $q->where($searchField, 'not like', '%' . $value . '%');
                    }
                });
                break;
            default:
                break;
        }
    }

    private function applyDateSearch($query, $field, $operator, $value)
    {
        switch ($operator) {
            case '=':
                $query->whereDate($field, '=', $value);
                break;
            case '>':
                $query->whereDate($field, '>', $value);
                break;
            case '<':
                $query->whereDate($field, '<', $value);
                break;
            default:
                break;
        }
    }

    /**
     * Recherche par dates pour les dossiers et documents numériques
     */
    private function applyDateSearchDigital($query, $field, $operator, $value)
    {
        // Pour les dossiers/documents numériques, on utilise created_at
        $dateField = 'created_at';

        if (in_array($field, ['date_creation', 'created_at'])) {
            $dateField = 'created_at';
        } elseif ($field === 'date_start') {
            $dateField = 'created_at'; // Pas de date_start dans les modèles numériques
        }

        switch ($operator) {
            case '=':
                $query->whereDate($dateField, '=', $value);
                break;
            case '>':
                $query->whereDate($dateField, '>', $value);
                break;
            case '<':
                $query->whereDate($dateField, '<', $value);
                break;
            default:
                break;
        }
    }

    private function applyRelationSearch($query, $field, $operator, $value)
    {
        switch ($field) {
            case 'room':
                if ($operator === 'avec') {
                    $query->whereHas('containers.shelf.room', function ($q) use ($value) {
                        $q->where('rooms.id', $value);
                    });
                } else {
                    $query->whereDoesntHave('containers.shelf.room', function ($q) use ($value) {
                        $q->where('rooms.id', $value);
                    });
                }
                break;

            case 'shelf':
                if ($operator === 'avec') {
                    $query->whereHas('containers.shelf', function ($q) use ($value) {
                        $q->where('shelves.id', $value);
                    });
                } else {
                    $query->whereDoesntHave('containers.shelf', function ($q) use ($value) {
                        $q->where('shelves.id', $value);
                    });
                }
                break;

            case 'container':
                if ($operator === 'avec') {
                    $query->whereHas('containers', function ($q) use ($value) {
                        $q->where('containers.id', $value);
                    });
                } else {
                    $query->whereDoesntHave('containers', function ($q) use ($value) {
                        $q->where('containers.id', $value);
                    });
                }
                break;

            case 'creator':
                $query->where('user_id', $operator === 'avec' ? '=' : '!=', $value);
                break;

            default:
                $relation = $this->getRelationName($field);
                if ($operator === 'avec') {
                    $query->whereHas($relation, function ($q) use ($value) {
                        $q->where('id', $value);
                    });
                } else {
                    $query->whereDoesntHave($relation, function ($q) use ($value) {
                        $q->where('id', $value);
                    });
                }
                break;
        }
    }

    /**
     * Recherche par relations pour les dossiers et documents numériques
     */
    private function applyRelationSearchDigital($query, $field, $operator, $value)
    {
        switch ($field) {
            case 'creator':
                $query->where('creator_id', $operator === 'avec' ? '=' : '!=', $value);
                break;

            case 'activity':
            case 'author':
            case 'term':
            case 'status':
            case 'keyword':
                // Les dossiers/documents numériques n'ont pas ces relations pour l'instant
                // On peut les ignorer ou les implémenter plus tard
                break;

            default:
                break;
        }
    }

    private function getRelationName($field)
    {
        $relationMap = [
            'activity' => 'activity',
            'term' => 'thesaurusConcepts',
            'author' => 'authors',
            'creator' => 'user',
            'status' => 'status',
            'keyword' => 'keywords'
        ];

        return $relationMap[$field] ?? $field;
    }

    public function sort(Request $request)
    {
        $queryPhysical = RecordPhysical::query();
        $queryFolders = RecordDigitalFolder::query();
        $queryDocuments = RecordDigitalDocument::query();

        switch ($request->input('categ')) {
            case "dates":
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');

                if ($exactDate) {
                    $queryPhysical->whereDate('date_exact', $exactDate);
                }

                if ($startDate && $endDate) {
                    $queryPhysical->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->whereDate('date_start', '>=', $startDate)
                            ->whereDate('date_end', '<=', $endDate);
                    });

                    // Pour les dossiers/documents numériques, on filtre par created_at
                    $queryFolders->whereBetween('created_at', [$startDate, $endDate]);
                    $queryDocuments->whereBetween('created_at', [$startDate, $endDate]);
                }
                break;

            case "term":
            case "concept":
                $conceptId = $request->input('id');
                $queryPhysical->whereHas('thesaurusConcepts', function ($q) use ($conceptId) {
                    $q->where('thesaurus_concepts.id', $conceptId);
                });
                break;

            case "author":
                $authorId = $request->input('id');
                $queryPhysical->whereHas('authors', function ($q) use ($authorId) {
                    $q->where('authors.id', $authorId);
                });
                break;

            case "activity":
                $activityId = $request->input('id');
                $queryPhysical->where('activity_id', $activityId);
                break;

            case "container":
                $containerId = $request->input('id');
                $queryPhysical->whereHas('containers', function ($q) use ($containerId) {
                    $q->where('containers.id', $containerId);
                });
                break;

            case "keyword":
                $keywordId = $request->input('id');
                $queryPhysical->whereHas('keywords', function ($q) use ($keywordId) {
                    $q->where('keywords.id', $keywordId);
                });
                break;
            default:
                break;
        }

        $queryPhysical->with([
            'level',
            'status',
            'support',
            'activity',
            'containers',
            'user',
            'authors',
            'thesaurusConcepts',
            'keywords'
        ]);

        // Récupération des résultats
        $physicalRecords = $queryPhysical->get()->map(function($record) {
            $record->record_type = 'physical';
            $record->type_label = 'Dossier Physique';
            return $record;
        });

        $folders = $queryFolders->with(['parent', 'children', 'creator'])->get()->map(function($folder) {
            $folder->record_type = 'folder';
            $folder->type_label = 'Dossier Numérique';
            return $folder;
        });

        $documents = $queryDocuments->with(['folder', 'type', 'attachment', 'creator'])->get()->map(function($document) {
            $document->record_type = 'document';
            $document->type_label = 'Document Numérique';
            return $document;
        });

        // Fusion et pagination
        $allResults = $physicalRecords->concat($folders)->concat($documents)->sortByDesc('created_at');

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $allResults->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $records = new LengthAwarePaginator(
            $currentItems,
            $allResults->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        $viewData = [
            'records' => $records,
            'statuses' => RecordStatus::all(),
            'slipStatuses' => SlipStatus::all(),
            'terms' => ThesaurusConcept::with('labels')->get(),
            'users' => User::select('id', 'name')->get(),
            'organisations' => Organisation::select('id', 'name')->get(),
            'supports' => RecordSupport::all(),
            'activities' => Activity::all(),
            'containers' => Container::all(),
            'levels' => RecordLevel::all(),
            'authors' => Author::with('authorType')->get(),
            'keywords' => Keyword::select('id', 'name')->orderBy('name')->get()
        ];

        return view('records.index', $viewData);
    }

    public function selectLast()
    {
        $queryPhysical = RecordPhysical::with([
            'status',
            'support',
            'level',
            'activity',
            'containers',
            'user',
            'authors',
            'thesaurusConcepts'
        ]);

        // Filtrer par organisation seulement si l'utilisateur a une organisation courante
        if (Auth::user()->current_organisation_id) {
            $queryPhysical->where(function($q) {
                $q->whereHas('activity.organisations', function($subQuery) {
                    $subQuery->where('organisations.id', Auth::user()->current_organisation_id);
                })
                ->orWhereDoesntHave('activity.organisations');
            });
        }

        // Récupération des 3 types de records
        $physicalRecords = $queryPhysical->latest()->take(20)->get()->map(function($record) {
            $record->record_type = 'physical';
            $record->type_label = 'Dossier Physique';
            return $record;
        });

        $folders = RecordDigitalFolder::with(['parent', 'children', 'creator'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function($folder) {
                $folder->record_type = 'folder';
                $folder->type_label = 'Dossier Numérique';
                return $folder;
            });

        $documents = RecordDigitalDocument::with(['folder', 'type', 'attachment', 'creator'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function($document) {
                $document->record_type = 'document';
                $document->type_label = 'Document Numérique';
                return $document;
            });

        // Fusion et tri
        $allResults = $physicalRecords->concat($folders)->concat($documents)->sortByDesc('created_at');

        // Pagination
        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $allResults->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $records = new LengthAwarePaginator(
            $currentItems,
            $allResults->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        $viewData = [
            'records' => $records,
            'statuses' => RecordStatus::all(),
            'slipStatuses' => SlipStatus::all(),
            'terms' => ThesaurusConcept::with('labels')->get(),
            'users' => User::select('id', 'name')->get(),
            'organisations' => Organisation::select('id', 'name')->get()
        ];

        return view('records.index', $viewData);
    }

    public function selectBuilding()
    {
        $buildings = Building::with(['floors.rooms.shelves.containers.records'])->get();

        // Ajouter les statistiques pour chaque building
        $buildings->each(function ($building) {
            $containersCount = 0;
            $recordsCount = 0;

            foreach ($building->floors as $floor) {
                foreach ($floor->rooms as $room) {
                    foreach ($room->shelves as $shelf) {
                        $containersCount += $shelf->containers->count();
                        foreach ($shelf->containers as $container) {
                            $recordsCount += $container->records->count();
                        }
                    }
                }
            }

            $building->containers_count = $containersCount;
            $building->records_count = $recordsCount;
        });

        return view('search.record.buildingSearch', [
            'buildings' => $buildings
        ]);
    }

    public function selectFloor(Request $request)
    {
        $floors = Floor::with(['rooms.shelves.containers.records'])
            ->where('building_id', $request->input('id'))
            ->get();

        // Ajouter les statistiques pour chaque floor
        $floors->each(function ($floor) {
            $containersCount = 0;
            $recordsCount = 0;

            foreach ($floor->rooms as $room) {
                foreach ($room->shelves as $shelf) {
                    $containersCount += $shelf->containers->count();
                    foreach ($shelf->containers as $container) {
                        $recordsCount += $container->records->count();
                    }
                }
            }

            $floor->containers_count = $containersCount;
            $floor->records_count = $recordsCount;
        });

        return view('search.record.floorSearch', [
            'floors' => $floors
        ]);
    }

    public function selectRoom(Request $request)
    {
        $rooms = Room::with(['shelves.containers.records'])
            ->where('floor_id', $request->input('id'))
            ->get();

        // Ajouter les statistiques pour chaque room
        $rooms->each(function ($room) {
            $containersCount = 0;
            $recordsCount = 0;

            foreach ($room->shelves as $shelf) {
                $containersCount += $shelf->containers->count();
                foreach ($shelf->containers as $container) {
                    $recordsCount += $container->records->count();
                }
            }

            $room->containers_count = $containersCount;
            $room->records_count = $recordsCount;
        });

        return view('search.record.roomSearch', [
            'rooms' => $rooms
        ]);
    }

    public function selectShelve(Request $request)
    {
        $shelves = Shelf::with(['containers.records'])
            ->where('room_id', $request->input('id'))
            ->get();

        // Ajouter les statistiques pour chaque shelf
        $shelves->each(function ($shelf) {
            $containersCount = $shelf->containers->count();
            $recordsCount = 0;

            foreach ($shelf->containers as $container) {
                $recordsCount += $container->records->count();
            }

            $shelf->containers_count = $containersCount;
            $shelf->records_count = $recordsCount;
        });

        return view('search.record.shelveSearch', [
            'shelves' => $shelves
        ]);
    }

    public function selectContainer(Request $request)
    {
        $containers = Container::with(['records'])
            ->where('shelve_id', $request->input('id'))
            ->get();

        // Ajouter les statistiques pour chaque container
        $containers->each(function ($container) {
            $container->records_count = $container->records->count();
        });

        return view('search.record.containerSearch', [
            'containers' => $containers
        ]);
    }

    public function selectWord()
    {
        $terms = ThesaurusConcept::with(['labels'])
            ->has('records') // Seulement les termes qui ont des records
            ->withCount('records') // Compter les records de manière optimisée
            ->orderBy('records_count', 'desc') // Trier par nombre de records décroissant
            ->paginate(50);

        return view('search.record.wordSearch', [
            'terms' => $terms
        ]);
    }

    public function selectActivity()
    {
        $activities = Activity::with(['records', 'parent', 'children', 'organisations'])->get();

        // Ajouter les statistiques pour chaque activité
        $activities->each(function ($activity) {
            $activity->records_count = $activity->records->count();
            $activity->children_count = $activity->children->count();
        });

        return view('search.record.activitySearch', [
            'activities' => $activities
        ]);
    }

    public function date()
    {
        return view('search.record.dateSearch');
    }
}
