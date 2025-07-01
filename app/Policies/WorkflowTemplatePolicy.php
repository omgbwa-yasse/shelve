<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkflowTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir la liste des templates
     */
    public function viewAny(User $user): bool
    {
        return $user->can('workflow_template_view');
    }

    /**
     * Détermine si l'utilisateur peut voir un template spécifique
     */
    public function view(User $user, WorkflowTemplate $template): bool
    {
        return $user->can('workflow_template_view');
    }

    /**
     * Détermine si l'utilisateur peut créer un template
     */
    public function create(User $user): bool
    {
        return $user->can('workflow_template_create');
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour un template
     */
    public function update(User $user, WorkflowTemplate $template): bool
    {
        // Administrateurs ou créateur du template
        if ($user->can('workflow_template_update')) {
            return true;
        }

        return $template->created_by === $user->id && $user->can('workflow_template_update-own');
    }

    /**
     * Détermine si l'utilisateur peut supprimer un template
     */
    public function delete(User $user, WorkflowTemplate $template): bool
    {
        // Ne peut pas supprimer un template avec des instances actives
        if ($template->instances()->where('status', '!=', 'completed')->count() > 0) {
            return false;
        }

        // Administrateurs ou créateur du template
        if ($user->can('workflow_template_delete')) {
            return true;
        }

        return $template->created_by === $user->id && $user->can('workflow_template_delete-own');
    }

    /**
     * Détermine si l'utilisateur peut dupliquer un template
     */
    public function duplicate(User $user, WorkflowTemplate $template): bool
    {
        return $user->can('workflow_template_create') && $user->can('workflow_template_view');
    }

    /**
     * Détermine si l'utilisateur peut activer/désactiver un template
     */
    public function toggleActive(User $user, WorkflowTemplate $template): bool
    {
        // Administrateurs ou créateur du template
        if ($user->can('workflow_template_update')) {
            return true;
        }

        return $template->created_by === $user->id && $user->can('workflow_template_update-own');
    }
}
