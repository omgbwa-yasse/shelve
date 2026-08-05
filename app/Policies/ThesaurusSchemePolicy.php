<?php

namespace App\Policies;

use App\Models\ThesaurusScheme;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ThesaurusSchemePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'thesaurus_scheme_viewAny');
    }

    public function view(?User $user, ThesaurusScheme $thesaurusScheme): bool|Response
    {
        return $this->canView($user, $thesaurusScheme, 'thesaurus_scheme_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'thesaurus_scheme_create');
    }

    public function update(?User $user, ThesaurusScheme $thesaurusScheme): bool|Response
    {
        return $this->canUpdate($user, $thesaurusScheme, 'thesaurus_scheme_update');
    }

    public function delete(?User $user, ThesaurusScheme $thesaurusScheme): bool|Response
    {
        return $this->canDelete($user, $thesaurusScheme, 'thesaurus_scheme_delete');
    }

    public function forceDelete(?User $user, ThesaurusScheme $thesaurusScheme): bool|Response
    {
        return $this->canForceDelete($user, $thesaurusScheme, 'thesaurus_scheme_force_delete');
    }
}
