<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowStepInstance;
use App\Models\WorkflowInstance;
use App\Enums\WorkflowInstanceStatus;
use App\Enums\WorkflowStepInstanceStatus;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkflowStepInstancePolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir les instances d'étapes
     */
    public function viewAny(User $user): bool
    {
        return $user->can('workflow_step-instance_view');
    }

    /**
     * Détermine si l'utilisateur peut voir une instance d'étape spécifique
     */
    public function view(User $user, WorkflowStepInstance $stepInstance): bool
    {
        // Admin général peut voir toutes les instances d'étapes
        if ($user->can('workflow_step-instance_view-all')) {
            return true;
        }

        // Utilisateur peut voir les instances d'étapes qui lui sont assignées
        if ($user->can('workflow_step-instance_view')) {
            // Vérifier si l'étape est assignée à l'utilisateur
            if ($stepInstance->assignments()->where('assignee_type', 'user')
                ->where('assignee_id', $user->id)->exists()) {
                return true;
            }

            // Vérifier si l'étape est assignée à une organisation de l'utilisateur
            $userOrganizationIds = $user->organisations->pluck('id')->toArray();
            if ($stepInstance->assignments()->where('assignee_type', 'organisation')
                ->whereIn('assignee_id', $userOrganizationIds)->exists()) {
                return true;
            }

            // Vérifier si l'utilisateur a créé l'instance de workflow parent
            if ($stepInstance->workflowInstance->created_by === $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut compléter une instance d'étape
     */
    public function complete(User $user, WorkflowStepInstance $stepInstance): bool
    {
        // L'instance d'étape doit être active
        if ($stepInstance->status !== WorkflowStepInstanceStatus::IN_PROGRESS) {
            return false;
        }

        // L'instance de workflow parent ne doit pas être en pause ou annulée
        if (in_array($stepInstance->workflowInstance->status, [
            WorkflowInstanceStatus::CANCELLED,
            WorkflowInstanceStatus::ON_HOLD,
        ])) {
            return false;
        }

        // Vérifier si l'utilisateur est assigné à cette étape
        if ($stepInstance->assignments()->where('assignee_type', 'user')
            ->where('assignee_id', $user->id)->exists()) {
            return $user->can('workflow_step-instance_execute');
        }

        // Vérifier si une organisation de l'utilisateur est assignée à cette étape
        $userOrganizationIds = $user->organisations->pluck('id')->toArray();
        if ($stepInstance->assignments()->where('assignee_type', 'organisation')
            ->whereIn('assignee_id', $userOrganizationIds)->exists()) {
            return $user->can('workflow_step-instance_execute');
        }

        // Les admins peuvent compléter n'importe quelle étape
        return $user->can('workflow_step-instance_execute-any');
    }

    /**
     * Détermine si l'utilisateur peut rejeter une instance d'étape
     */
    public function reject(User $user, WorkflowStepInstance $stepInstance): bool
    {
        // Même logique que pour compléter
        return $this->complete($user, $stepInstance);
    }

    /**
     * Détermine si l'utilisateur peut réassigner une instance d'étape
     */
    public function reassign(User $user, WorkflowStepInstance $stepInstance): bool
    {
        // Seuls les administrateurs ou le créateur du workflow peuvent réassigner
        if ($user->can('workflow_step-instance_reassign')) {
            return true;
        }

        // Le créateur de l'instance de workflow peut réassigner
        if ($user->can('workflow_step-instance_reassign-own') &&
            $stepInstance->workflowInstance->created_by === $user->id) {
            return true;
        }

        return false;
    }
}
