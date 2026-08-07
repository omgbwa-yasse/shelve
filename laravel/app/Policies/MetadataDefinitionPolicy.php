<?php

namespace App\Policies;

use App\Models\MetadataDefinition;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

/**
 * Politique d'accès aux définitions de métadonnées (D02, sous-référentiel global).
 */
class MetadataDefinitionPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'metadata_definition_viewAny');
    }

    public function view(?User $user, MetadataDefinition $metadataDefinition): bool|Response
    {
        return $this->canView($user, $metadataDefinition, 'metadata_definition_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'metadata_definition_create');
    }

    public function update(?User $user, MetadataDefinition $metadataDefinition): bool|Response
    {
        return $this->canUpdate($user, $metadataDefinition, 'metadata_definition_update');
    }

    public function delete(?User $user, MetadataDefinition $metadataDefinition): bool|Response
    {
        return $this->canDelete($user, $metadataDefinition, 'metadata_definition_delete');
    }

    public function forceDelete(?User $user, MetadataDefinition $metadataDefinition): bool|Response
    {
        return $this->canForceDelete($user, $metadataDefinition, 'metadata_definition_force_delete');
    }
}
