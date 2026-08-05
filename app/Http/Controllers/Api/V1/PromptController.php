<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Prompt\StorePromptRequest;
use App\Http\Requests\Api\V1\Prompt\UpdatePromptRequest;
use App\Http\Resources\Api\V1\PromptResource;
use App\Models\Prompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D14 — prompts (org-scopés, motif D03). Porté le 2026-08-04.
 *
 * Fusion des contrôleurs Blade `PromptController` (exécution IA) et
 * `PromptManagementController` (CRUD settings) : une seule ressource `prompts`.
 *
 * Isolation : un prompt est visible si système, s'il appartient à l'organisation
 * courante, ou s'il est personnel (R03 — 404, jamais 403). `organisation_id` et
 * `user_id` sont posés depuis l'agent authentifié.
 *
 * ⚠️ TODO (E2, phase 3) : `PromptController::actions()` (appel réel au LLM via
 * `PromptTransactionService`/`AiBridge`) n'est pas porté — appel IA, classe E2.
 * ⚠️ TODO sécurité : `is_system` reste accepté du client comme en Blade ; un prompt
 * système devrait être réservé aux administrateurs (Gate `system_updates_manage`).
 */
class PromptController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'title', 'is_system', 'organisation_id', 'user_id', 'prompt_category_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'title', 'is_system', 'organisation_id', 'user_id', 'prompt_category_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['user', 'organisation'];

    /**
     * GET /api/v1/prompts
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Prompt::class);

        $query = Prompt::visibleTo(Auth::user()->current_organisation_id, Auth::id());

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, PromptResource::class));
    }

    /**
     * GET /api/v1/prompts/{id}
     */
    public function show(Prompt $prompt, Request $request): JsonResponse
    {
        $this->authorize('view', $prompt);

        // Isolation R03 : un prompt hors visibilité (org, système, personnel) est 404.
        $prompt = Prompt::visibleTo(Auth::user()->current_organisation_id, Auth::id())->findOrFail($prompt->id);

        return response()->json(['data' => new PromptResource($prompt)]);
    }

    /**
     * POST /api/v1/prompts
     */
    public function store(StorePromptRequest $request): JsonResponse
    {
        $this->authorize('create', Prompt::class);

        $prompt = Prompt::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'is_system' => $request->boolean('is_system'),
            'organisation_id' => Auth::user()->current_organisation_id,
            'user_id' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new PromptResource($prompt)],
            201,
            ['Location' => "/api/v1/prompts/{$prompt->id}"]
        );
    }

    /**
     * PATCH /api/v1/prompts/{id}
     */
    public function update(UpdatePromptRequest $request, Prompt $prompt): JsonResponse
    {
        $this->authorize('update', $prompt);

        $prompt = Prompt::visibleTo(Auth::user()->current_organisation_id, Auth::id())->findOrFail($prompt->id);

        $prompt->update([
            'title' => $request->input('title', $prompt->title),
            'content' => $request->input('content', $prompt->content),
            'is_system' => $request->has('is_system') ? $request->boolean('is_system') : $prompt->is_system,
        ]);

        return response()->json(['data' => new PromptResource($prompt->fresh())]);
    }

    /**
     * DELETE /api/v1/prompts/{id}
     */
    public function destroy(Prompt $prompt): Response
    {
        $this->authorize('delete', $prompt);

        $prompt = Prompt::visibleTo(Auth::user()->current_organisation_id, Auth::id())->findOrFail($prompt->id);

        // TODO (E2, phase 3) : refuser la suppression si des transactions existent,
        // comme le fait le Blade (`PromptManagementController::destroy()`).
        $prompt->delete();

        return response()->noContent();
    }
}
