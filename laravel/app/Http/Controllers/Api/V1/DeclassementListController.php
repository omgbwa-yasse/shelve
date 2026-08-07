<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeclassementList\StoreDeclassementListRequest;
use App\Http\Requests\Api\V1\DeclassementList\UpdateDeclassementListRequest;
use App\Http\Resources\Api\V1\DeclassementListResource;
use App\Models\DeclassementComment;
use App\Models\DeclassementList;
use App\Models\DeclassementRecord;
use App\Models\DeclassementStatus;
use App\Models\Record;
use App\Models\RecordStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D07 — listes de déclassement, relu le 2026-08-04 contre `DeclassementListController`
 * (Blade) et le schéma.
 *
 * Les listes sont **org-scopées** (`organisation_id`, trait BelongsToOrganisation) :
 * motif D03 — index borné à l'organisation courante, ressource d'une autre
 * organisation en 404 (jamais 403, voir CONVENTIONS §4). `creator_id`,
 * `organisation_id` et les champs d'approbation/validation/traitement sont posés
 * depuis l'agent, jamais acceptés du client.
 *
 * Le workflow complet est porté : eligible-records, add-records, remove-record,
 * comment, request-approval, approve, validate, process, reject. Le portage des
 * `record_ids` cible la table unifiée `records` (`declassement_records.record_id`),
 * corrigeant le `record_physical_id` périmé du Blade.
 */
class DeclassementListController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'organisation_id', 'declassement_status_id', 'is_approval_requested', 'is_approved', 'is_validated', 'is_treated', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['status', 'creator', 'records', 'comments'];

    /**
     * Récupère (ou crée si absent) un statut de déclassement par son libellé.
     */
    private function statusByName(string $name): DeclassementStatus
    {
        return DeclassementStatus::firstOrCreate(['name' => $name]);
    }

    /**
     * GET /api/v1/declassement-lists
     *
     * Le filtre `categ` (draft/requested/approved/validated/treated) reproduit le
     * contrôleur Blade : seules les listes de l'organisation courante sont renvoyées.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DeclassementList::class);

        $query = DeclassementList::byOrganisation(Auth::user()->current_organisation_id);

        switch ($request->input('categ', 'draft')) {
            case 'requested':
                $query->where('is_approval_requested', true)->where('is_approved', false);
                break;
            case 'approved':
                $query->where('is_approved', true)->where('is_validated', false);
                break;
            case 'validated':
                $query->where('is_validated', true)->where('is_treated', false);
                break;
            case 'treated':
                $query->where('is_treated', true);
                break;
            case 'draft':
            default:
                $query->where('is_approval_requested', false);
                break;
        }

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->orderByDesc('created_at')->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, DeclassementListResource::class));
    }

    /**
     * GET /api/v1/declassement-lists/{id}
     */
    public function show(DeclassementList $declassementList): JsonResponse
    {
        $this->authorize('view', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)
            ->with(['status', 'creator', 'records.record.activity', 'records.record.status', 'records.addedBy', 'comments.user', 'approvalRequestedBy', 'approvedBy', 'validatedBy', 'treatedBy'])
            ->findOrFail($declassementList->id);

        return response()->json(['data' => new DeclassementListResource($declassementList)]);
    }

    /**
     * POST /api/v1/declassement-lists
     */
    public function store(StoreDeclassementListRequest $request): JsonResponse
    {
        $this->authorize('create', DeclassementList::class);

        $declassementList = DeclassementList::create([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'declassement_status_id' => $this->statusByName('Brouillon')->id,
            'creator_id' => Auth::id(),
            'query_criteria' => $request->boolean('generate_from_query')
                ? ['sort_code' => 'E', 'activity_id' => $request->input('activity_id')]
                : null,
        ]);

        $recordIds = $request->input('record_ids', []);

        if ($request->boolean('generate_from_query')) {
            $recordIds = array_unique(array_merge(
                $recordIds,
                $this->eligibleQuery($request->input('activity_id'))->pluck('records.id')->all()
            ));
        }

        foreach ($recordIds as $recordId) {
            DeclassementRecord::firstOrCreate(
                ['declassement_list_id' => $declassementList->id, 'record_id' => $recordId],
                ['added_by' => Auth::id()]
            );
        }

        return response()->json(
            ['data' => new DeclassementListResource($declassementList->load('status', 'creator', 'records'))],
            201,
            ['Location' => "/api/v1/declassement-lists/{$declassementList->id}"]
        );
    }

    /**
     * PATCH /api/v1/declassement-lists/{id}
     */
    public function update(UpdateDeclassementListRequest $request, DeclassementList $declassementList): JsonResponse
    {
        $this->authorize('update', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($declassementList->id);

        if ($declassementList->is_approval_requested) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Impossible de modifier une liste déjà soumise pour approbation.'],
                422
            );
        }

        $declassementList->update($request->validated());

        return response()->json(['data' => new DeclassementListResource($declassementList->fresh())]);
    }

    /**
     * DELETE /api/v1/declassement-lists/{id}
     */
    public function destroy(DeclassementList $declassementList): JsonResponse|Response
    {
        $this->authorize('delete', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($declassementList->id);

        if ($declassementList->is_approval_requested) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Impossible de supprimer une liste déjà soumise pour approbation.'],
                422
            );
        }

        $declassementList->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/declassement-lists/eligible-records
     *
     * Records éliminables (sort E, durée de rétention écoulée, hors liste non traitée),
     * bornés à l'organisation courante.
     */
    public function eligibleRecords(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DeclassementList::class);

        $query = $this->eligibleQuery($request->input('activity_id') ? (int) $request->input('activity_id') : null);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, \App\Http\Resources\Api\V1\RecordLifecycleResource::class));
    }

    /**
     * POST /api/v1/declassement-lists/{id}/records
     */
    public function addRecords(Request $request, DeclassementList $declassementList): JsonResponse
    {
        $this->authorize('update', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($declassementList->id);

        if ($declassementList->is_approval_requested) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Impossible de modifier une liste déjà soumise pour approbation.'],
                422
            );
        }

        $request->validate([
            'record_ids' => 'required|array',
            'record_ids.*' => 'exists:records,id',
        ]);

        foreach ($request->input('record_ids') as $recordId) {
            DeclassementRecord::firstOrCreate(
                ['declassement_list_id' => $declassementList->id, 'record_id' => $recordId],
                ['added_by' => Auth::id()]
            );
        }

        return response()->json(['data' => new DeclassementListResource($declassementList->fresh()->load('records'))]);
    }

    /**
     * DELETE /api/v1/declassement-lists/{id}/records/{declassementRecord}
     */
    public function removeRecord(DeclassementList $declassementList, DeclassementRecord $declassementRecord): JsonResponse|Response
    {
        $this->authorize('update', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($declassementList->id);

        if ($declassementList->is_approval_requested) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Impossible de modifier une liste déjà soumise pour approbation.'],
                422
            );
        }

        abort_unless($declassementRecord->declassement_list_id === $declassementList->id, 404);

        $declassementRecord->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/declassement-lists/{id}/comments
     */
    public function comment(Request $request, DeclassementList $declassementList): JsonResponse
    {
        $this->authorize('view', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($declassementList->id);

        $request->validate(['content' => 'required|string']);

        $comment = DeclassementComment::create([
            'declassement_list_id' => $declassementList->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        return response()->json(['data' => [
            'id' => $comment->id,
            'declassement_list_id' => $comment->declassement_list_id,
            'user_id' => $comment->user_id,
            'content' => $comment->content,
            'created_at' => $comment->created_at?->toIso8601ZuluString(),
        ]], 201);
    }

    /**
     * POST /api/v1/declassement-lists/{id}/request-approval
     */
    public function requestApproval(Request $request, DeclassementList $declassementList): JsonResponse
    {
        $this->authorize('update', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($declassementList->id);

        if ($declassementList->records()->count() === 0) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'La liste ne contient aucun dossier.'],
                422
            );
        }

        $declassementList->update([
            'is_approval_requested' => true,
            'approval_requested_by' => Auth::id(),
            'approval_requested_date' => now(),
            'declassement_status_id' => $this->statusByName("Demande d'approbation soumise")->id,
        ]);

        return response()->json(['data' => new DeclassementListResource($declassementList->fresh())]);
    }

    /**
     * POST /api/v1/declassement-lists/{id}/approve
     */
    public function approve(Request $request, DeclassementList $declassementList): JsonResponse
    {
        $this->authorize('update', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($declassementList->id);

        if (!$declassementList->is_approval_requested) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => "Cette liste n'a pas encore été soumise pour approbation."],
                422
            );
        }

        $declassementList->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_date' => now(),
            'declassement_status_id' => $this->statusByName('Approuvé')->id,
        ]);

        return response()->json(['data' => new DeclassementListResource($declassementList->fresh())]);
    }

    /**
     * POST /api/v1/declassement-lists/{id}/validate
     */
    public function validateList(Request $request, DeclassementList $declassementList): JsonResponse
    {
        $this->authorize('update', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($declassementList->id);

        if (!$declassementList->is_approved) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => "Cette liste n'a pas encore été approuvée."],
                422
            );
        }

        $declassementList->update([
            'is_validated' => true,
            'validated_by' => Auth::id(),
            'validated_date' => now(),
            'declassement_status_id' => $this->statusByName('Validé')->id,
        ]);

        return response()->json(['data' => new DeclassementListResource($declassementList->fresh())]);
    }

    /**
     * POST /api/v1/declassement-lists/{id}/process
     */
    public function process(Request $request, DeclassementList $declassementList): JsonResponse
    {
        $this->authorize('update', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($declassementList->id);

        if (!$declassementList->is_validated) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => "Cette liste n'a pas encore été validée."],
                422
            );
        }

        $eliminatedStatus = RecordStatus::where('name', 'Éliminé')->first();

        foreach ($declassementList->records()->with('record')->get() as $declassementRecord) {
            if ($declassementRecord->record && $eliminatedStatus) {
                $declassementRecord->record->update(['status_id' => $eliminatedStatus->id]);
            }
        }

        $declassementList->update([
            'is_treated' => true,
            'treated_by' => Auth::id(),
            'treated_date' => now(),
            'declassement_status_id' => $this->statusByName('Traité')->id,
        ]);

        return response()->json(['data' => new DeclassementListResource($declassementList->fresh())]);
    }

    /**
     * POST /api/v1/declassement-lists/{id}/reject
     */
    public function reject(Request $request, DeclassementList $declassementList): JsonResponse
    {
        $this->authorize('update', $declassementList);

        $declassementList = DeclassementList::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($declassementList->id);

        $request->validate(['reason' => 'required|string']);

        $declassementList->update([
            'is_approval_requested' => false,
            'is_approved' => false,
            'is_validated' => false,
            'rejection_reason' => $request->input('reason'),
            'declassement_status_id' => $this->statusByName('Rejeté')->id,
        ]);

        DeclassementComment::create([
            'declassement_list_id' => $declassementList->id,
            'user_id' => Auth::id(),
            'content' => 'Rejet : ' . $request->input('reason'),
        ]);

        return response()->json(['data' => new DeclassementListResource($declassementList->fresh())]);
    }

    /**
     * Records éliminables — portage sur la table unifiée `records` de la requête
     * `DeclassementList::eligibleRecordsQuery()` (le Blade ciblait `record_physicals`).
     * Borné à l'organisation courante.
     */
    private function eligibleQuery(?int $activityId = null)
    {
        $referenceDate = "COALESCE(
            CASE
                WHEN records.date_format = 'Y' AND records.end_date REGEXP '^[0-9]{4}$' THEN MAKEDATE(records.end_date, 365)
                WHEN records.date_format = 'M' AND records.end_date REGEXP '^[0-9]{4}/[0-9]{1,2}$' THEN STR_TO_DATE(CONCAT(REPLACE(records.end_date, '/', '-'), '-01'), '%Y-%m-%d')
                WHEN records.date_format = 'D' AND records.end_date REGEXP '^[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}$' THEN STR_TO_DATE(REPLACE(records.end_date, '/', '-'), '%Y-%m-%d')
                ELSE NULL
            END,
            records.date_exact
        )";

        $query = Record::query()
            ->join('activities', 'records.activity_id', '=', 'activities.id')
            ->join('retention_activity', 'activities.id', '=', 'retention_activity.activity_id')
            ->join('retentions', 'retention_activity.retention_id', '=', 'retentions.id')
            ->join('sorts', 'retentions.sort_id', '=', 'sorts.id')
            ->where('records.organisation_id', Auth::user()->current_organisation_id)
            ->where('sorts.code', 'E')
            ->whereRaw("DATEDIFF(NOW(), {$referenceDate}) > retentions.duration * 365")
            ->whereDoesntHave('declassementRecords', function ($q) {
                $q->whereHas('declassementList', function ($listQuery) {
                    $listQuery->where('is_treated', false);
                });
            })
            ->select('records.*')
            ->with(['activity', 'status', 'level', 'creator']);

        if ($activityId) {
            $query->where('records.activity_id', $activityId);
        }

        return $query;
    }
}
