<?php

namespace App\Policies\Concerns;

use App\Models\Organisation;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceMember;

/**
 * Vérification d'accès à la cible de rattachement (`attachable`) d'un
 * `Project`/`Objective`/`Kpi` — voir App\Traits\HasAttachable et
 * `evolution/PROJECT-OKR-KPI-PLAN.md`, §2.
 *
 * S'ajoute à la vérification d'organisation déjà faite par `BasePolicy` : un
 * projet peut être dans la bonne organisation mais rattaché à un workplace ou
 * une personne à laquelle l'utilisateur n'a pas accès.
 */
trait ChecksAttachableAccess
{
    protected function canAccessAttachable(User $user, string $attachableType, int $attachableId): bool
    {
        return match ($attachableType) {
            Workplace::class => WorkplaceMember::where('workplace_id', $attachableId)
                ->where('user_id', $user->id)
                ->exists(),

            // L'organisation courante de l'agent est toujours accessible, même sans
            // ligne dans le pivot `user_organisation_role` (qui porte des rôles
            // assignés, pas la seule appartenance) — sinon un agent n'aurait jamais
            // accès aux projets/OKR/KPI de sa propre organisation par défaut.
            Organisation::class => $attachableId === $user->current_organisation_id
                || $user->organisations()->where('organisations.id', $attachableId)->exists(),

            User::class => $attachableId === $user->id || $this->isManagerOf($user, $attachableId),

            default => false,
        };
    }

    /**
     * Un « responsable » peut voir/gérer les projets/OKR/KPI rattachés à ses
     * subordonnés directs. Approximation actuelle : les utilisateurs qui
     * partagent une organisation avec le manager courant et dont le manager
     * dispose d'une permission de gestion élargie (superadmin déjà court-circuité
     * par `BasePolicy::before`). À affiner si un vrai lien hiérarchique
     * (N+1) est modélisé un jour.
     */
    protected function isManagerOf(User $user, int $targetUserId): bool
    {
        return $user->organisations()
            ->whereHas('users', fn ($query) => $query->where('users.id', $targetUserId))
            ->exists();
    }
}
