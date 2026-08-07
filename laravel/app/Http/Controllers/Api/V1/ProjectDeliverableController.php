<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProjectDeliverable\StoreProjectDeliverableRequest;
use App\Http\Requests\Api\V1\ProjectDeliverable\UpdateProjectDeliverableRequest;
use App\Http\Resources\Api\V1\ProjectDeliverableResource;
use App\Models\Project;
use App\Models\ProjectDeliverable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D17 — Livrables de projet (avec pièce jointe optionnelle via `Attachment`
 * existant — voir §5 du plan MS-Project-parity). Cycle de vie
 * draft → submitted → approved|rejected.
 */
class ProjectDeliverableController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        return response()->json(['data' => ProjectDeliverableResource::collection($project->deliverables()->get())]);
    }

    public function store(StoreProjectDeliverableRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        abort_unless(Auth::user()->hasPermission('project_deliverable_create'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        $deliverable = $project->deliverables()->create($request->validated() + ['created_by' => Auth::id()]);

        return response()->json(
            ['data' => new ProjectDeliverableResource($deliverable->fresh())],
            201,
            ['Location' => "/api/v1/project-deliverables/{$deliverable->id}"]
        );
    }

    public function update(UpdateProjectDeliverableRequest $request, ProjectDeliverable $projectDeliverable): JsonResponse
    {
        $projectDeliverable->load('project');
        $this->authorize('update', $projectDeliverable->project);
        abort_unless(Auth::user()->hasPermission('project_deliverable_update'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectDeliverable->project_id);
        abort_unless($projectDeliverable->project_id === $project->id, 404);

        $projectDeliverable->update($request->validated());

        return response()->json(['data' => new ProjectDeliverableResource($projectDeliverable->fresh())]);
    }

    public function destroy(ProjectDeliverable $projectDeliverable): Response
    {
        $projectDeliverable->load('project');
        $this->authorize('update', $projectDeliverable->project);
        abort_unless(Auth::user()->hasPermission('project_deliverable_delete'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectDeliverable->project_id);
        abort_unless($projectDeliverable->project_id === $project->id, 404);

        $projectDeliverable->delete();

        return response()->noContent();
    }

    private function authorizeAndFind(ProjectDeliverable $projectDeliverable): ProjectDeliverable
    {
        $projectDeliverable->load('project');
        $this->authorize('update', $projectDeliverable->project);
        abort_unless(Auth::user()->hasPermission('project_deliverable_update'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectDeliverable->project_id);
        abort_unless($projectDeliverable->project_id === $project->id, 404);

        return $projectDeliverable;
    }

    /**
     * POST /api/v1/project-deliverables/{projectDeliverable}/submit
     */
    public function submit(ProjectDeliverable $projectDeliverable): JsonResponse
    {
        $projectDeliverable = $this->authorizeAndFind($projectDeliverable);

        $projectDeliverable->submit(Auth::id());

        return response()->json(['data' => new ProjectDeliverableResource($projectDeliverable->fresh())]);
    }

    /**
     * POST /api/v1/project-deliverables/{projectDeliverable}/approve
     */
    public function approve(ProjectDeliverable $projectDeliverable): JsonResponse
    {
        $projectDeliverable = $this->authorizeAndFind($projectDeliverable);

        $projectDeliverable->approve(Auth::id());

        return response()->json(['data' => new ProjectDeliverableResource($projectDeliverable->fresh())]);
    }

    /**
     * POST /api/v1/project-deliverables/{projectDeliverable}/reject
     */
    public function reject(ProjectDeliverable $projectDeliverable): JsonResponse
    {
        $projectDeliverable = $this->authorizeAndFind($projectDeliverable);

        $projectDeliverable->reject();

        return response()->json(['data' => new ProjectDeliverableResource($projectDeliverable->fresh())]);
    }
}
