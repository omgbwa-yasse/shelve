<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkflowStepPolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir la liste des étapes
     */
    public function viewAny(User $user): bool
    {
        return $user->can('workflow_step_view');
    }

    /**
     * Détermine si l'utilisateur peut voir une étape spécifique
     */
    public function view(User $user, WorkflowStep $step): bool
    {
        // Si l'utilisateur peut voir le template, il peut voir l'étape
        return $user->can('workflow_step_view');
    }

    /**
     * Détermine si l'utilisateur peut créer une étape
     */
    public function create(User $user, ?WorkflowTemplate $template = null): bool
    {
        // Vérifier si l'utilisateur a la permission de créer des étapes
        if (!$user->can('workflow_step_create')) {
            return false;
        }

        // Si un template est spécifié, vérifier si l'utilisateur peut le modifier
        if ($template) {
            // Administrateurs ou créateur du template
            if ($user->can('workflow_template_update')) {
                return true;
            }

            return $template->created_by === $user->id && $user->can('workflow_template_update-own');
        }

        return true;
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour une étape
     */
    public function update(User $user, WorkflowStep $step): bool
    {
        // Vérifier si le template associé à cette étape peut être modifié par l'utilisateur
        $template = $step->template;

        // Administrateurs ou créateur du template
        if ($user->can('workflow_step_update')) {
            return true;
        }

        return $template->created_by === $user->id && $user->can('workflow_step_update-own');
    }

    /**
     * Détermine si l'utilisateur peut supprimer une étape
     */
    public function delete(User $user, WorkflowStep $step): bool
    {
        // Vérifier si cette étape n'est pas utilisée dans des instances actives
        $templateHasActiveInstances = $step->template->instances()
            ->where('status', '!=', 'completed')
            ->exists();

        if ($templateHasActiveInstances) {
            return false;
        }

        // Administrateurs ou créateur du template
        if ($user->can('workflow_step_delete')) {
            return true;
        }

        return $step->template->created_by === $user->id && $user->can('workflow_step_delete-own');
    }

    /**
     * Détermine si l'utilisateur peut réorganiser les étapes
     */
    public function reorder(User $user, WorkflowTemplate $template): bool
    {
        // Même logique que pour mettre à jour
        if ($user->can('workflow_step_update')) {
            return true;
        }

        return $template->created_by === $user->id && $user->can('workflow_step_update-own');
    }

    /**
     * Détermine si l'utilisateur peut gérer les assignations d'une étape
     */
    public function manageAssignments(User $user, WorkflowStep $step): bool
    {
        // Même logique que pour mettre à jour
        if ($user->can('workflow_step_update')) {
            return true;
        }

        return $step->template->created_by === $user->id && $user->can('workflow_step_update-own');
    }
}
