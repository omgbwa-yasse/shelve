<?php

namespace App\Policies;

use App\Models\RecordDigitalDocumentMetadataProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

/**
 * Politique d'accès aux profils de métadonnées de documents numériques (D02).
 */
class RecordDigitalDocumentMetadataProfilePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'record_digital_document_metadata_profile_viewAny');
    }

    public function view(?User $user, RecordDigitalDocumentMetadataProfile $profile): bool|Response
    {
        return $this->canView($user, $profile, 'record_digital_document_metadata_profile_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'record_digital_document_metadata_profile_create');
    }

    public function update(?User $user, RecordDigitalDocumentMetadataProfile $profile): bool|Response
    {
        return $this->canUpdate($user, $profile, 'record_digital_document_metadata_profile_update');
    }

    public function delete(?User $user, RecordDigitalDocumentMetadataProfile $profile): bool|Response
    {
        return $this->canDelete($user, $profile, 'record_digital_document_metadata_profile_delete');
    }

    public function forceDelete(?User $user, RecordDigitalDocumentMetadataProfile $profile): bool|Response
    {
        return $this->canForceDelete($user, $profile, 'record_digital_document_metadata_profile_force_delete');
    }
}
