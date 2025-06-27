<?php

namespace App\Policies;

use App\Models\Language;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class LanguagePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'language_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Language $language): bool|Response
    {
        return $this->canView($user, $language, 'language_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'language_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Language $language): bool|Response
    {
        return $this->canUpdate($user, $language, 'language_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Language $language): bool|Response
    {
        return $this->canDelete($user, $language, 'language_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Language $language): bool|Response
    {
        return $this->canForceDelete($user, $language, 'language_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Language $language): bool
    {
        $cacheKey = "language_org_access:{$user->id}:{$language->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $language) {
            // For models directly linked to organisations
            if (method_exists($language, 'organisations')) {
                foreach($language->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($language->organisation_id)) {
                return $language->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($language, 'activity') && $language->activity) {
                foreach($language->activity->organisations as $organisation) {
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
