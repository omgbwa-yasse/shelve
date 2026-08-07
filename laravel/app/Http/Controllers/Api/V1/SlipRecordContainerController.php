<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SlipRecordContainer\StoreSlipRecordContainerRequest;
use App\Http\Requests\Api\V1\SlipRecordContainer\UpdateSlipRecordContainerRequest;
use App\Http\Resources\Api\V1\SlipRecordContainerResource;
use App\Models\Container;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\SlipRecordContainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D04 — relu et validé le 2026-08-04 contre `SlipRecordContainerController` et le schéma.
 *
 * Association document↔contenant, **org-scopée** par héritage (motif D03) : le `Slip`
 * parent est résolu dans l'organisation courante, et la pivot est bornée au `slipRecord`
 * de ce bordereau (404 hors périmètre). La table `slip_record_container` a une clé
 * composite `(slip_record_id, container_id)` sans colonne `id` : chaque ressource est
 * résolue sur ces deux clés, sans modèle lié de route. `creator_id` posé depuis l'agent.
 * Le contenant doit appartenir à l'organisation courante (R03).
 */
class SlipRecordContainerController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['slip_record_id', 'container_id', 'description', 'created_at', 'updated_at'];
    private const SORTABLE = ['slip_record_id', 'container_id', 'description', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['slipRecord', 'container', 'creator'];

    /**
     * GET /api/v1/slips/{slip}/records/{record}/containers
     */
    public function index(Slip $slip, SlipRecord $record, Request $request): JsonResponse
    {
        $this->authorize('viewAny', SlipRecordContainer::class);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);
        $record = $slip->records()->whereKey($record->id)->firstOrFail();

        $query = SlipRecordContainer::where('slip_record_id', $record->id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        // `slip_record_container` a une clé composite (slip_record_id, container_id),
        // pas de colonne `id` : le tri par défaut d'applySorting() plantait en 500 sur
        // tout appel sans `?sort=` explicite (même défaut que UserOrganisationRoleController).
        $this->applySorting($query, $request, self::SORTABLE, 'created_at');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, SlipRecordContainerResource::class));
    }

    /**
     * GET /api/v1/slips/{slip}/records/{record}/containers/{container}
     */
    public function show(Slip $slip, SlipRecord $record, string $container): JsonResponse
    {
        $pivot = $this->resolvePivot($slip, $record, $container);

        $this->authorize('view', $pivot);

        return response()->json(['data' => new SlipRecordContainerResource($pivot)]);
    }

    /**
     * POST /api/v1/slips/{slip}/records/{record}/containers
     */
    public function store(StoreSlipRecordContainerRequest $request, Slip $slip, SlipRecord $record): JsonResponse
    {
        $this->authorize('create', SlipRecordContainer::class);

        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);
        $record = $slip->records()->whereKey($record->id)->firstOrFail();

        // Le contenant doit appartenir à l'organisation courante (R03).
        if (!Container::inOrganisation(Auth::user()->current_organisation_id)->whereKey($request->input('container_id'))->exists()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Le contenant n\'appartient pas à votre organisation.', 'errors' => ['container_id' => ['Le contenant n\'appartient pas à votre organisation.']]],
                422
            );
        }

        $pivot = SlipRecordContainer::create($request->validated() + [
            'slip_record_id' => $record->id,
            'creator_id' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new SlipRecordContainerResource($pivot)],
            201,
            ['Location' => "/api/v1/slips/{$slip->id}/records/{$record->id}/containers/{$pivot->container_id}"]
        );
    }

    /**
     * PATCH /api/v1/slips/{slip}/records/{record}/containers/{container}
     */
    public function update(UpdateSlipRecordContainerRequest $request, Slip $slip, SlipRecord $record, string $container): JsonResponse
    {
        $pivot = $this->resolvePivot($slip, $record, $container);

        $this->authorize('update', $pivot);

        // Clé composite sans colonne `id` : les opérations passent par le query builder
        // (les méthodes d'instance `update()`/`fresh()` échouent — « Illegal offset type »).
        SlipRecordContainer::where('slip_record_id', $pivot->slip_record_id)
            ->where('container_id', $pivot->container_id)
            ->update($request->validated() + ['creator_id' => Auth::id()]);

        $pivot = $this->resolvePivot($slip, $record, $container);

        return response()->json(['data' => new SlipRecordContainerResource($pivot)]);
    }

    /**
     * DELETE /api/v1/slips/{slip}/records/{record}/containers/{container}
     */
    public function destroy(Slip $slip, SlipRecord $record, string $container): Response
    {
        $pivot = $this->resolvePivot($slip, $record, $container);

        $this->authorize('delete', $pivot);

        // Clé composite : suppression via query builder (cf. `update`).
        SlipRecordContainer::where('slip_record_id', $pivot->slip_record_id)
            ->where('container_id', $pivot->container_id)
            ->delete();

        return response()->noContent();
    }

    private function resolvePivot(Slip $slip, SlipRecord $record, string $container): SlipRecordContainer
    {
        $slip = Slip::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($slip->id);
        $record = $slip->records()->whereKey($record->id)->firstOrFail();

        return SlipRecordContainer::where('slip_record_id', $record->id)
            ->where('container_id', (int) $container)
            ->firstOrFail();
    }
}
