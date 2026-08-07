<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReferenceList\StoreReferenceListRequest;
use App\Http\Requests\Api\V1\ReferenceList\UpdateReferenceListRequest;
use App\Http\Requests\Api\V1\ReferenceList\StoreReferenceValueRequest;
use App\Http\Requests\Api\V1\ReferenceList\UpdateReferenceValueRequest;
use App\Http\Resources\Api\V1\ReferenceListResource;
use App\Http\Resources\Api\V1\ReferenceValueResource;
use App\Models\ReferenceList;
use App\Models\ReferenceValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D01 — relu et validé le 2026-08-04 contre `Settings\ReferenceListController` et le schéma.
 *
 * Les valeurs sont des sous-ressources imbriquées de leur liste (`.../values`), et
 * `created_by`/`updated_by` sont toujours posés depuis l'agent authentifié, jamais
 * acceptés du client.
 */
class ReferenceListController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'code', 'active', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'code', 'active', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['values', 'creator', 'updater'];

    /**
     * GET /api/v1/reference-lists
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ReferenceList::class);

        $query = ReferenceList::query()->withCount('values');

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ReferenceListResource::class));
    }

    /**
     * GET /api/v1/reference-lists/{id}
     */
    public function show(ReferenceList $referenceList): JsonResponse
    {
        $this->authorize('view', $referenceList);

        return response()->json(['data' => new ReferenceListResource($referenceList->load('values'))]);
    }

    /**
     * POST /api/v1/reference-lists
     */
    public function store(StoreReferenceListRequest $request): JsonResponse
    {
        $this->authorize('create', ReferenceList::class);

        $list = ReferenceList::create($request->validated() + ['created_by' => Auth::id()]);

        return response()->json(
            ['data' => new ReferenceListResource($list)],
            201,
            ['Location' => "/api/v1/reference-lists/{$list->id}"]
        );
    }

    /**
     * PATCH /api/v1/reference-lists/{id}
     */
    public function update(UpdateReferenceListRequest $request, ReferenceList $referenceList): JsonResponse
    {
        $this->authorize('update', $referenceList);

        $referenceList->update($request->validated() + ['updated_by' => Auth::id()]);

        return response()->json(['data' => new ReferenceListResource($referenceList->fresh())]);
    }

    /**
     * DELETE /api/v1/reference-lists/{id}
     */
    public function destroy(ReferenceList $referenceList): Response
    {
        $this->authorize('delete', $referenceList);

        // Une liste référencée par des définitions de métadonnées ne se supprime pas.
        if ($referenceList->metadataDefinitions()->count() > 0) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit d\'intégrité', 'status' => 409, 'detail' => 'Impossible de supprimer une liste de référence utilisée par des définitions de métadonnées.'],
                409
            );
        }

        $referenceList->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/reference-lists/{referenceList}/values
     */
    public function addValue(StoreReferenceValueRequest $request, ReferenceList $referenceList): JsonResponse
    {
        $this->authorize('create', $referenceList);

        $exists = ReferenceValue::where('list_id', $referenceList->id)
            ->where('code', $request->input('code'))
            ->exists();

        if ($exists) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Ce code existe déjà dans cette liste.', 'errors' => ['code' => ['Ce code existe déjà dans cette liste.']]],
                422
            );
        }

        $value = ReferenceValue::create(
            $request->validated() + ['list_id' => $referenceList->id, 'created_by' => Auth::id()]
        );

        return response()->json(
            ['data' => new ReferenceValueResource($value)],
            201,
            ['Location' => "/api/v1/reference-lists/{$referenceList->id}/values/{$value->id}"]
        );
    }

    /**
     * PATCH /api/v1/reference-lists/{referenceList}/values/{value}
     */
    public function updateValue(UpdateReferenceValueRequest $request, ReferenceList $referenceList, ReferenceValue $value): JsonResponse
    {
        $this->authorize('update', $referenceList);

        $exists = ReferenceValue::where('list_id', $referenceList->id)
            ->where('code', $request->input('code'))
            ->where('id', '!=', $value->id)
            ->exists();

        if ($exists) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Ce code existe déjà dans cette liste.', 'errors' => ['code' => ['Ce code existe déjà dans cette liste.']]],
                422
            );
        }

        $value->update($request->validated() + ['updated_by' => Auth::id()]);

        return response()->json(['data' => new ReferenceValueResource($value->fresh())]);
    }

    /**
     * DELETE /api/v1/reference-lists/{referenceList}/values/{value}
     */
    public function deleteValue(ReferenceList $referenceList, ReferenceValue $value): Response
    {
        $this->authorize('delete', $referenceList);

        $value->delete();

        return response()->noContent();
    }
}

