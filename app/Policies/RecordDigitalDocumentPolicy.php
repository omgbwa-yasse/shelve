<?php

namespace App\Policies;

use App\Models\RecordDigitalDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecordDigitalDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_documents_view');
    }

    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, RecordDigitalDocument $document): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_documents_view');
    }

    /**
     * Determine if the user can create documents.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_documents_create');
    }

    /**
     * Determine if the user can update the document.
     */
    public function update(User $user, RecordDigitalDocument $document): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_documents_edit');
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, RecordDigitalDocument $document): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_documents_delete');
    }

    /**
     * Determine if the user can restore the document.
     */
    public function restore(User $user, RecordDigitalDocument $document): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_documents_restore');
    }

    /**
     * Determine if the user can permanently delete the document.
     */
    public function forceDelete(User $user, RecordDigitalDocument $document): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_documents_force_delete');
    }
}
