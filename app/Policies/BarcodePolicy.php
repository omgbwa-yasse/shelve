<?php

namespace App\Policies;

use App\Models\Barcode;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class BarcodePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'barcode_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Barcode $barcode): bool|Response
    {
        return $this->canView($user, $barcode, 'barcode_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'barcode_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Barcode $barcode): bool|Response
    {
        return $this->canUpdate($user, $barcode, 'barcode_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Barcode $barcode): bool|Response
    {
        return $this->canDelete($user, $barcode, 'barcode_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Barcode $barcode): bool|Response
    {
        return $this->canForceDelete($user, $barcode, 'barcode_force_delete');
    }";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $barcode) {
            // For models directly linked to organisations
            if (method_exists($barcode, 'organisations')) {
                foreach($barcode->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($barcode->organisation_id)) {
                return $barcode->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($barcode, 'activity') && $barcode->activity) {
                foreach($barcode->activity->organisations as $organisation) {
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
