<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WorkplaceTemplate\StoreWorkplaceTemplateRequest;
use App\Http\Requests\Api\V1\WorkplaceTemplate\UpdateWorkplaceTemplateRequest;
use App\Http\Resources\Api\V1\WorkplaceTemplateResource;
use App\Models\WorkplaceTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D12 — modèles d'espace de travail, référentiel global (aucune organisation).
 *
 * Relevé contre `WorkplaceTemplateController` (relu le 2026-08-04). `code`,
 * `is_active`, `is_system`, `created_by` posés côté serveur ; les `default_structure`
 * / `default_settings` sont décodés du JSON du formulaire (comme en Blade).
 * Les modèles système sont protégés en update/destroy (403).
 */
class WorkplaceTemplateController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'category', 'is_active', 'is_system', 'usage_count', 'display_order', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'category', 'is_active', 'is_system', 'usage_count', 'display_order', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['creator'];

    /**
     * GET /api/v1/workplace-templates
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkplaceTemplate::class);

        $query = WorkplaceTemplate::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'display_order');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, WorkplaceTemplateResource::class));
    }

    /**
     * GET /api/v1/workplace-templates/{id}
     */
    public function show(WorkplaceTemplate $workplaceTemplate): JsonResponse
    {
        $this->authorize('view', $workplaceTemplate);

        return response()->json(['data' => new WorkplaceTemplateResource($workplaceTemplate)]);
    }

    /**
     * POST /api/v1/workplace-templates
     */
    public function store(StoreWorkplaceTemplateRequest $request): JsonResponse
    {
        $this->authorize('create', WorkplaceTemplate::class);

        $data = $request->validated();

        $workplaceTemplate = WorkplaceTemplate::create([
            ...$data,
            'code' => 'TPL-' . strtoupper(uniqid()),
            'created_by' => Auth::id(),
            'is_active' => true,
            'is_system' => false,
            'default_structure' => isset($data['default_structure']) ? json_decode($data['default_structure'], true) : [],
            'default_settings' => isset($data['default_settings']) ? json_decode($data['default_settings'], true) : [],
        ]);

        return response()->json(
            ['data' => new WorkplaceTemplateResource($workplaceTemplate)],
            201,
            ['Location' => "/api/v1/workplace-templates/{$workplaceTemplate->id}"]
        );
    }

    /**
     * PATCH /api/v1/workplace-templates/{id}
     */
    public function update(UpdateWorkplaceTemplateRequest $request, WorkplaceTemplate $workplaceTemplate): JsonResponse
    {
        $this->authorize('update', $workplaceTemplate);

        if ($workplaceTemplate->is_system) {
            abort(403, 'Impossible de modifier un modèle système.');
        }

        $data = $request->validated();

        $workplaceTemplate->update([
            ...$data,
            'default_structure' => isset($data['default_structure']) ? json_decode($data['default_structure'], true) : $workplaceTemplate->default_structure,
            'default_settings' => isset($data['default_settings']) ? json_decode($data['default_settings'], true) : $workplaceTemplate->default_settings,
        ]);

        return response()->json(['data' => new WorkplaceTemplateResource($workplaceTemplate->fresh())]);
    }

    /**
     * DELETE /api/v1/workplace-templates/{id}
     */
    public function destroy(WorkplaceTemplate $workplaceTemplate): Response
    {
        $this->authorize('delete', $workplaceTemplate);

        if ($workplaceTemplate->is_system) {
            abort(403, 'Impossible de supprimer un modèle système.');
        }

        $workplaceTemplate->delete();

        return response()->noContent();
    }
}
