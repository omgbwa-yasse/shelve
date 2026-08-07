<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MailPriority\StoreMailPriorityRequest;
use App\Http\Requests\Api\V1\MailPriority\UpdateMailPriorityRequest;
use App\Http\Resources\Api\V1\MailPriorityResource;
use App\Models\MailPriority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D06 — relu et validé le 2026-08-04 contre `MailPriorityController` et le schéma.
 *
 * Les priorités de courrier (`mail_priorities`) sont des référentiels globaux,
 * partagés entre organisations (motif D01) : pas de portée organisation, pas de champ
 * `creator_id`. L'unicité du nom est portée par les règles `unique` / `Rule::unique()->ignore`.
 */
class MailPriorityController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'duration', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'duration', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['mails'];

    /**
     * GET /api/v1/mail-priorities
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MailPriority::class);

        $query = MailPriority::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, MailPriorityResource::class));
    }

    /**
     * GET /api/v1/mail-priorities/{id}
     */
    public function show(MailPriority $mailPriority): JsonResponse
    {
        $this->authorize('view', $mailPriority);

        return response()->json(['data' => new MailPriorityResource($mailPriority)]);
    }

    /**
     * POST /api/v1/mail-priorities
     */
    public function store(StoreMailPriorityRequest $request): JsonResponse
    {
        $this->authorize('create', MailPriority::class);

        $mailPriority = MailPriority::create($request->validated());

        return response()->json(
            ['data' => new MailPriorityResource($mailPriority)],
            201,
            ['Location' => "/api/v1/mail-priorities/{$mailPriority->id}"]
        );
    }

    /**
     * PATCH /api/v1/mail-priorities/{id}
     */
    public function update(UpdateMailPriorityRequest $request, MailPriority $mailPriority): JsonResponse
    {
        $this->authorize('update', $mailPriority);

        $mailPriority->update($request->validated());

        return response()->json(['data' => new MailPriorityResource($mailPriority->fresh())]);
    }

    /**
     * DELETE /api/v1/mail-priorities/{id}
     */
    public function destroy(MailPriority $mailPriority): Response
    {
        $this->authorize('delete', $mailPriority);

        $mailPriority->delete();

        return response()->noContent();
    }
}
