<?php

namespace App\Policies;

use App\Models\ThesaurusConcept;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ThesaurusConceptPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'thesaurus_concept_viewAny');
    }

    public function view(?User $user, ThesaurusConcept $thesaurusConcept): bool|Response
    {
        return $this->canView($user, $thesaurusConcept, 'thesaurus_concept_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'thesaurus_concept_create');
    }

    public function update(?User $user, ThesaurusConcept $thesaurusConcept): bool|Response
    {
        return $this->canUpdate($user, $thesaurusConcept, 'thesaurus_concept_update');
    }

    public function delete(?User $user, ThesaurusConcept $thesaurusConcept): bool|Response
    {
        return $this->canDelete($user, $thesaurusConcept, 'thesaurus_concept_delete');
    }

    public function forceDelete(?User $user, ThesaurusConcept $thesaurusConcept): bool|Response
    {
        return $this->canForceDelete($user, $thesaurusConcept, 'thesaurus_concept_force_delete');
    }
}
