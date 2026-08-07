<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Batch\StoreBatchRequest;
use App\Http\Requests\Api\V1\Batch\UpdateBatchRequest;
use App\Http\Resources\Api\V1\BatchResource;
use App\Models\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D06 — relu et validé le 2026-08-04 contre `BatchController`, `BatchHandlerController`,
 * `BatchReceivedController`, `BatchSendController`, `BatchTransferController` et le schéma.
 *
 * Les parapheurs (batches) sont **org-scopés** via `organisation_holder_id` (R03) :
 * l'index ne renvoie que les parapheurs de l'organisation courante, et toute ressource
 * d'une autre organisation répond 404 (jamais 403). `organisation_holder_id` est posé
 * depuis l'agent authentifié, jamais accepté du client.
 *
 * Actions non-CRUD du Blade — TODO, workflows courrier multi-étapes non portés :
 *   - `BatchController::indexMail` / `storeMail` / `updateMail` / `destroyMail` /
 *     `getAvailableMails` : gestion des courriers d'un parapheur (attach/detach pivot
 *     `batch_mail`, contrôle de doublons, pagination JSON dédiée).
 *   - `BatchController::exportPdf` : export PDF (Barryvdh\DomPDF) — type « export ».
 *   - `BatchHandlerController::list` / `create` / `addItems` / `removeItems` /
 *     `deleteBatch` : parapheur AJAX transactionnel (les opérations CRUD équivalentes
 *     sont exposées par cette ressource ; l'attachement/détachement de courriers reste
 *     à porter par une sous-ressource `batches/{batch}/mails`).
 *   - `BatchTransferController::transferToBoxes` / `transferToDollies` : transfert d'un
 *     parapheur vers des contenants/chariots (pivot `mail_archives`, `dolly_mails`).
 *   - `BatchReceivedController` / `BatchSendController::store` : création d'une
 *     `BatchTransaction` + une `MailTransaction` par courrier du parapheur — ABANDONNÉE
 *     ici car la table `mail_transactions` est absente du schéma (voir `mail-transactions`).
 */
class BatchController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'organisation_holder_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'organisation_holder_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['mails', 'transactions', 'organisationHolder'];

    /**
     * GET /api/v1/batches
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Batch::class);

        $query = Batch::inOrganisation(Auth::user()->current_organisation_id)
            ->withCount('mails');

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, BatchResource::class));
    }

    /**
     * GET /api/v1/batches/{id}
     */
    public function show(Batch $batch, Request $request): JsonResponse
    {
        $this->authorize('view', $batch);

        // Isolation R03 : un parapheur hors de l'organisation courante est 404.
        $query = Batch::inOrganisation(Auth::user()->current_organisation_id)
            ->withCount('mails');

        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $batch = $query->findOrFail($batch->id);

        return response()->json(['data' => new BatchResource($batch)]);
    }

    /**
     * POST /api/v1/batches
     */
    public function store(StoreBatchRequest $request): JsonResponse
    {
        $this->authorize('create', Batch::class);

        $batch = Batch::create($request->validated() + [
            'organisation_holder_id' => Auth::user()->current_organisation_id,
        ]);

        return response()->json(
            ['data' => new BatchResource($batch)],
            201,
            ['Location' => "/api/v1/batches/{$batch->id}"]
        );
    }

    /**
     * PATCH /api/v1/batches/{id}
     */
    public function update(UpdateBatchRequest $request, Batch $batch): JsonResponse
    {
        $this->authorize('update', $batch);

        $batch = Batch::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($batch->id);

        $batch->update($request->validated());

        return response()->json(['data' => new BatchResource($batch->fresh())]);
    }

    /**
     * DELETE /api/v1/batches/{id}
     */
    public function destroy(Batch $batch): JsonResponse|Response
    {
        $this->authorize('delete', $batch);

        $batch = Batch::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($batch->id);

        // Reprise de la garde du Blade : un parapheur contenant des courriers ne peut
        // pas être supprimé (l'unicité de l'historique de transfert en dépend).
        if ($batch->mails()->exists()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit', 'status' => 409, 'detail' => 'Impossible de supprimer un parapheur contenant des courriers.'],
                409
            );
        }

        $batch->delete();

        return response()->noContent();
    }
}
