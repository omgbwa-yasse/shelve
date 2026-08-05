<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ActivityResource;
use App\Http\Resources\Api\V1\BuildingResource;
use App\Http\Resources\Api\V1\ContainerResource;
use App\Http\Resources\Api\V1\FloorResource;
use App\Http\Resources\Api\V1\RecordResource;
use App\Http\Resources\Api\V1\RoomResource;
use App\Http\Resources\Api\V1\ShelfResource;
use App\Http\Resources\Api\V1\ThesaurusConceptResource;
use App\Models\Activity;
use App\Models\Building;
use App\Models\Container;
use App\Models\Floor;
use App\Models\Record;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\ThesaurusConcept;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * D10 — Recherche avancée, tri et sélecteurs de notices, porté le 2026-08-05 contre
 * `SearchRecordController` (Blade).
 *
 * Les résultats de notices sont org-scopés (`records.organisation_id`, R03). Les
 * sélecteurs de localisation suivent le contrôleur Blade (navigation par parent,
 * sans double filtrage : l'index D03 borne déjà les listes à l'organisation).
 */
class SearchRecordController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/v1/search/records/advanced?field[]=&operator[]=&value[]=
     */
    public function advanced(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        $fields = $request->input('field');
        $operators = $request->input('operator');
        $values = $request->input('value');

        $query = Record::query()
            ->currentVersion()
            ->where('organisation_id', Auth::user()->current_organisation_id)
            ->with(['type', 'level', 'status', 'activity', 'organisation']);

        if ($fields && $operators && $values) {
            foreach ($fields as $index => $field) {
                $operator = $operators[$index] ?? null;
                $value = $values[$index] ?? null;

                if ($field === '' || $field === null || $operator === '' || $operator === null || $value === '') {
                    continue;
                }

                $this->applyCriteria($query, $field, $value);
            }
        }

        $page = $query->orderBy('updated_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordResource::class));
    }

    /**
     * GET /api/v1/search/records/sort?categ=dates|term|author|activity|container|keyword&id=&date_*=...
     */
    public function sort(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        $query = Record::query()
            ->currentVersion()
            ->where('organisation_id', Auth::user()->current_organisation_id)
            ->with(['type', 'level', 'status', 'activity', 'organisation']);

        switch ($request->input('categ')) {
            case 'dates':
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');

                if ($exactDate) {
                    $query->whereDate('date_exact', $exactDate);
                }

                if ($startDate && $endDate) {
                    $query->where(function (Builder $q) use ($startDate, $endDate) {
                        $q->whereDate('start_date', '>=', $startDate)
                            ->whereDate('end_date', '<=', $endDate);
                    });
                }
                break;

            case 'term':
            case 'concept':
                $query->whereHas('thesaurusConcepts', fn (Builder $q) => $q->where('thesaurus_concepts.id', $request->input('id')));
                break;

            case 'author':
                $query->whereHas('authors', fn (Builder $q) => $q->where('authors.id', $request->input('id')));
                break;

            case 'activity':
                $query->where('activity_id', $request->input('id'));
                break;

            case 'container':
                $query->whereHas('mediums', fn (Builder $q) => $q->where('container_id', $request->input('id')));
                break;

            case 'keyword':
                $query->whereHas('keywords', fn (Builder $q) => $q->where('keywords.id', $request->input('id')));
                break;
        }

        $page = $query->orderBy('updated_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordResource::class));
    }

    /**
     * GET /api/v1/search/records/last
     */
    public function last(): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        $query = Record::query()
            ->currentVersion()
            ->where('organisation_id', Auth::user()->current_organisation_id)
            ->with(['type', 'level', 'status', 'activity', 'organisation']);

        $page = $query->latest()->paginate($this->pageSize(request()))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordResource::class));
    }

    /**
     * GET /api/v1/search/records/words
     */
    public function words(): JsonResponse
    {
        $this->authorize('viewAny', ThesaurusConcept::class);

        // Pagination fixe de 50 (comme le contrôleur Blade) : un nuage de mots n'a
        // pas besoin de la pagination client, bornée par `page[size]` (max 100).
        $page = ThesaurusConcept::query()
            ->with(['labels'])
            ->has('records')
            ->withCount('records')
            ->orderBy('records_count', 'desc')
            ->paginate(50)
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, ThesaurusConceptResource::class));
    }

    /**
     * GET /api/v1/search/records/activities
     */
    public function activities(): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        $activities = Activity::with(['records', 'parent', 'children', 'organisations'])->get();

        $activities->each(function (Activity $activity) {
            $activity->records_count = $activity->records->count();
            $activity->children_count = $activity->children->count();
        });

        return response()->json(['data' => ActivityResource::collection($activities)]);
    }

    /**
     * GET /api/v1/search/locations/buildings
     */
    public function buildings(): JsonResponse
    {
        $this->authorize('viewAny', Building::class);

        $buildings = Building::with(['floors.rooms.shelves.containers.records'])->get();

        return response()->json(['data' => BuildingResource::collection($buildings)]);
    }

    /**
     * GET /api/v1/search/locations/floors?building_id=...
     */
    public function floors(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Floor::class);

        $floors = Floor::with(['rooms.shelves.containers.records'])
            ->where('building_id', $request->input('building_id', $request->input('id')))
            ->get();

        return response()->json(['data' => FloorResource::collection($floors)]);
    }

    /**
     * GET /api/v1/search/locations/rooms?floor_id=...
     */
    public function rooms(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Room::class);

        $rooms = Room::with(['shelves.containers.records'])
            ->where('floor_id', $request->input('floor_id', $request->input('id')))
            ->get();

        return response()->json(['data' => RoomResource::collection($rooms)]);
    }

    /**
     * GET /api/v1/search/locations/shelves?room_id=...
     */
    public function shelves(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Shelf::class);

        $shelves = Shelf::with(['containers.records'])
            ->where('room_id', $request->input('room_id', $request->input('id')))
            ->get();

        return response()->json(['data' => ShelfResource::collection($shelves)]);
    }

    /**
     * GET /api/v1/search/locations/containers?shelf_id=...
     */
    public function containers(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Container::class);

        $containers = Container::with(['records'])
            ->where('shelve_id', $request->input('shelf_id', $request->input('shelve_id', $request->input('id'))))
            ->get();

        return response()->json(['data' => ContainerResource::collection($containers)]);
    }

    /**
     * Critère de la recherche avancée (logique du contrôleur Blade `advanced`).
     */
    private function applyCriteria(Builder $query, string $field, mixed $value): void
    {
        switch ($field) {
            case 'code':
            case 'name':
            case 'content':
            case 'description':
                $query->where($field, 'like', '%' . $value . '%');
                break;

            case 'date_start':
            case 'date_end':
            case 'date_exact':
                $query->whereDate($field, $value);
                break;

            case 'activity':
                $query->where('activity_id', $value);
                break;

            case 'author':
                $query->whereHas('authors', fn (Builder $q) => $q->where('authors.id', $value));
                break;

            case 'keyword':
                $query->whereHas('keywords', fn (Builder $q) => $q->where('keywords.id', $value));
                break;

            case 'term':
            case 'concept':
                $query->whereHas('thesaurusConcepts', fn (Builder $q) => $q->where('thesaurus_concepts.id', $value));
                break;

            default:
                if (Schema::hasColumn('records', $field)) {
                    $query->where($field, '=', $value);
                }
                break;
        }
    }
}
