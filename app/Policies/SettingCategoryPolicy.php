<?php

namespace App\Policies;

use App\Models\SettingCategory;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class SettingCategoryPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'setting_category_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, SettingCategory $settingCategory): bool|Response
    {
        return $this->canView($user, $settingCategory, 'setting_category_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'setting_category_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, SettingCategory $settingCategory): bool|Response
    {
        return $this->canUpdate($user, $settingCategory, 'setting_category_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, SettingCategory $settingCategory): bool|Response
    {
        return $this->canDelete($user, $settingCategory, 'setting_category_delete');
    }
}
