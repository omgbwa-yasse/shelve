<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordChild\StoreRecordChildRequest;
use App\Http\Requests\Api\V1\RecordChild\UpdateRecordChildRequest;
use App\Http\Resources\Api\V1\RecordResource;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordType;
use App\Services\MetadataValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D02 — notices filles, relu et validé le 2026-08-04 contre `RecordChildController`.
 *
 * Sous-ressource de la notice parente, **org-scopée par héritage** (motif D03) : la
 * notice parente est résolue dans l'organisation courante, puis chaque enfant est
 * borné au `parent_id` de cette notice (404 hors périmètre). La notice fille est un
 * `Record` (table `records`) : la Policy utilisée est `RecordPolicy` (préfixe `records_*`).
 */
class RecordChildController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'type_id', 'level_id', 'status_id', 'activity_id', 'start_date', 'end_date', 'date_exact', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'type_id', 'level_id', 'status_id', 'activity_id', 'start_date', 'end_date', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['type', 'level', 'status', 'activity', 'parent', 'children', 'authors', 'keywords', 'attachments'];

    /**
     * GET /api/v1/records/{record}/children
     */
    public function index(Record $record, Request $request): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $query = $record->children()->currentVersion()->getQuery();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordResource::class));
    }

    /**
     * GET /api/v1/records/{record}/children/{child}
     */
    public function show(Record $record, Record $child): JsonResponse
    {
        $this->authorize('view', $child);

        $child = $this->resolveChild($record, $child);

        return response()->json(['data' => new RecordResource($child)]);
    }

    /**
     * POST /api/v1/records/{record}/children
     */
    public function store(StoreRecordChildRequest $request, Record $record): JsonResponse
    {
        $this->authorize('create', Record::class);

        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        $data = $request->validated();

        if (empty($data['level_id'])) {
            $data['level_id'] = RecordLevel::query()->value('id');
        }

        if (empty($data['status_id'])) {
            $data['status_id'] = RecordStatus::query()->value('id');
        }

        $this->validateMetadataAgainstType($data['type_id'] ?? $record->type_id, $data['metadata'] ?? null);

        $child = new Record($data);
        $child->parent_id = $record->id;
        $child->organisation_id = $record->organisation_id;
        $child->creator_id = Auth::id();
        $child->access_level = $child->access_level ?? 'internal';
        $child->version_number = 1;
        $child->is_current_version = true;

        if (empty($child->code) && $child->type) {
            $child->code = $child->type->generateCode();
        } elseif (empty($child->code)) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Le code est requis sans type pour le générer.', 'errors' => ['code' => ['Le code est requis lorsque aucun type n\'est fourni.']]],
                422
            );
        }

        $child->save();

        if (!empty($data['metadata']) && is_array($data['metadata'])) {
            $child->setMultipleMetadata($data['metadata']);
            $child->save();
        }

        return response()->json(
            ['data' => new RecordResource($child->fresh())],
            201,
            ['Location' => "/api/v1/records/{$record->id}/children/{$child->id}"]
        );
    }

    /**
     * PATCH /api/v1/records/{record}/children/{child}
     */
    public function update(UpdateRecordChildRequest $request, Record $record, Record $child): JsonResponse
    {
        $this->authorize('update', $child);

        $child = $this->resolveChild($record, $child);

        $data = $request->validated();

        $this->validateMetadataAgainstType($data['type_id'] ?? $child->type_id, $data['metadata'] ?? null);

        if (array_key_exists('metadata', $data) && is_array($data['metadata'])) {
            $child->setMultipleMetadata($data['metadata']);
            unset($data['metadata']);
        }

        $child->update($data);

        return response()->json(['data' => new RecordResource($child->fresh())]);
    }

    /**
     * DELETE /api/v1/records/{record}/children/{child}
     */
    public function destroy(Record $record, Record $child): Response
    {
        $this->authorize('delete', $child);

        $child = $this->resolveChild($record, $child);

        $child->delete();

        return response()->noContent();
    }

    /**
     * Résout un enfant borné au `parent_id` de la notice parente, dans l'organisation
     * courante. Une notice d'une autre organisation ou d'un autre parent répond 404.
     */
    private function resolveChild(Record $record, Record $child): Record
    {
        $record = Record::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($record->id);

        return $record->children()
            ->whereKey($child->id)
            ->firstOrFail();
    }

    /**
     * Les anciens champs descriptifs figés sont désormais des MetadataDefinition
     * rattachées au RecordType — validées ici plutôt que par une liste de règles
     * codées en dur dans le FormRequest.
     */
    private function validateMetadataAgainstType(?int $typeId, ?array $metadata): void
    {
        if (!$typeId) {
            return;
        }

        $type = RecordType::find($typeId);

        if ($type) {
            app(MetadataValidationService::class)->validateRecordMetadata($type, $metadata ?? []);
        }
    }
}
