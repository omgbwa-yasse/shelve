<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WorkflowInstance\StoreWorkflowInstanceRequest;
use App\Http\Resources\Api\V1\WorkflowInstanceResource;
use App\Models\WorkflowInstance;
use App\Services\WorkflowEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D13 — instances de workflow, relu le 2026-08-04 contre
 * `WorkflowInstanceController` (Blade) et le schéma.
 *
 * Les instances sont **org-scopées** (`organisation_id`, trait BelongsToOrganisation) :
 * motif D03 — index borné à l'organisation courante, ressource d'une autre
 * organisation en 404. Pas de `update` (le Blade n'en expose pas : l'évolution d'une
 * instance passe par les actions start/pause/resume/cancel, portées ici via
 * WorkflowEngine). `organisation_id`, `started_by` et le statut initial sont posés
 * depuis l'agent / le serveur.
 */
class WorkflowInstanceController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'definition_id', 'name', 'status', 'organisation_id', 'started_by', 'updated_by', 'completed_by', 'started_at', 'completed_at'];
    private const SORTABLE = ['id', 'definition_id', 'name', 'status', 'started_at', 'completed_at'];
    private const INCLUDABLE = ['definition', 'starter', 'updater', 'completer', 'tasks'];

    public function __construct(private readonly WorkflowEngine $workflowEngine)
    {
    }

    /**
     * GET /api/v1/workflow-instances
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkflowInstance::class);

        $query = WorkflowInstance::byOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->orderByDesc('started_at')->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, WorkflowInstanceResource::class));
    }

    /**
     * GET /api/v1/workflow-instances/{id}
     */
    public function show(WorkflowInstance $workflowInstance): JsonResponse
    {
        $this->authorize('view', $workflowInstance);

        $workflowInstance = WorkflowInstance::byOrganisation(Auth::user()->current_organisation_id)
            ->with(['definition', 'tasks', 'starter'])
            ->findOrFail($workflowInstance->id);

        return response()->json(['data' => new WorkflowInstanceResource($workflowInstance)]);
    }

    /**
     * POST /api/v1/workflow-instances
     */
    public function store(StoreWorkflowInstanceRequest $request): JsonResponse
    {
        $this->authorize('create', WorkflowInstance::class);

        $instance = WorkflowInstance::create($request->validated() + [
            'status' => 'running',
            'current_state' => [],
            'organisation_id' => Auth::user()->current_organisation_id,
            'started_by' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new WorkflowInstanceResource($instance->load('definition', 'starter'))],
            201,
            ['Location' => "/api/v1/workflow-instances/{$instance->id}"]
        );
    }

    /**
     * DELETE /api/v1/workflow-instances/{id}
     */
    public function destroy(WorkflowInstance $workflowInstance): Response
    {
        $this->authorize('delete', $workflowInstance);

        $workflowInstance = WorkflowInstance::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workflowInstance->id);

        $workflowInstance->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/workflow-instances/{id}/start
     */
    public function start(WorkflowInstance $instance): JsonResponse
    {
        $this->authorize('update', $instance);

        $instance = WorkflowInstance::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($instance->id);

        try {
            $this->workflowEngine->startWorkflow($instance);
        } catch (\Exception $e) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Workflow', 'status' => 422, 'detail' => 'Échec du démarrage : ' . $e->getMessage()],
                422
            );
        }

        return response()->json(['data' => new WorkflowInstanceResource($instance->fresh())]);
    }

    /**
     * POST /api/v1/workflow-instances/{id}/pause
     */
    public function pause(WorkflowInstance $instance): JsonResponse
    {
        $this->authorize('update', $instance);

        $instance = WorkflowInstance::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($instance->id);

        $this->workflowEngine->pauseWorkflow($instance);

        return response()->json(['data' => new WorkflowInstanceResource($instance->fresh())]);
    }

    /**
     * POST /api/v1/workflow-instances/{id}/resume
     */
    public function resume(WorkflowInstance $instance): JsonResponse
    {
        $this->authorize('update', $instance);

        $instance = WorkflowInstance::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($instance->id);

        $this->workflowEngine->resumeWorkflow($instance);

        return response()->json(['data' => new WorkflowInstanceResource($instance->fresh())]);
    }

    /**
     * POST /api/v1/workflow-instances/{id}/cancel
     */
    public function cancel(WorkflowInstance $instance): JsonResponse
    {
        $this->authorize('update', $instance);

        $instance = WorkflowInstance::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($instance->id);

        $this->workflowEngine->cancelWorkflow($instance);

        return response()->json(['data' => new WorkflowInstanceResource($instance->fresh())]);
    }
}
