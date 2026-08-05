<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AiSkill\StoreAiSkillRequest;
use App\Http\Requests\Api\V1\AiSkill\UpdateAiSkillRequest;
use App\Http\Resources\Api\V1\AiSkillResource;
use App\Models\AiSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D14 — compétences IA (référentiel global, système + customs). Porté le 2026-08-04.
 *
 * `installed_by` est posé depuis l'agent authentifié. L'action `toggle` du Blade
 * (`enabled` inversé) est portée telle quelle : c'est une simple mise à jour.
 *
 * ⚠️ TODO (E2, phase 3) : `install` du Blade (upload d'un ZIP puis `AiSkillService::installFromZip()`)
 * n'est pas porté — manipulation de fichiers disque, à traiter en phase 3.
 */
class AiSkillController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'slug', 'name', 'version', 'location', 'enabled', 'installed_by', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'slug', 'name', 'version', 'location', 'enabled', 'installed_by', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['installer'];

    /**
     * GET /api/v1/ai-skills
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AiSkill::class);

        $query = AiSkill::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, AiSkillResource::class));
    }

    /**
     * GET /api/v1/ai-skills/{id}
     */
    public function show(AiSkill $aiSkill): JsonResponse
    {
        $this->authorize('view', $aiSkill);

        return response()->json(['data' => new AiSkillResource($aiSkill)]);
    }

    /**
     * POST /api/v1/ai-skills
     */
    public function store(StoreAiSkillRequest $request): JsonResponse
    {
        $this->authorize('create', AiSkill::class);

        $aiSkill = AiSkill::create($request->validated() + ['installed_by' => Auth::id()]);

        return response()->json(
            ['data' => new AiSkillResource($aiSkill)],
            201,
            ['Location' => "/api/v1/ai-skills/{$aiSkill->id}"]
        );
    }

    /**
     * PATCH /api/v1/ai-skills/{id}
     */
    public function update(UpdateAiSkillRequest $request, AiSkill $aiSkill): JsonResponse
    {
        $this->authorize('update', $aiSkill);

        $aiSkill->update($request->validated());

        return response()->json(['data' => new AiSkillResource($aiSkill->fresh())]);
    }

    /**
     * POST /api/v1/ai-skills/{aiSkill}/toggle — activer/désactiver (porté du Blade).
     */
    public function toggle(AiSkill $aiSkill): JsonResponse
    {
        $this->authorize('update', $aiSkill);

        $aiSkill->update(['enabled' => !$aiSkill->enabled]);

        return response()->json(['data' => new AiSkillResource($aiSkill->fresh())]);
    }

    /**
     * DELETE /api/v1/ai-skills/{id}
     */
    public function destroy(AiSkill $aiSkill): Response
    {
        $this->authorize('delete', $aiSkill);

        // TODO (E2, phase 3) : suppression du dossier disque via `AiSkillService::delete()`,
        // comme le fait le Blade — ici suppression de l'enregistrement uniquement.
        $aiSkill->delete();

        return response()->noContent();
    }
}
