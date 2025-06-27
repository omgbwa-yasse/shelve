<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class ReportPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'report_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool|Response
    {
        return $this->canView($user, $report, 'report_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'report_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool|Response
    {
        return $this->canUpdate($user, $report, 'report_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool|Response
    {
        return $this->canDelete($user, $report, 'report_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool|Response
    {
        return $this->canForceDelete($user, $report, 'report_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Report $report): bool
    {
        $cacheKey = "report_org_access:{$user->id}:{$report->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $report) {
            // For models directly linked to organisations
            if (method_exists($report, 'organisations')) {
                foreach($report->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($report->organisation_id)) {
                return $report->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($report, 'activity') && $report->activity) {
                foreach($report->activity->organisations as $organisation) {
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
