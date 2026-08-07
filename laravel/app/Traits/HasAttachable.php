<?php

namespace App\Traits;

use App\Models\Organisation;
use App\Models\User;
use App\Models\Workplace;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Rattachement polymorphe à l'une de 3 cibles hétérogènes : un `Workplace`,
 * une `Organisation` (unité administrative) ou un `User` (personne).
 *
 * Généralise le patron déjà en place sur `Task.taskable` (voir
 * `evolution/PROJECT-OKR-KPI-PLAN.md`, §0) plutôt que 3 colonnes FK nullables
 * répétées sur chaque table — une seule paire `attachable_type`/`attachable_id`,
 * une seule règle de validation centralisée.
 */
trait HasAttachable
{
    /** Classes autorisées comme cible de rattachement. */
    public static function attachableTypes(): array
    {
        return [
            Workplace::class,
            Organisation::class,
            User::class,
        ];
    }

    /**
     * Alias courts exposés dans le contrat API — le client envoie
     * `attachable_type: "workplace"|"organisation"|"user"`, jamais un FQCN PHP.
     */
    public static function attachableAliases(): array
    {
        return [
            'workplace' => Workplace::class,
            'organisation' => Organisation::class,
            'user' => User::class,
        ];
    }

    public static function resolveAttachableAlias(string $alias): ?string
    {
        return self::attachableAliases()[$alias] ?? null;
    }

    public static function attachableAliasFor(string $fqcn): ?string
    {
        return array_search($fqcn, self::attachableAliases(), true) ?: null;
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeAttachedToWorkplace($query, int $workplaceId)
    {
        return $query->where('attachable_type', Workplace::class)->where('attachable_id', $workplaceId);
    }

    public function scopeAttachedToOrganisation($query, int $organisationId)
    {
        return $query->where('attachable_type', Organisation::class)->where('attachable_id', $organisationId);
    }

    public function scopeAttachedToUser($query, int $userId)
    {
        return $query->where('attachable_type', User::class)->where('attachable_id', $userId);
    }
}
