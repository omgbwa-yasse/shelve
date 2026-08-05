<?php

namespace App\Policies;

use App\Models\AiSkill;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class AiSkillPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'ai_skill_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, AiSkill $aiSkill): bool|Response
    {
        return $this->canView($user, $aiSkill, 'ai_skill_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'ai_skill_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, AiSkill $aiSkill): bool|Response
    {
        return $this->canUpdate($user, $aiSkill, 'ai_skill_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, AiSkill $aiSkill): bool|Response
    {
        return $this->canDelete($user, $aiSkill, 'ai_skill_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, AiSkill $aiSkill): bool|Response
    {
        return $this->canForceDelete($user, $aiSkill, 'ai_skill_force_delete');
    }
}
