<?php

namespace App\Policies;

use App\Models\PublicUser;
use App\Models\PublicDocumentRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicDocumentRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any document requests.
     */
    public function viewAny(PublicUser $user): bool
    {
        return $user->is_approved;
    }

    /**
     * Determine whether the user can view the document request.
     */
    public function view(PublicUser $user, PublicDocumentRequest $documentRequest): bool
    {
        return $user->is_approved && $user->id === $documentRequest->user_id;
    }

    /**
     * Determine whether the user can create document requests.
     */
    public function create(PublicUser $user): bool
    {
        return $user->is_approved && $user->email_verified_at !== null;
    }

    /**
     * Determine whether the user can update the document request.
     */    public function update(PublicUser $user, PublicDocumentRequest $documentRequest): bool
    {
        return $user->is_approved
            && $user->id === $documentRequest->user_id
            && in_array($documentRequest->status, ['pending']);
    }

    /**
     * Determine whether the user can delete the document request.
     */
    public function delete(PublicUser $user, PublicDocumentRequest $documentRequest): bool
    {
        return $user->is_approved
            && $user->id === $documentRequest->user_id
            && in_array($documentRequest->status, ['pending']);
    }
}
