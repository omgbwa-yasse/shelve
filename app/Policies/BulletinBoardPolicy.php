<?php

namespace App\Policies;

use App\Models\BulletinBoard;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class BulletinBoardPolicy
{
    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, BulletinBoard $bulletinboard): bool
    {
        $cacheKey = "bulletin_board_org_access:{$user->id}:{$bulletinboard->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $bulletinboard) {
            // For models directly linked to organisations
            if (method_exists($bulletinboard, 'organisations')) {
                foreach($bulletinboard->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($bulletinboard->organisation_id)) {
                return $bulletinboard->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($bulletinboard, 'activity') && $bulletinboard->activity) {
                foreach($bulletinboard->activity->organisations as $organisation) {
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
