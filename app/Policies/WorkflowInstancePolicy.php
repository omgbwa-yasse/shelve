<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowInstance;
use App\Enums\WorkflowInstanceStatus;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkflowInstancePolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir la liste des instances de workflow
     */
    public function viewAny(User $user): bool
    {
        return $user->can('workflow_instance_view');
    }

    /**
     * Détermine si l'utilisateur peut voir une instance spécifique
     */
    public function view(User $user, WorkflowInstance $instance): bool
    {
        // Admin général peut voir toutes les instances
        if ($user->can('workflow_instance_view-all')) {
            return true;
        }

        // Utilisateur peut voir ses propres instances ou celles de son organisation
        if ($user->can('workflow_instance_view')) {
            // Instance créée par l'utilisateur
            if ($instance->created_by === $user->id) {
                return true;
            }

            // Instance assignée à l'utilisateur (via étapes)
            if ($instance->stepInstances()->whereHas('assignments', function ($query) use ($user) {
                $query->where('assignee_type', 'user')
                      ->where('assignee_id', $user->id);
            })->exists()) {
                return true;
            }

            // Instance assignée à l'organisation de l'utilisateur
            $userOrganizationIds = $user->organisations->pluck('id')->toArray();
            if ($instance->stepInstances()->whereHas('assignments', function ($query) use ($userOrganizationIds) {
                $query->where('assignee_type', 'organisation')
                      ->whereIn('assignee_id', $userOrganizationIds);
            })->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut créer une instance de workflow
     */
    public function create(User $user): bool
    {
        return $user->can('workflow_instance_create');
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour une instance de workflow
     */
    public function update(User $user, WorkflowInstance $instance): bool
    {
        // Admin peut mettre à jour toutes les instances
        if ($user->can('workflow_instance_update-all')) {
            return true;
        }

        // Les instances terminées ou annulées ne peuvent plus être mises à jour
        if (in_array($instance->status, [
            WorkflowInstanceStatus::COMPLETED,
            WorkflowInstanceStatus::CANCELLED
        ])) {
            return false;
        }

        // Utilisateur peut mettre à jour ses propres instances
        if ($user->can('workflow_instance_update-own') && $instance->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer une instance de workflow
     */
    public function delete(User $user, WorkflowInstance $instance): bool
    {
        // Seuls les admins peuvent supprimer des instances
        if ($user->can('workflow_instance_delete')) {
            // Vérifier que l'instance peut être supprimée (pas de contraintes)
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut démarrer une instance de workflow
     */
    public function start(User $user, WorkflowInstance $instance): bool
    {
        // L'instance doit être à l'état 'draft'
        if ($instance->status !== WorkflowInstanceStatus::DRAFT) {
            return false;
        }

        // Même logique que pour mettre à jour
        return $this->update($user, $instance);
    }

    /**
     * Détermine si l'utilisateur peut annuler une instance de workflow
     */
    public function cancel(User $user, WorkflowInstance $instance): bool
    {
        // L'instance ne peut pas déjà être annulée ou terminée
        if (in_array($instance->status, [
            WorkflowInstanceStatus::COMPLETED,
            WorkflowInstanceStatus::CANCELLED
        ])) {
            return false;
        }

        // Admin peut annuler toutes les instances
        if ($user->can('workflow_instance_cancel-all')) {
            return true;
        }

        // Utilisateur peut annuler ses propres instances
        if ($user->can('workflow_instance_cancel-own') && $instance->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut mettre en pause une instance de workflow
     */
    public function pause(User $user, WorkflowInstance $instance): bool
    {
        // L'instance doit être en cours
        if ($instance->status !== WorkflowInstanceStatus::IN_PROGRESS) {
            return false;
        }

        // Même logique que pour mettre à jour
        return $this->update($user, $instance);
    }

    /**
     * Détermine si l'utilisateur peut reprendre une instance de workflow
     */
    public function resume(User $user, WorkflowInstance $instance): bool
    {
        // L'instance doit être en pause
        if ($instance->status !== WorkflowInstanceStatus::PAUSED) {
            return false;
        }

        // Même logique que pour mettre à jour
        return $this->update($user, $instance);
    }
}
