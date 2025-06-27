<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

abstract class PublicBasePolicy
{
    /**
     * Create a detailed authorization response with a custom message.
     */
    protected function deny(string $message = 'Cette action n\'est pas autorisée.'): Response
    {
        return Response::deny($message);
    }

    /**
     * Create a 404 response to hide the existence of a resource.
     */
    protected function denyAsNotFound(): Response
    {
        return Response::denyAsNotFound();
    }

    /**
     * Allow the action.
     */
    protected function allow(): Response
    {
        return Response::allow();
    }

    /**
     * Check if the public user is approved and verified.
     */
    protected function isApprovedAndVerified($user): bool
    {
        return $user &&
               $user->is_approved &&
               $user->email_verified_at !== null;
    }

    /**
     * Check if user can view public content.
     */
    protected function canViewPublic(): bool|Response
    {
        return true; // Most public content is viewable by everyone
    }

    /**
     * Check if user can perform authenticated actions.
     */
    protected function canPerformAuthenticatedAction($user, string $action = 'effectuer cette action'): bool|Response
    {
        if (!$user) {
            return $this->deny('Vous devez être connecté pour ' . $action . '.');
        }

        if (!$this->isApprovedAndVerified($user)) {
            return $this->getUserStatusError($user, $action);
        }

        return true;
    }

    /**
     * Get appropriate error message based on user status.
     */
    private function getUserStatusError($user, string $action): Response
    {
        if (!$user->is_approved) {
            return $this->deny('Votre compte doit être approuvé pour ' . $action . '.');
        }

        return $this->deny('Vous devez vérifier votre email pour ' . $action . '.');
    }
}
