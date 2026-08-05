<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Record\StoreRecordRequest;
use App\Http\Requests\Api\V1\Record\UpdateRecordRequest;
use App\Http\Requests\Api\V1\RecordReactivation\StoreRecordReactivationRequest;
use App\Http\Resources\Api\V1\RecordReactivationResource;
use App\Http\Resources\Api\V1\RecordResource;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordReactivation;
use App\Models\RecordStatus;
use App\Models\RecordType;
use App\Services\MetadataValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D02 — notices, relu et validé le 2026-08-04 contre `RecordController` et le schéma.
 *
 * Les notices sont **org-scopées** par `organisation_id` (motif D03) : l'index ne
 * renvoie que les notices de l'organisation courante, et toute notice d'une autre
 * organisation répond 404 (jamais 403). `organisation_id` et `creator_id` sont posés
 * depuis l'agent, jamais acceptés du client. `version_number`/`is_current_version`
 * sont posés serveur (version 1 courante à la création, comme en Blade).
 *
 * Actions non-CRUD portées (les plus simples) :
 *  - `PATCH /records/{record}/status` : marquage de statut (RecordStatus).
 *  - `POST /records/{record}/reactivations` : demande de réactivation.
 */
class RecordController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'type_id', 'level_id', 'status_id', 'activity_id', 'parent_id', 'organisation_id', 'creator_id', 'access_level', 'confidentiality_id', 'access_limit_id', 'start_date', 'end_date', 'date_exact', 'date_format', 'is_current_version', 'unavailable', 'is_essential', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'type_id', 'level_id', 'status_id', 'activity_id', 'parent_id', 'organisation_id', 'creator_id', 'access_level', 'start_date', 'end_date', 'date_exact', 'date_format', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['type', 'level', 'status', 'activity', 'organisation', 'creator', 'parent', 'children', 'mediums', 'authors', 'keywords', 'attachments', 'assignedUser', 'approver', 'confidentiality', 'accessLimit'];

    /**
     * GET /api/v1/records
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        $query = Record::inOrganisation(Auth::user()->current_organisation_id)
            ->with('type')
            ->currentVersion();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'updated_at');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordResource::class));
    }

    /**
     * GET /api/v1/records/{id}
     */
    public function show(Record $record, Request $request): JsonResponse
    {
        $this->authorize('view', $record);

        // Isolation R03 : une notice hors de l'organisation courante est 404.
        $record = Record::inOrganisation(Auth::user()->current_organisation_id)
            ->with('type', 'level', 'status', 'activity', 'organisation', 'creator', 'parent', 'mediums', 'authors', 'keywords', 'attachments')
            ->findOrFail($record->id);

        return response()->json(['data' => new RecordResource($record)]);
    }

    /**
     * POST /api/v1/records
     */
    public function store(StoreRecordRequest $request): JsonResponse
    {
        $this->authorize('create', Record::class);

        $data = $request->validated();

        if (empty($data['level_id'])) {
            $data['level_id'] = RecordLevel::query()->value('id');
        }

        if (empty($data['status_id'])) {
            $data['status_id'] = RecordStatus::query()->value('id');
        }

        if (empty($data['access_level'])) {
            $data['access_level'] = 'internal';
        }

        $this->validateMetadataAgainstType($data['type_id'] ?? null, $data['metadata'] ?? null);

        $record = new Record($data);
        $record->organisation_id = Auth::user()->current_organisation_id;
        $record->creator_id = Auth::id();
        $record->version_number = 1;
        $record->is_current_version = true;

        // Code auto-généré depuis le type si absent (comme en Blade).
        if (empty($record->code)) {
            if ($record->type) {
                $record->code = $record->type->generateCode();
            } else {
                return response()->json(
                    ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Le code est requis sans type pour le générer.', 'errors' => ['code' => ['Le code est requis lorsque aucun type n\'est fourni.']]],
                    422
                );
            }
        }

        $record->save();

        // Métadonnées dynamiques (comme en Blade).
        if (!empty($data['metadata']) && is_array($data['metadata'])) {
            $record->setMultipleMetadata($data['metadata']);
            $record->save();
        }

        return response()->json(
            ['data' => new RecordResource($record->fresh())],
            201,
            ['Location' => "/api/v1/records/{$record->id}"]
        );
    }

    /**
     * PATCH /api/v1/records/{id}
     */
    public function update(UpdateRecordRequest $request, Record $record): JsonResponse
    {
        $this->authorize('update', $record);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $data = $request->validated();

        $this->validateMetadataAgainstType($data['type_id'] ?? $record->type_id, $data['metadata'] ?? null);

        if (array_key_exists('metadata', $data) && is_array($data['metadata'])) {
            $record->setMultipleMetadata($data['metadata']);
            unset($data['metadata']);
        }

        $record->update($data);

        return response()->json(['data' => new RecordResource($record->fresh())]);
    }

    /**
     * DELETE /api/v1/records/{id}
     */
    public function destroy(Record $record): Response
    {
        $this->authorize('delete', $record);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $record->delete();

        return response()->noContent();
    }

    /**
     * PATCH /api/v1/records/{record}/status
     *
     * Marquage de statut : porte sur `status_id` uniquement (action dédiée au
     * workflow, distincte de la mise à jour générale).
     */
    public function status(Request $request, Record $record): JsonResponse
    {
        $this->authorize('status', $record);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $validated = $request->validate([
            'status_id' => 'required|exists:record_statuses,id',
        ]);

        $record->update(['status_id' => $validated['status_id']]);

        return response()->json(['data' => new RecordResource($record->fresh())]);
    }

    /**
     * POST /api/v1/records/{record}/reactivations
     *
     * Demande de réactivation : enregistre la demande sur la notice (statut antérieur,
     * demandeur, date) sans l'approuver. L'approbation/le rejet sont sur
     * `/record-reactivations/{id}/approve` et `/record-reactivations/{id}/reject`.
     */
    public function reactivation(StoreRecordReactivationRequest $request, Record $record): JsonResponse
    {
        $this->authorize('reactivate', $record);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $reactivation = RecordReactivation::create([
            'record_id' => $record->id,
            'previous_status_id' => $record->status_id,
            'reason' => $request->validated()['reason'],
            'new_transfer_date' => $request->validated()['new_transfer_date'] ?? null,
            'requested_by' => Auth::id(),
            'requested_date' => now(),
        ]);

        return response()->json(
            ['data' => new RecordReactivationResource($reactivation)],
            201,
            ['Location' => "/api/v1/record-reactivations/{$reactivation->id}"]
        );
    }

    /**
     * Les anciens champs descriptifs figés (content, biographical_history, ...) sont
     * désormais des MetadataDefinition rattachées au RecordType — validées ici plutôt
     * que par une liste de règles codées en dur dans le FormRequest. Sans type, la
     * validation est ignorée (aucun profil à valider contre — comportement identique
     * à avant, où ces champs n'étaient de toute façon validés contre aucun schéma).
     */
    private function validateMetadataAgainstType(?int $typeId, ?array $metadata): void
    {
        // Toujours valider (même sans `metadata` envoyé) : un type peut avoir des
        // métadonnées obligatoires, auquel cas leur absence doit être rejetée.
        if (!$typeId) {
            return;
        }

        $type = RecordType::find($typeId);

        if ($type) {
            app(MetadataValidationService::class)->validateRecordMetadata($type, $metadata ?? []);
        }
    }
}
