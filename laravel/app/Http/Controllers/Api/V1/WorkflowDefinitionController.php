<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WorkflowDefinition\StoreWorkflowDefinitionRequest;
use App\Http\Requests\Api\V1\WorkflowDefinition\UpdateWorkflowDefinitionRequest;
use App\Http\Resources\Api\V1\WorkflowDefinitionResource;
use App\Models\WorkflowDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D13 — définitions de workflow, relu le 2026-08-04 contre
 * `WorkflowDefinitionController` (Blade) et le schéma.
 *
 * Les définitions sont **org-scopées** (`organisation_id`, trait BelongsToOrganisation) :
 * motif D03 — index borné à l'organisation courante, ressource d'une autre
 * organisation en 404. `organisation_id`, `created_by`, `updated_by` et `version`
 * sont posés depuis l'agent / le serveur, jamais acceptés du client.
 * Actions BPMN portées : `storeConfiguration` et `updateConfiguration`.
 */
class WorkflowDefinitionController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'status', 'version', 'organisation_id', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'status', 'version', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['creator', 'updater', 'instances', 'transitions'];

    /**
     * GET /api/v1/workflow-definitions
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkflowDefinition::class);

        $query = WorkflowDefinition::byOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->orderByDesc('created_at')->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, WorkflowDefinitionResource::class));
    }

    /**
     * GET /api/v1/workflow-definitions/{id}
     */
    public function show(WorkflowDefinition $workflowDefinition): JsonResponse
    {
        $this->authorize('view', $workflowDefinition);

        $workflowDefinition = WorkflowDefinition::byOrganisation(Auth::user()->current_organisation_id)
            ->with(['instances', 'transitions', 'creator'])
            ->findOrFail($workflowDefinition->id);

        return response()->json(['data' => new WorkflowDefinitionResource($workflowDefinition)]);
    }

    /**
     * POST /api/v1/workflow-definitions
     */
    public function store(StoreWorkflowDefinitionRequest $request): JsonResponse
    {
        $this->authorize('create', WorkflowDefinition::class);

        // La version est incrémentée par nom, comme en Blade.
        $latestVersion = WorkflowDefinition::where('name', $request->input('name'))->max('version') ?? 0;

        $definition = WorkflowDefinition::create($request->validated() + [
            'version' => $latestVersion + 1,
            'organisation_id' => Auth::user()->current_organisation_id,
            'created_by' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new WorkflowDefinitionResource($definition->load('creator'))],
            201,
            ['Location' => "/api/v1/workflow-definitions/{$definition->id}"]
        );
    }

    /**
     * PATCH /api/v1/workflow-definitions/{id}
     */
    public function update(UpdateWorkflowDefinitionRequest $request, WorkflowDefinition $workflowDefinition): JsonResponse
    {
        $this->authorize('update', $workflowDefinition);

        $workflowDefinition = WorkflowDefinition::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workflowDefinition->id);

        $workflowDefinition->update($request->validated() + ['updated_by' => Auth::id()]);

        return response()->json(['data' => new WorkflowDefinitionResource($workflowDefinition->fresh())]);
    }

    /**
     * DELETE /api/v1/workflow-definitions/{id}
     */
    public function destroy(WorkflowDefinition $workflowDefinition): Response
    {
        $this->authorize('delete', $workflowDefinition);

        $workflowDefinition = WorkflowDefinition::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workflowDefinition->id);

        $workflowDefinition->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/workflow-definitions/{id}/configuration
     */
    public function storeConfiguration(Request $request, WorkflowDefinition $definition): JsonResponse
    {
        $this->authorize('update', $definition);

        $definition = WorkflowDefinition::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($definition->id);

        $request->validate(['bpmn_xml' => 'required|string']);

        $definition->update([
            'bpmn_xml' => $request->input('bpmn_xml'),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['data' => new WorkflowDefinitionResource($definition->fresh())]);
    }

    /**
     * PUT /api/v1/workflow-definitions/{id}/configuration
     */
    public function updateConfiguration(Request $request, WorkflowDefinition $definition): JsonResponse
    {
        return $this->storeConfiguration($request, $definition);
    }
}
