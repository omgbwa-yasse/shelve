<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProjectMilestone\StoreProjectMilestoneRequest;
use App\Http\Requests\Api\V1\ProjectMilestone\UpdateProjectMilestoneRequest;
use App\Http\Resources\Api\V1\ProjectMilestoneResource;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D17 — Jalons de projet. Toujours enfants d'un projet ; l'autorisation
 * délègue la vérification d'organisation/attachable à `ProjectPolicy`
 * (voir §5 du plan MS-Project-parity) et ajoute un contrôle de permission
 * dédié (`project_milestone_*`).
 */
class ProjectMilestoneController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        return response()->json(['data' => ProjectMilestoneResource::collection(
            $project->milestones()->withCount('deliverables')->get()
        )]);
    }

    public function store(StoreProjectMilestoneRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        abort_unless(Auth::user()->hasPermission('project_milestone_create'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        $milestone = $project->milestones()->create($request->validated() + ['created_by' => Auth::id()]);

        return response()->json(
            ['data' => new ProjectMilestoneResource($milestone->fresh())],
            201,
            ['Location' => "/api/v1/project-milestones/{$milestone->id}"]
        );
    }

    public function update(UpdateProjectMilestoneRequest $request, ProjectMilestone $projectMilestone): JsonResponse
    {
        $projectMilestone->load('project');
        $this->authorize('update', $projectMilestone->project);
        abort_unless(Auth::user()->hasPermission('project_milestone_update'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectMilestone->project_id);
        abort_unless($projectMilestone->project_id === $project->id, 404);

        $projectMilestone->update($request->validated());

        return response()->json(['data' => new ProjectMilestoneResource($projectMilestone->fresh())]);
    }

    public function destroy(ProjectMilestone $projectMilestone): Response
    {
        $projectMilestone->load('project');
        $this->authorize('update', $projectMilestone->project);
        abort_unless(Auth::user()->hasPermission('project_milestone_delete'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectMilestone->project_id);
        abort_unless($projectMilestone->project_id === $project->id, 404);

        $projectMilestone->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/project-milestones/{projectMilestone}/reach
     */
    public function reach(ProjectMilestone $projectMilestone): JsonResponse
    {
        $projectMilestone->load('project');
        $this->authorize('update', $projectMilestone->project);
        abort_unless(Auth::user()->hasPermission('project_milestone_update'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectMilestone->project_id);
        abort_unless($projectMilestone->project_id === $project->id, 404);

        $projectMilestone->reach();

        return response()->json(['data' => new ProjectMilestoneResource($projectMilestone->fresh())]);
    }
}
