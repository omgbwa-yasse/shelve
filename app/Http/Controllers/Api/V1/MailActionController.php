<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MailAction\StoreMailActionRequest;
use App\Http\Requests\Api\V1\MailAction\UpdateMailActionRequest;
use App\Http\Resources\Api\V1\MailActionResource;
use App\Models\MailAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D06 — relu et validé le 2026-08-04 contre `MailActionController` et le schéma.
 *
 * Les actions de courrier (refertoir `mail_actions`) sont des référentiels globaux,
 * partagés entre organisations (motif D01) : pas de portée organisation, pas de champ
 * `creator_id` (la table n'en possède pas). La seule donnée gérée côté serveur est
 * l'unicité du nom, portée par la règle `unique` (Store) / `Rule::unique()->ignore`
 * (Update).
 */
class MailActionController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'duration', 'to_return', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'duration', 'to_return', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['mails'];

    /**
     * GET /api/v1/mail-actions
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MailAction::class);

        $query = MailAction::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, MailActionResource::class));
    }

    /**
     * GET /api/v1/mail-actions/{id}
     */
    public function show(MailAction $mailAction): JsonResponse
    {
        $this->authorize('view', $mailAction);

        return response()->json(['data' => new MailActionResource($mailAction)]);
    }

    /**
     * POST /api/v1/mail-actions
     */
    public function store(StoreMailActionRequest $request): JsonResponse
    {
        $this->authorize('create', MailAction::class);

        $mailAction = MailAction::create($request->validated());

        return response()->json(
            ['data' => new MailActionResource($mailAction)],
            201,
            ['Location' => "/api/v1/mail-actions/{$mailAction->id}"]
        );
    }

    /**
     * PATCH /api/v1/mail-actions/{id}
     */
    public function update(UpdateMailActionRequest $request, MailAction $mailAction): JsonResponse
    {
        $this->authorize('update', $mailAction);

        $mailAction->update($request->validated());

        return response()->json(['data' => new MailActionResource($mailAction->fresh())]);
    }

    /**
     * DELETE /api/v1/mail-actions/{id}
     */
    public function destroy(MailAction $mailAction): Response
    {
        $this->authorize('delete', $mailAction);

        $mailAction->delete();

        return response()->noContent();
    }
}
