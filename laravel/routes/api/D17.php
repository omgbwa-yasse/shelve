<?php

// Routes API v1 du domaine D17 (Projets / Tâches / OKR / KPI) — voir
// evolution/PROJECT-OKR-KPI-PLAN.md. Inclus dans le groupe api.v1.
// (auth:sanctum + rate.limit) de routes/api.php.

use App\Http\Controllers\Api\V1\KeyResultController;
use App\Http\Controllers\Api\V1\KpiController;
use App\Http\Controllers\Api\V1\KpiMeasurementController;
use App\Http\Controllers\Api\V1\ObjectiveController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectDeliverableController;
use App\Http\Controllers\Api\V1\ProjectMilestoneController;
use App\Http\Controllers\Api\V1\ProjectResourceController;
use App\Http\Controllers\Api\V1\ProjectRiskController;
use App\Http\Controllers\Api\V1\ProjectStatusReportController;
use App\Http\Controllers\Api\V1\TaskDependencyController;
use Illuminate\Support\Facades\Route;

Route::apiResource('projects', ProjectController::class)->except(['create', 'edit']);
Route::get('projects/{project}/tasks', [ProjectController::class, 'tasks'])->name('projects.tasks');
Route::get('projects/{project}/alerts', [ProjectController::class, 'alerts'])->name('projects.alerts');

// Jalons (MS-Project-parity — voir evolution/PROJECT-OKR-KPI-PLAN.md extension)
Route::get('projects/{project}/milestones', [ProjectMilestoneController::class, 'index'])->name('projects.milestones.index');
Route::post('projects/{project}/milestones', [ProjectMilestoneController::class, 'store'])->name('projects.milestones.store');
Route::match(['put', 'patch'], 'project-milestones/{projectMilestone}', [ProjectMilestoneController::class, 'update'])->name('project-milestones.update');
Route::delete('project-milestones/{projectMilestone}', [ProjectMilestoneController::class, 'destroy'])->name('project-milestones.destroy');
Route::post('project-milestones/{projectMilestone}/reach', [ProjectMilestoneController::class, 'reach'])->name('project-milestones.reach');

// Livrables
Route::get('projects/{project}/deliverables', [ProjectDeliverableController::class, 'index'])->name('projects.deliverables.index');
Route::post('projects/{project}/deliverables', [ProjectDeliverableController::class, 'store'])->name('projects.deliverables.store');
Route::match(['put', 'patch'], 'project-deliverables/{projectDeliverable}', [ProjectDeliverableController::class, 'update'])->name('project-deliverables.update');
Route::delete('project-deliverables/{projectDeliverable}', [ProjectDeliverableController::class, 'destroy'])->name('project-deliverables.destroy');
Route::post('project-deliverables/{projectDeliverable}/submit', [ProjectDeliverableController::class, 'submit'])->name('project-deliverables.submit');
Route::post('project-deliverables/{projectDeliverable}/approve', [ProjectDeliverableController::class, 'approve'])->name('project-deliverables.approve');
Route::post('project-deliverables/{projectDeliverable}/reject', [ProjectDeliverableController::class, 'reject'])->name('project-deliverables.reject');

// Ressources (humaine, financière, matérielle, informationnelle)
Route::get('projects/{project}/resources', [ProjectResourceController::class, 'index'])->name('projects.resources.index');
Route::post('projects/{project}/resources', [ProjectResourceController::class, 'store'])->name('projects.resources.store');
Route::match(['put', 'patch'], 'project-resources/{projectResource}', [ProjectResourceController::class, 'update'])->name('project-resources.update');
Route::delete('project-resources/{projectResource}', [ProjectResourceController::class, 'destroy'])->name('project-resources.destroy');

// Registre des risques
Route::get('projects/{project}/risks', [ProjectRiskController::class, 'index'])->name('projects.risks.index');
Route::post('projects/{project}/risks', [ProjectRiskController::class, 'store'])->name('projects.risks.store');
Route::match(['put', 'patch'], 'project-risks/{projectRisk}', [ProjectRiskController::class, 'update'])->name('project-risks.update');
Route::delete('project-risks/{projectRisk}', [ProjectRiskController::class, 'destroy'])->name('project-risks.destroy');
Route::post('project-risks/{projectRisk}/mitigate', [ProjectRiskController::class, 'mitigate'])->name('project-risks.mitigate');
Route::post('project-risks/{projectRisk}/close', [ProjectRiskController::class, 'close'])->name('project-risks.close');
Route::post('project-risks/{projectRisk}/occur', [ProjectRiskController::class, 'occur'])->name('project-risks.occur');

// Rapports d'étape (append-only)
Route::get('projects/{project}/status-reports', [ProjectStatusReportController::class, 'index'])->name('projects.status-reports.index');
Route::post('projects/{project}/status-reports', [ProjectStatusReportController::class, 'store'])->name('projects.status-reports.store');
Route::delete('project-status-reports/{projectStatusReport}', [ProjectStatusReportController::class, 'destroy'])->name('project-status-reports.destroy');

// Dépendances entre tâches (Gantt)
Route::get('tasks/{task}/dependencies', [TaskDependencyController::class, 'index'])->name('tasks.dependencies.index');
Route::post('tasks/{task}/dependencies', [TaskDependencyController::class, 'store'])->name('tasks.dependencies.store');
Route::delete('task-dependencies/{taskDependency}', [TaskDependencyController::class, 'destroy'])->name('task-dependencies.destroy');

Route::apiResource('objectives', ObjectiveController::class)->except(['create', 'edit']);
Route::get('objectives/{objective}/key-results', [KeyResultController::class, 'index'])->name('objectives.key-results.index');
Route::post('objectives/{objective}/key-results', [KeyResultController::class, 'store'])->name('objectives.key-results.store');

// PUT + PATCH : le client Next générique (`lib/api/resources.ts::update`) envoie
// PUT, cohérent avec `Route::apiResource` qui accepte les deux sur ses autres
// ressources — cette route n'étant pas un apiResource, il faut le déclarer.
Route::match(['put', 'patch'], 'key-results/{keyResult}', [KeyResultController::class, 'update'])->name('key-results.update');
Route::delete('key-results/{keyResult}', [KeyResultController::class, 'destroy'])->name('key-results.destroy');

Route::apiResource('kpis', KpiController::class)->except(['create', 'edit']);
Route::get('kpis/{kpi}/measurements', [KpiMeasurementController::class, 'index'])->name('kpis.measurements.index');
Route::post('kpis/{kpi}/measurements', [KpiMeasurementController::class, 'store'])->name('kpis.measurements.store');
