<?php

namespace App\Services;

use App\Models\Project;

/**
 * Alertes de projet — délibérément calculées à la lecture plutôt que
 * persistées (voir `evolution/PROJECT-OKR-KPI-PLAN.md`, extension
 * MS-Project-parity) : pas de désynchronisation possible, pas de worker de
 * file d'attente à maintenir. Chaque alerte est reconstruite depuis l'état
 * courant des tâches/jalons/livrables/ressources à chaque appel.
 */
class ProjectAlertService
{
    private const DUE_SOON_DAYS = 3;

    public function forProject(Project $project): array
    {
        $project->loadMissing(['tasks', 'milestones', 'deliverables', 'resources', 'risks']);

        return array_values(array_merge(
            $this->taskAlerts($project),
            $this->milestoneAlerts($project),
            $this->deliverableAlerts($project),
            $this->budgetAlerts($project),
            $this->riskAlerts($project),
        ));
    }

    private function riskAlerts(Project $project): array
    {
        $alerts = [];

        foreach ($project->risks as $risk) {
            if ($risk->status !== 'open') {
                continue;
            }

            if ($risk->criticality === 'high') {
                $alerts[] = [
                    'type' => 'risk_critical',
                    'severity' => 'high',
                    'subject_type' => 'risk',
                    'subject_id' => $risk->id,
                    'message' => "Risque critique ouvert : « {$risk->title} » (probabilité {$risk->probability}, impact {$risk->impact}).",
                ];
            }

            if ($risk->is_overdue) {
                $alerts[] = [
                    'type' => 'risk_review_overdue',
                    'severity' => 'medium',
                    'subject_type' => 'risk',
                    'subject_id' => $risk->id,
                    'message' => "Risque « {$risk->title} » — date de revue dépassée ({$risk->review_date?->toDateString()}).",
                ];
            }
        }

        return $alerts;
    }

    private function taskAlerts(Project $project): array
    {
        $alerts = [];

        foreach ($project->tasks as $task) {
            if ($task->is_overdue) {
                $alerts[] = [
                    'type' => 'task_overdue',
                    'severity' => 'high',
                    'subject_type' => 'task',
                    'subject_id' => $task->id,
                    'message' => "Tâche « {$task->title} » en retard (échéance {$task->due_date?->toDateString()}).",
                ];
            }
        }

        return $alerts;
    }

    private function milestoneAlerts(Project $project): array
    {
        $alerts = [];

        foreach ($project->milestones as $milestone) {
            if ($milestone->status !== 'pending' || $milestone->due_date === null) {
                continue;
            }

            if ($milestone->is_overdue) {
                $alerts[] = [
                    'type' => 'milestone_overdue',
                    'severity' => 'high',
                    'subject_type' => 'milestone',
                    'subject_id' => $milestone->id,
                    'message' => "Jalon « {$milestone->name} » dépassé (échéance {$milestone->due_date->toDateString()}).",
                ];
            } elseif (now()->diffInDays($milestone->due_date, false) <= self::DUE_SOON_DAYS && now()->diffInDays($milestone->due_date, false) >= 0) {
                $alerts[] = [
                    'type' => 'milestone_due_soon',
                    'severity' => 'medium',
                    'subject_type' => 'milestone',
                    'subject_id' => $milestone->id,
                    'message' => "Jalon « {$milestone->name} » à échéance proche ({$milestone->due_date->toDateString()}).",
                ];
            }
        }

        return $alerts;
    }

    private function deliverableAlerts(Project $project): array
    {
        $alerts = [];

        foreach ($project->deliverables as $deliverable) {
            if ($deliverable->is_overdue) {
                $alerts[] = [
                    'type' => 'deliverable_overdue',
                    'severity' => 'high',
                    'subject_type' => 'deliverable',
                    'subject_id' => $deliverable->id,
                    'message' => "Livrable « {$deliverable->name} » en retard (échéance {$deliverable->due_date?->toDateString()}).",
                ];
            }
        }

        return $alerts;
    }

    private function budgetAlerts(Project $project): array
    {
        $alerts = [];

        if ($project->is_over_budget) {
            $alerts[] = [
                'type' => 'budget_overrun',
                'severity' => 'high',
                'subject_type' => 'project',
                'subject_id' => $project->id,
                'message' => sprintf(
                    'Budget dépassé : %.2f consommé pour %.2f planifié.',
                    $project->budget_actual,
                    (float) $project->budget_planned
                ),
            ];
        }

        foreach ($project->resources as $resource) {
            if ($resource->is_over_budget) {
                $alerts[] = [
                    'type' => 'resource_over_budget',
                    'severity' => 'medium',
                    'subject_type' => 'resource',
                    'subject_id' => $resource->id,
                    'message' => "Ressource « {$resource->name} » dépasse son montant planifié.",
                ];
            }
        }

        return $alerts;
    }
}
