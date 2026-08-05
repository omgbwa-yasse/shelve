<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordContainer\StoreRecordContainerRequest;
use App\Http\Requests\Api\V1\RecordContainer\UpdateRecordContainerRequest;
use App\Http\Resources\Api\V1\RecordContainerResource;
use App\Models\Container;
use App\Models\Record;
use App\Models\RecordContainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D02 — contenants d'une notice, relu et validé le 2026-08-04 contre
 * `RecordContainerController` et le schéma (table `record_physical_container`).
 *
 * Pivot **org-scopée par héritage** de la notice parente (motif D03) : la notice est
 * résolue dans l'organisation courante, puis la pivot est bornée à cette notice
 * (404 hors périmètre). Clé composite `(record_physical_id, container_id)` sans
 * colonne `id`. Depuis l'unification (2026-08-04), `record_physical_id` porte l'id de
 * la notice (`records.id`). Le contenant doit appartenir à l'organisation courante
 * (R03). `creator_id` posé depuis l'agent.
 */
class RecordContainerController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['record_physical_id', 'container_id', 'creator_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['record_physical_id', 'container_id', 'creator_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['container'];

    /**
     * GET /api/v1/records/{record}/containers
     */
    public function index(Record $record, Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecordContainer::class);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $query = RecordContainer::with('container')->where('record_physical_id', $record->id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordContainerResource::class));
    }

    /**
     * GET /api/v1/records/{record}/containers/{container}
     */
    public function show(Record $record, Container $container): JsonResponse
    {
        $pivot = $this->resolvePivot($record, $container);

        $this->authorize('view', $pivot);

        return response()->json(['data' => new RecordContainerResource($pivot->load('container'))]);
    }

    /**
     * POST /api/v1/records/{record}/containers
     */
    public function store(StoreRecordContainerRequest $request, Record $record): JsonResponse
    {
        $this->authorize('create', RecordContainer::class);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        // Le contenant doit appartenir à l'organisation courante (R03).
        if (!Container::inOrganisation(Auth::user()->current_organisation_id)
            ->whereKey($request->input('container_id'))
            ->exists()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Le contenant n\'appartient pas à votre organisation.', 'errors' => ['container_id' => ['Le contenant n\'appartient pas à votre organisation.']]],
                422
            );
        }

        $pivot = RecordContainer::firstOrCreate(
            ['record_physical_id' => $record->id, 'container_id' => $request->input('container_id')],
            [
                'description' => $request->input('description'),
                'creator_id' => Auth::id(),
            ]
        );

        return response()->json(
            ['data' => new RecordContainerResource($pivot->load('container'))],
            201,
            ['Location' => "/api/v1/records/{$record->id}/containers/{$pivot->container_id}"]
        );
    }

    /**
     * PATCH /api/v1/records/{record}/containers/{container}
     */
    public function update(UpdateRecordContainerRequest $request, Record $record, Container $container): JsonResponse
    {
        $pivot = $this->resolvePivot($record, $container);

        $this->authorize('update', $pivot);

        // Clé composite sans colonne `id` : mise à jour via query builder.
        RecordContainer::where('record_physical_id', $pivot->record_physical_id)
            ->where('container_id', $pivot->container_id)
            ->update($request->validated());

        $pivot = $this->resolvePivot($record, $container);

        return response()->json(['data' => new RecordContainerResource($pivot->load('container'))]);
    }

    /**
     * DELETE /api/v1/records/{record}/containers/{container}
     */
    public function destroy(Record $record, Container $container): Response
    {
        $pivot = $this->resolvePivot($record, $container);

        $this->authorize('delete', $pivot);

        RecordContainer::where('record_physical_id', $pivot->record_physical_id)
            ->where('container_id', $pivot->container_id)
            ->delete();

        return response()->noContent();
    }

    private function resolvePivot(Record $record, Container $container): RecordContainer
    {
        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        return RecordContainer::where('record_physical_id', $record->id)
            ->where('container_id', $container->id)
            ->firstOrFail();
    }
}
