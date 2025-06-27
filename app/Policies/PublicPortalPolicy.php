<?php

namespace App\Policies;

use App\Models\PublicPortal;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class PublicPortalPolicy
{
    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, PublicPortal $publicportal): bool
    {
        $cacheKey = "public_portal_org_access:{$user->id}:{$publicportal->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $publicportal) {
            // For models directly linked to organisations
            if (method_exists($publicportal, 'organisations')) {
                foreach($publicportal->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($publicportal->organisation_id)) {
                return $publicportal->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($publicportal, 'activity') && $publicportal->activity) {
                foreach($publicportal->activity->organisations as $organisation) {
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
