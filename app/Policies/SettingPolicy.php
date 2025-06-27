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
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'setting_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Setting $setting): bool|Response
    {
        return $this->canView($user, $setting, 'setting_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'setting_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Setting $setting): bool|Response
    {
        return $this->canUpdate($user, $setting, 'setting_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Setting $setting): bool|Response
    {
        return $this->canDelete($user, $setting, 'setting_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Setting $setting): bool|Response
    {
        return $this->canForceDelete($user, $setting, 'setting_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Setting $setting): bool
    {
        $cacheKey = "setting_org_access:{$user->id}:{$setting->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $setting) {
            // For models directly linked to organisations
            if (method_exists($setting, 'organisations')) {
                foreach($setting->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($setting->organisation_id)) {
                return $setting->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($setting, 'activity') && $setting->activity) {
                foreach($setting->activity->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // Default: allow access if no specific organisation restriction
            return true;
        });
    }
}
