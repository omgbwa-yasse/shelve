<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class SettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('setting_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Setting $setting): bool
    {
        return $user->hasPermissionTo('setting_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $setting);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('setting_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Setting $setting): bool
    {
        return $user->hasPermissionTo('setting_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $setting);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Setting $setting): bool
    {
        return $user->hasPermissionTo('setting_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $setting);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Setting $setting): bool
    {
        return $user->hasPermissionTo('setting_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $setting);
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
