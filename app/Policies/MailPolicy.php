<?php

namespace App\Policies;

use App\Models\Mail;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class MailPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'mail_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Mail $mail): bool|Response
    {
        return $this->canView($user, $mail, 'mail_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'mail_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Mail $mail): bool|Response
    {
        return $this->canUpdate($user, $mail, 'mail_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Mail $mail): bool|Response
    {
        return $this->canDelete($user, $mail, 'mail_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Mail $mail): bool|Response
    {
        return $this->canForceDelete($user, $mail, 'mail_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Mail $mail): bool
    {
        $cacheKey = "mail_org_access:{$user->id}:{$mail->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $mail) {
            // For models directly linked to organisations
            if (method_exists($mail, 'organisations')) {
                foreach($mail->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($mail->organisation_id)) {
                return $mail->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($mail, 'activity') && $mail->activity) {
                foreach($mail->activity->organisations as $organisation) {
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
