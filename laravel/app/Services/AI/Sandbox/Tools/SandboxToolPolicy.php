<?php

namespace App\Services\AI\Sandbox\Tools;

use App\Models\AiSandbox;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Contrôle d'accès commun aux outils sandbox : la permission `ai_sandbox_*`
 * exigée ET l'isolation multi-organisation (R03). Sans ces contrôles, l'IA
 * pourrait exécuter du code hors du périmètre de l'agent connecté.
 *
 * - La permission est scopée à l'organisation courante
 *   (`User::hasPermissionToInOrganisation`), rôle par org inclus.
 * - Le sandbox ciblé doit appartenir à l'utilisateur ET à son org courante :
 *   un changement d'org en cours de session ne laisse pas un accès résiduel.
 */
trait SandboxToolPolicy
{
    /** Permission `ai_sandbox_*` exigée pour l'outil. */
    abstract protected function requiredPermission(): string;

    /**
     * Retourne l'utilisateur authentifié ou jette une exception.
     */
    protected function requireUser(): User
    {
        $user = Auth::user();

        if (! $user) {
            throw new RuntimeException('Authentification requise pour utiliser le sandbox.');
        }

        return $user;
    }

    /**
     * Vérifie la permission `ai_sandbox_*` de l'outil pour l'org courante.
     */
    protected function authorizeSandbox(): void
    {
        $user = $this->requireUser();

        if (! $user->hasPermissionToInOrganisation($this->requiredPermission(), $user->current_organisation_id)) {
            throw new RuntimeException('Permission manquante : ' . $this->requiredPermission() . '.');
        }
    }

    /**
     * Résout un sandbox en vérifiant le propriétaire ET l'organisation courante.
     */
    protected function sandboxOrFail(int $id): AiSandbox
    {
        $user = $this->requireUser();

        $sandbox = AiSandbox::find($id);

        if (! $sandbox) {
            throw new RuntimeException('Sandbox introuvable ou non autorisé.');
        }

        if ($sandbox->user_id !== $user->id) {
            throw new RuntimeException('Sandbox introuvable ou non autorisé.');
        }

        if ($sandbox->organisation_id !== $user->current_organisation_id) {
            throw new RuntimeException('Sandbox hors de votre organisation.');
        }

        return $sandbox;
    }
}
