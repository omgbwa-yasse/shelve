<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProjectStatusReport\StoreProjectStatusReportRequest;
use App\Http\Resources\Api\V1\ProjectStatusReportResource;
use App\Models\Project;
use App\Models\ProjectStatusReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D17 — Rapports d'étape (append-only : pas d'update, un rapport erroné se
 * corrige en en soumettant un nouveau).
 */
class ProjectStatusReportController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        return response()->json(['data' => ProjectStatusReportResource::collection($project->statusReports()->get())]);
    }

    public function store(StoreProjectStatusReportRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        abort_unless(Auth::user()->hasPermission('project_status_report_create'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($project->id);

        $report = $project->statusReports()->create($request->validated() + [
            'reported_at' => $request->input('reported_at', now()->toDateString()),
            'created_by' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new ProjectStatusReportResource($report)],
            201,
            ['Location' => "/api/v1/project-status-reports/{$report->id}"]
        );
    }

    public function destroy(ProjectStatusReport $projectStatusReport): Response
    {
        $projectStatusReport->load('project');
        $this->authorize('update', $projectStatusReport->project);
        abort_unless(Auth::user()->hasPermission('project_status_report_delete'), 403);

        $project = Project::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($projectStatusReport->project_id);
        abort_unless($projectStatusReport->project_id === $project->id, 404);

        $projectStatusReport->delete();

        return response()->noContent();
    }
}
