<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CommunicationRecord\StoreCommunicationRecordRequest;
use App\Http\Requests\Api\V1\CommunicationRecord\UpdateCommunicationRecordRequest;
use App\Http\Resources\Api\V1\CommunicationRecordResource;
use App\Models\Communication;
use App\Models\CommunicationRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D05 — relu et validé le 2026-08-04 contre `CommunicationRecordController` et le schéma.
 *
 * Les documents de communication (pivot `communication_record`) sont **org-scopés** par
 * leur communication parente (motif D03) : la `Communication` est résolue dans
 * l'organisation courante, puis chaque pivot est bornée à `communication_id`.
 * `communication_id` vient de la route, `return_date` est calculé serveur (+14 jours,
 * comme en Blade). Le Blade référençait `CommunicationRecordPhysical` (classe inexistante) :
 * corrigé avec le modèle réel `communicationRecord`.
 *
 * TODO (non porté) : `searchRecords` — recherche d'archives `RecordPhysical` pour le
 * formulaire de création (domaine D02, non porté).
 */
class CommunicationRecordController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'communication_id', 'record_id', 'is_original', 'return_date', 'return_effective', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'communication_id', 'record_id', 'is_original', 'return_date', 'return_effective', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['communication', 'record'];

    /**
     * GET /api/v1/communications/{communication}/records
     */
    public function index(Communication $communication, Request $request): JsonResponse
    {
        $this->authorize('viewAny', CommunicationRecord::class);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);

        $query = CommunicationRecord::where('communication_id', $communication->id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, CommunicationRecordResource::class));
    }

    /**
     * GET /api/v1/communications/{communication}/records/{id}
     */
    public function show(Communication $communication, CommunicationRecord $communicationRecord): JsonResponse
    {
        $this->authorize('view', $communicationRecord);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);
        $communicationRecord = $this->resolveRecord($communication, $communicationRecord->id);

        return response()->json(['data' => new CommunicationRecordResource($communicationRecord)]);
    }

    /**
     * POST /api/v1/communications/{communication}/records
     */
    public function store(StoreCommunicationRecordRequest $request, Communication $communication): JsonResponse
    {
        $this->authorize('create', CommunicationRecord::class);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);

        $communicationRecord = CommunicationRecord::create($request->validated() + [
            'communication_id' => $communication->id,
            'return_date' => now()->addDays(14)->format('Y-m-d'),
            'operator_id' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new CommunicationRecordResource($communicationRecord)],
            201,
            ['Location' => "/api/v1/communications/{$communication->id}/records/{$communicationRecord->id}"]
        );
    }

    /**
     * PUT /api/v1/communications/{communication}/records/{id}
     */
    public function update(UpdateCommunicationRecordRequest $request, Communication $communication, CommunicationRecord $communicationRecord): JsonResponse
    {
        $this->authorize('update', $communicationRecord);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);
        $communicationRecord = $this->resolveRecord($communication, $communicationRecord->id);

        $communicationRecord->update($request->validated());

        return response()->json(['data' => new CommunicationRecordResource($communicationRecord->fresh())]);
    }

    /**
     * DELETE /api/v1/communications/{communication}/records/{id}
     */
    public function destroy(Communication $communication, CommunicationRecord $communicationRecord): Response
    {
        $this->authorize('delete', $communicationRecord);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);
        $communicationRecord = $this->resolveRecord($communication, $communicationRecord->id);

        $communicationRecord->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/communications/{communication}/records/{id}/return-effective
     */
    public function returnEffective(Communication $communication, CommunicationRecord $communicationRecord): JsonResponse
    {
        $this->authorize('update', $communicationRecord);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);
        $communicationRecord = $this->resolveRecord($communication, $communicationRecord->id);

        $communicationRecord->update([
            'return_effective' => now(),
            'operator_id' => Auth::id(),
        ]);

        return response()->json(['data' => new CommunicationRecordResource($communicationRecord->fresh())]);
    }

    /**
     * POST /api/v1/communications/{communication}/records/{id}/return-cancel
     */
    public function returnCancel(Communication $communication, CommunicationRecord $communicationRecord): JsonResponse
    {
        $this->authorize('update', $communicationRecord);

        $communication = Communication::forOrganisation(Auth::user()->current_organisation_id)->findOrFail($communication->id);
        $communicationRecord = $this->resolveRecord($communication, $communicationRecord->id);

        $communicationRecord->update(['return_effective' => null]);

        return response()->json(['data' => new CommunicationRecordResource($communicationRecord->fresh())]);
    }

    private function resolveRecord(Communication $communication, int $id): CommunicationRecord
    {
        return CommunicationRecord::where('communication_id', $communication->id)->findOrFail($id);
    }
}
