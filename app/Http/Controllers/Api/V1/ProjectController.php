<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Project\StoreProjectRequest;
use App\Http\Requests\Api\V1\Project\UpdateProjectRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Http\Resources\Api\V1\TaskResource;
use App\Models\Project;
use App\Services\ProjectAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D17 — Projets (module Projet/Tâche/OKR/KPI, voir
 * `evolution/PROJECT-OKR-KPI-PLAN.md`). Org-scopé (`organisation_id`, trait
 * BelongsToOrganisation) — motif D03 : index borné à l'organisation courante,
 * ressource d'une autre organisation en 404. `attachable_type` est traduit
 * depuis son alias court avant écriture (voir App\Traits\HasAttachable).
 */
class ProjectController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'status', 'attachable_type', 'attachable_id', 'organisation_id', 'owner_id', 'created_at'];
    private const SORTABLE = ['id', 'code', 'name', 'status', 'start_date', 'end_date', 'created_at'];
    private const INCLUDABLE = ['owner', 'creator', 'updater', 'attachable', 'objectives', 'tasks'];

    /**
     * GET /api/v1/projects
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $query = Project::byOrganisation(Auth::user()->current_organisation_id)
            ->withCount(['objectives', 'tasks']);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->orderByDesc('created_at')->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ProjectResource::class));
    }

    /**
     * GET /api/v1/projects/{id}
     */
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)
            ->withCount(['objectives', 'tasks'])
            ->with(['owner', 'creator', 'attachable'])
            ->findOrFail($project->id);

        return response()->json(['data' => new ProjectResource($project)]);
    }

    /**
     * POST /api/v1/projects
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $data = $request->validated();
        $data['attachable_type'] = Project::resolveAttachableAlias($data['attachable_type']);

        $project = Project::create($data + [
            'organisation_id' => Auth::user()->current_organisation_id,
            'created_by' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new ProjectResource($project->load('attachable'))],
            201,
            ['Location' => "/api/v1/projects/{$project->id}"]
        );
    }

    /**
     * PATCH /api/v1/projects/{id}
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        $data = $request->validated();

        if (isset($data['attachable_type'])) {
            $data['attachable_type'] = Project::resolveAttachableAlias($data['attachable_type']);
        }

        $project->update($data + ['updated_by' => Auth::id()]);

        return response()->json(['data' => new ProjectResource($project->fresh('attachable'))]);
    }

    /**
     * DELETE /api/v1/projects/{id}
     */
    public function destroy(Project $project): Response
    {
        $this->authorize('delete', $project);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        $project->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/projects/{project}/tasks — délègue à `Task.taskable`
     * (voir `evolution/PROJECT-OKR-KPI-PLAN.md`, §3), sans exposer le FQCN
     * `Project::class` au client.
     */
    public function tasks(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        return response()->json(['data' => TaskResource::collection($project->tasks()->get())]);
    }

    /**
     * GET /api/v1/projects/{project}/alerts — alertes calculées à la lecture
     * (retards, jalons/livrables à échéance, dépassements budgétaires),
     * voir `App\Services\ProjectAlertService`.
     */
    public function alerts(Project $project, ProjectAlertService $alertService): JsonResponse
    {
        $this->authorize('view', $project);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        return response()->json(['data' => $alertService->forProject($project)]);
    }
}
