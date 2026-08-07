<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Activity\StoreActivityRequest;
use App\Http\Requests\Api\V1\Activity\UpdateActivityRequest;
use App\Http\Resources\Api\V1\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre le contrôleur Blade et le schéma.
 */
class ActivityController extends Controller
{
    use HandlesApiQueries;

    /**
     * Listes blanches — CONVENTIONS §3. Un champ hors liste provoque un 400, jamais
     * un filtre silencieusement ignoré : un filtre ignoré renvoie des données que
     * l'appelant croit filtrées (risque R03).
     *
     * À RESTREINDRE : la liste ci-dessous reprend toutes les colonnes exploitables.
     */
    private const FILTERABLE = ['id', 'code', 'name', 'parent_id', 'communicability_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'parent_id', 'communicability_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = [];

    /**
     * GET /api/v1/activities
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        $query = Activity::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ActivityResource::class));
    }

    /**
     * GET /api/v1/activities/{id}
     */
    public function show(Activity $activity): JsonResponse
    {
        $this->authorize('view', $activity);

        return response()->json(['data' => new ActivityResource($activity)]);
    }

    /**
     * POST /api/v1/activities
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        $this->authorize('create', Activity::class);

        $activity = Activity::create($request->validated());

        return response()->json(
            ['data' => new ActivityResource($activity)],
            201,
            ['Location' => "/api/v1/activities/{$activity->id}"]
        );
    }

    /**
     * PATCH /api/v1/activities/{id}
     */
    public function update(UpdateActivityRequest $request, Activity $activity): JsonResponse
    {
        $this->authorize('update', $activity);

        // TODO concurrence optimiste : vérifier If-Match contre updated_at (CONVENTIONS §7).

        $activity->update($request->validated());

        return response()->json(['data' => new ActivityResource($activity->fresh())]);
    }

    /**
     * DELETE /api/v1/activities/{id}
     */
    public function destroy(Activity $activity): Response
    {
        $this->authorize('delete', $activity);

        // TODO renvoyer 409 si une contrainte d'intégrité empêche la suppression.
        $activity->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/activities/list — liste à plat avec recherche, filtre alphabétique
     * et marqueur `has_children`, comme le `list()` du contrôleur Blade.
     *
     * `/list` précède `/hierarchy` et le verbe `{activity}` : à déclarer avant la
     * route `show` dans routes/api.php.
     */
    public function list(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        $query = Activity::query();
        $search = $request->input('search');
        $filter = $request->input('filter', 'all');
        $parentId = $request->input('parent_id');

        if ($search) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"));
        }

        if ($filter !== 'all') {
            if ($filter === '#') {
                $query->where(fn ($q) => $q->whereRaw("name NOT REGEXP '^[A-Za-z0-9]'")
                    ->orWhereRaw("code NOT REGEXP '^[A-Za-z0-9]'"));
            } elseif (strlen($filter) === 1) {
                $query->where(fn ($q) => $q->where('name', 'like', "{$filter}%")
                    ->orWhere('code', 'like', "{$filter}%"));
            }
        }

        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        } elseif (!$search && $filter === 'all') {
            $query->whereNull('parent_id');
        }

        $page = $query->orderBy('code')->paginate($this->pageSize($request))->withQueryString();

        $page->getCollection()->transform(function ($activity) {
            $activity->has_children = $activity->children()->exists();

            return $activity;
        });

        return response()->json($this->paginatedResponse($page, ActivityResource::class));
    }

    /**
     * GET /api/v1/activities/hierarchy/{id?} — hiérarchie des activités.
     * Sans `id` : les activités racines. Avec `id` : l'activité et ses enfants.
     */
    public function hierarchy(?Activity $activity = null): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        if ($activity) {
            $activity->load('children');

            return response()->json([
                'data' => ['activity' => new ActivityResource($activity), 'children' => ActivityResource::collection($activity->children)],
            ]);
        }

        $roots = Activity::whereNull('parent_id')->orderBy('code')->get();

        return response()->json(['data' => ['root_activities' => ActivityResource::collection($roots)]]);
    }
}
