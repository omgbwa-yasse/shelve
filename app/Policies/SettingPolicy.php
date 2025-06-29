<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class SettingPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'setting_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Setting $setting): bool|Response
    {
        return $this->canView($user, $setting, 'setting_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'setting_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Setting $setting): bool|Response
    {
        return $this->canUpdate($user, $setting, 'setting_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Setting $setting): bool|Response
    {
        return $this->canDelete($user, $setting, 'setting_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Setting $setting): bool|Response
    {
        return $this->canForceDelete($user, $setting, 'setting_force_delete');
    }

    /**
     */
}
