<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProjectResource\StoreProjectResourceRequest;
use App\Http\Requests\Api\V1\ProjectResource\UpdateProjectResourceRequest;
use App\Http\Resources\Api\V1\ProjectResourceResource;
use App\Models\Project;
use App\Models\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D17 — Ressources de projet (humaine, financière, matérielle,
 * informationnelle — table unique à discriminant `type`, voir
 * `App\Models\ProjectResource`).
 */
class ProjectResourceController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        return response()->json(['data' => ProjectResourceResource::collection($project->resources()->get())]);
    }

    public function store(StoreProjectResourceRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        abort_unless(Auth::user()->hasPermission('project_resource_create'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        $resource = $project->resources()->create($request->validated() + ['created_by' => Auth::id()]);

        return response()->json(
            ['data' => new ProjectResourceResource($resource)],
            201,
            ['Location' => "/api/v1/project-resources/{$resource->id}"]
        );
    }

    public function update(UpdateProjectResourceRequest $request, ProjectResource $projectResource): JsonResponse
    {
        $projectResource->load('project');
        $this->authorize('update', $projectResource->project);
        abort_unless(Auth::user()->hasPermission('project_resource_update'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectResource->project_id);
        abort_unless($projectResource->project_id === $project->id, 404);

        $projectResource->update($request->validated());

        return response()->json(['data' => new ProjectResourceResource($projectResource->fresh())]);
    }

    public function destroy(ProjectResource $projectResource): Response
    {
        $projectResource->load('project');
        $this->authorize('update', $projectResource->project);
        abort_unless(Auth::user()->hasPermission('project_resource_delete'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectResource->project_id);
        abort_unless($projectResource->project_id === $project->id, 404);

        $projectResource->delete();

        return response()->noContent();
    }
}
