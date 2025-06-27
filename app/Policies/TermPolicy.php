<?php

namespace App\Policies;

use App\Models\Term;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class TermPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('term_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Term $term): bool
    {
        return $user->hasPermissionTo('term_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $term);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('term_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Term $term): bool
    {
        return $user->hasPermissionTo('term_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $term);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Term $term): bool
    {
        return $user->hasPermissionTo('term_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $term);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Term $term): bool
    {
        return $user->hasPermissionTo('term_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $term);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Term $term): bool
    {
        $cacheKey = "term_org_access:{$user->id}:{$term->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $term) {
            // For models directly linked to organisations
            if (method_exists($term, 'organisations')) {
                foreach($term->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($term->organisation_id)) {
                return $term->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($term, 'activity') && $term->activity) {
                foreach($term->activity->organisations as $organisation) {
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
