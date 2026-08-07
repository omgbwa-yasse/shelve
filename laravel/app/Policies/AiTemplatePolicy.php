<?php

namespace App\Policies;

use App\Models\AiTemplate;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class AiTemplatePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'ai_template_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, AiTemplate $aiTemplate): bool|Response
    {
        return $this->canView($user, $aiTemplate, 'ai_template_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'ai_template_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, AiTemplate $aiTemplate): bool|Response
    {
        return $this->canUpdate($user, $aiTemplate, 'ai_template_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, AiTemplate $aiTemplate): bool|Response
    {
        return $this->canDelete($user, $aiTemplate, 'ai_template_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, AiTemplate $aiTemplate): bool|Response
    {
        return $this->canForceDelete($user, $aiTemplate, 'ai_template_force_delete');
    }
}
