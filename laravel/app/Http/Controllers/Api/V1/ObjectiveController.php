<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Objective\StoreObjectiveRequest;
use App\Http\Requests\Api\V1\Objective\UpdateObjectiveRequest;
use App\Http\Resources\Api\V1\ObjectiveResource;
use App\Models\Objective;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * D17 — Objectifs (OKR). Voir `evolution/PROJECT-OKR-KPI-PLAN.md`, §3.
 * `store()` accepte un tableau `key_results` optionnel pour créer l'objectif
 * et ses résultats clés en une seule requête (écran de création OKR).
 */
class ObjectiveController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'title', 'status', 'project_id', 'attachable_type', 'attachable_id', 'organisation_id', 'owner_id'];
    private const SORTABLE = ['id', 'title', 'status', 'period_start', 'period_end', 'created_at'];
    private const INCLUDABLE = ['owner', 'creator', 'updater', 'attachable', 'project', 'keyResults'];

    /**
     * GET /api/v1/objectives
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Objective::class);

        $query = Objective::byOrganisation(Auth::user()->current_organisation_id)->with('keyResults');

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->orderByDesc('created_at')->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ObjectiveResource::class));
    }

    /**
     * GET /api/v1/objectives/{id}
     */
    public function show(Objective $objective): JsonResponse
    {
        $this->authorize('view', $objective);

        $objective = Objective::byOrganisation(Auth::user()->current_organisation_id)
            ->with(['owner', 'attachable', 'keyResults'])
            ->findOrFail($objective->id);

        return response()->json(['data' => new ObjectiveResource($objective)]);
    }

    /**
     * POST /api/v1/objectives
     */
    public function store(StoreObjectiveRequest $request): JsonResponse
    {
        $this->authorize('create', Objective::class);

        $data = $request->validated();
        $data['attachable_type'] = Objective::resolveAttachableAlias($data['attachable_type']);
        $keyResults = $data['key_results'] ?? [];
        unset($data['key_results']);

        $objective = DB::transaction(function () use ($data, $keyResults) {
            $objective = Objective::create($data + [
                'organisation_id' => Auth::user()->current_organisation_id,
                'created_by' => Auth::id(),
            ]);

            foreach ($keyResults as $index => $keyResult) {
                $objective->keyResults()->create($keyResult + ['sort_order' => $index]);
            }

            return $objective;
        });

        return response()->json(
            ['data' => new ObjectiveResource($objective->load(['attachable', 'keyResults']))],
            201,
            ['Location' => "/api/v1/objectives/{$objective->id}"]
        );
    }

    /**
     * PATCH /api/v1/objectives/{id}
     */
    public function update(UpdateObjectiveRequest $request, Objective $objective): JsonResponse
    {
        $this->authorize('update', $objective);

        $objective = Objective::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($objective->id);

        $data = $request->validated();

        if (isset($data['attachable_type'])) {
            $data['attachable_type'] = Objective::resolveAttachableAlias($data['attachable_type']);
        }

        $objective->update($data + ['updated_by' => Auth::id()]);

        return response()->json(['data' => new ObjectiveResource($objective->fresh(['attachable', 'keyResults']))]);
    }

    /**
     * DELETE /api/v1/objectives/{id}
     */
    public function destroy(Objective $objective): Response
    {
        $this->authorize('delete', $objective);

        $objective = Objective::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($objective->id);

        $objective->delete();

        return response()->noContent();
    }
}
