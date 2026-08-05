<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BatchTransaction\UpdateBatchTransactionRequest;
use App\Http\Resources\Api\V1\BatchTransactionResource;
use App\Models\BatchTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D06 — relu et validé le 2026-08-04 contre `BatchReceivedController`, `BatchSendController`
 * et le schéma.
 *
 * Les transactions de parapheur (`batch_transactions`, envoi/réception d'un parapheur
 * entre deux organisations) sont **org-scopées** (R03) par la double colonne
 * `organisation_send_id` / `organisation_received_id` : l'index renvoie les transactions
 * dont l'organisation courante est émettrice ou réceptrice, et toute ressource hors
 * périmètre répond 404 (jamais 403).
 *
 * Les deux contrôleurs Blade (`/mails/batch-received` et `/mails/batch-send`) sont
 * fusionnés en une seule ressource : ils ne diffèrent que par le rôle de l'organisation
 * courante (réceptrice ou émettrice), que l'on peut isoler par filtre
 * (`?filter[organisation_received_id]=` / `?filter[organisation_send_id]=`).
 *
 * TODO / abandon partiel — le **store** (réception ou envoi d'un parapheur) est non porté :
 * il crée une `BatchTransaction` PUIS une `MailTransaction` par courrier du parapheur,
 * or la table `mail_transactions` est **absente du schéma** — vérifié le 2026-08-05 dans
 * `database/schema/baseline-schema.sql` (seules `batch_transactions`, `batches` et le pivot
 * `dolly_mail_transactions` existent ; le modèle `MailTransaction` ne pointe vers aucune
 * table). Dette de schéma : recréer la table `mail_transactions` (décision métier requise)
 * avant tout portage du store. Le reste du workflow (index/show/update/destroy sur
 * `batch_transactions`) est porté.
 * `organisation_send_id` / `organisation_received_id` sont gérés côté serveur (l'organisation
 * courante du rôle concerné), jamais acceptés du client : seul `batch_id` est modifiable.
 */
class BatchTransactionController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'batch_id', 'organisation_send_id', 'organisation_received_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'batch_id', 'organisation_send_id', 'organisation_received_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['batch', 'organisationSend', 'organisationReceived', 'mails'];

    /**
     * GET /api/v1/batch-transactions
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BatchTransaction::class);

        $query = BatchTransaction::inOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, BatchTransactionResource::class));
    }

    /**
     * GET /api/v1/batch-transactions/{id}
     */
    public function show(BatchTransaction $batchTransaction, Request $request): JsonResponse
    {
        $this->authorize('view', $batchTransaction);

        // Isolation R03 : une transaction hors de l'organisation courante est 404.
        $query = BatchTransaction::inOrganisation(Auth::user()->current_organisation_id);

        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $batchTransaction = $query->findOrFail($batchTransaction->id);

        return response()->json(['data' => new BatchTransactionResource($batchTransaction)]);
    }

    /**
     * POST /api/v1/batch-transactions
     *
     * NON PORTÉ (501) — la réception/envoi d'un parapheur crée une `MailTransaction` par
     * courrier, et la table `mail_transactions` est absente du schéma (vérifié le 2026-08-05,
     * voir l'en-tête de ce contrôleur). Décision de schéma requise avant portage.
     */
    public function store(): JsonResponse
    {
        abort(501, 'La réception/envoi de parapheur n\'est pas encore exposé par l\'API v1.');
    }

    /**
     * PATCH /api/v1/batch-transactions/{id}
     */
    public function update(UpdateBatchTransactionRequest $request, BatchTransaction $batchTransaction): JsonResponse
    {
        $this->authorize('update', $batchTransaction);

        $batchTransaction = BatchTransaction::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($batchTransaction->id);

        $batchTransaction->update($request->validated());

        return response()->json(['data' => new BatchTransactionResource($batchTransaction->fresh())]);
    }

    /**
     * DELETE /api/v1/batch-transactions/{id}
     */
    public function destroy(BatchTransaction $batchTransaction): Response
    {
        $this->authorize('delete', $batchTransaction);

        $batchTransaction = BatchTransaction::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($batchTransaction->id);

        $batchTransaction->delete();

        return response()->noContent();
    }
}
