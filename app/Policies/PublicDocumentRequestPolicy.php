<?php

namespace App\Policies;

use App\Models\PublicUser;
use App\Models\PublicDocumentRequest;
use Illuminate\Auth\Access\Response;

class PublicDocumentRequestPolicy extends PublicBasePolicy
{
    /**
     * Determine whether the user can view any document requests.
     */
    public function viewAny(PublicUser $user): bool|Response
    {
        return $this->canPerformAuthenticatedAction($user, 'voir les demandes de documents');
    }

    /**
     * Determine whether the user can view the document request.
     */
    public function view(PublicUser $user, PublicDocumentRequest $documentRequest): bool|Response
    {
        $authCheck = $this->canPerformAuthenticatedAction($user, 'voir cette demande');
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ($user->id !== $documentRequest->user_id) {
            return $this->denyAsNotFound();
        }

        return $this->allow();
    }

    /**
     * Determine whether the user can create document requests.
     */
    public function create(PublicUser $user): bool|Response
    {
        return $this->canPerformAuthenticatedAction($user, 'créer une demande de document');
    }

    /**
     * Determine whether the user can update the document request.
     */
    public function update(PublicUser $user, PublicDocumentRequest $documentRequest): bool|Response
    {
        $authCheck = $this->canPerformAuthenticatedAction($user, 'modifier cette demande');
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ($user->id !== $documentRequest->user_id) {
            return $this->denyAsNotFound();
        }

        // Can only update if status is still pending
        if (!in_array($documentRequest->status, ['pending'])) {
            return $this->deny('Vous ne pouvez modifier que les demandes en attente.');
        }

        return $this->allow();
    }

    /**
     * Determine whether the user can delete the document request.
     */
    public function delete(PublicUser $user, PublicDocumentRequest $documentRequest): bool|Response
    {
        $authCheck = $this->canPerformAuthenticatedAction($user, 'supprimer cette demande');
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ($user->id !== $documentRequest->user_id) {
            return $this->denyAsNotFound();
        }

        // Can only delete if status is pending
        if (!in_array($documentRequest->status, ['pending'])) {
            return $this->deny('Vous ne pouvez supprimer que les demandes en attente.');
        }

        return $this->allow();
    }
}
