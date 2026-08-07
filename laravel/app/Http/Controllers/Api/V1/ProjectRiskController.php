<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProjectRisk\StoreProjectRiskRequest;
use App\Http\Requests\Api\V1\ProjectRisk\UpdateProjectRiskRequest;
use App\Http\Resources\Api\V1\ProjectRiskResource;
use App\Models\Project;
use App\Models\ProjectRisk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Registre des risques projet — même patron que `ProjectMilestoneController` :
 * toujours enfant d'un projet, l'autorisation délègue à `ProjectPolicy` sur le
 * parent et ajoute un contrôle de permission dédié (`project_risk_*`).
 */
class ProjectRiskController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        return response()->json(['data' => ProjectRiskResource::collection(
            $project->risks()->with(['owner', 'task'])->orderByDesc('created_at')->get()
        )]);
    }

    public function store(StoreProjectRiskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        abort_unless(Auth::user()->hasPermission('project_risk_create'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        $risk = $project->risks()->create($request->validated() + ['created_by' => Auth::id()]);

        return response()->json(
            ['data' => new ProjectRiskResource($risk->fresh(['owner', 'task']))],
            201,
            ['Location' => "/api/v1/project-risks/{$risk->id}"]
        );
    }

    public function update(UpdateProjectRiskRequest $request, ProjectRisk $projectRisk): JsonResponse
    {
        $projectRisk->load('project');
        $this->authorize('update', $projectRisk->project);
        abort_unless(Auth::user()->hasPermission('project_risk_update'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectRisk->project_id);
        abort_unless($projectRisk->project_id === $project->id, 404);

        $projectRisk->update($request->validated());

        return response()->json(['data' => new ProjectRiskResource($projectRisk->fresh(['owner', 'task']))]);
    }

    public function destroy(ProjectRisk $projectRisk): Response
    {
        $projectRisk->load('project');
        $this->authorize('update', $projectRisk->project);
        abort_unless(Auth::user()->hasPermission('project_risk_delete'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectRisk->project_id);
        abort_unless($projectRisk->project_id === $project->id, 404);

        $projectRisk->delete();

        return response()->noContent();
    }

    /** POST /api/v1/project-risks/{projectRisk}/mitigate */
    public function mitigate(UpdateProjectRiskRequest $request, ProjectRisk $projectRisk): JsonResponse
    {
        $this->transition($projectRisk, 'project_risk_update');
        $projectRisk->mitigate($request->input('mitigation_plan'));

        return response()->json(['data' => new ProjectRiskResource($projectRisk->fresh(['owner', 'task']))]);
    }

    /** POST /api/v1/project-risks/{projectRisk}/close */
    public function close(ProjectRisk $projectRisk): JsonResponse
    {
        $this->transition($projectRisk, 'project_risk_update');
        $projectRisk->close();

        return response()->json(['data' => new ProjectRiskResource($projectRisk->fresh(['owner', 'task']))]);
    }

    /** POST /api/v1/project-risks/{projectRisk}/occur — le risque s'est matérialisé. */
    public function occur(ProjectRisk $projectRisk): JsonResponse
    {
        $this->transition($projectRisk, 'project_risk_update');
        $projectRisk->markOccurred();

        return response()->json(['data' => new ProjectRiskResource($projectRisk->fresh(['owner', 'task']))]);
    }

    private function transition(ProjectRisk $projectRisk, string $permission): void
    {
        $projectRisk->load('project');
        $this->authorize('update', $projectRisk->project);
        abort_unless(Auth::user()->hasPermission($permission), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectRisk->project_id);
        abort_unless($projectRisk->project_id === $project->id, 404);
    }
}
