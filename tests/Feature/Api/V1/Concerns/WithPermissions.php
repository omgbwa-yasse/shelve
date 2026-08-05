<?php

namespace Tests\Feature\Api\V1\Concerns;

use App\Models\Permission;
use App\Models\User;

/**
 * Permissions de test pour les référentiels D01.
 *
 * Les Policies refusent par défaut : sans les permissions correspondantes, chaque
 * requête des tests recevrait un 403 au lieu du code attendu. Les permissions D01
 * n'étaient ni seedées ni invoquées par le back-office Blade avant ce portage
 * (voir JOURNAL-PHASE-1.md) : ce trait les matérialise à la volée, puis les
 * rattache à l'agent de test.
 */
trait WithPermissions
{
    /**
     * Crée (si absentes) les permissions `{resource}_{ability}` et les rattache à
     * l'utilisateur.
     *
     * @param  string[]  $resources  ex. ['activity', 'law']
     */
    protected function grantD01Permissions(User $user, array $resources, array $abilities = ['viewAny', 'view', 'create', 'update', 'delete']): void
    {
        $permissionIds = [];

        foreach ($resources as $resource) {
            foreach ($abilities as $ability) {
                $permission = Permission::firstOrCreate(
                    ['name' => "{$resource}_{$ability}"],
                    ['category' => 'settings', 'description' => "D01 {$resource}_{$ability} (test)", 'guard_name' => 'web']
                );
                $permissionIds[] = $permission->id;
            }
        }

        $user->permissions()->syncWithoutDetaching($permissionIds);
    }
}
