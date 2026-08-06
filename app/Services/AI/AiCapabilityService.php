<?php

namespace App\Services\AI;

use App\Models\User;

/**
 * Traduit les permissions effectives d'un agent (`User::effectivePermissionNames()`)
 * en un résumé lisible injecté dans le prompt système de l'assistant IA — voir
 * exigence utilisateur du 2026-08-05 : "le chatbot ne peut que voir les actions
 * autorisées de son profil". Le contrôle réel reste les policies Laravel
 * (`$this->authorize()` dans chaque contrôleur) : ce résumé ne fait qu'empêcher
 * l'assistant de suggérer ou de prétendre exécuter une action hors périmètre.
 */
class AiCapabilityService
{
    private const ABILITY_LABELS = [
        'viewAny' => 'consulter',
        'view' => 'consulter',
        'create' => 'créer',
        'update' => 'modifier',
        'delete' => 'supprimer',
    ];

    /** Résumé texte des actions autorisées, groupées par ressource, pour le prompt système. */
    public function summaryFor(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return "L'agent est super-administrateur : il a accès à toutes les actions de l'application.";
        }

        $byResource = $this->groupByResource($user->effectivePermissionNames());

        if (empty($byResource)) {
            return "L'agent ne dispose d'aucune permission connue : ne propose ni n'exécute aucune action de création, modification ou suppression.";
        }

        $lines = [];
        foreach ($byResource as $resource => $abilities) {
            $lines[] = "- {$resource} : " . implode(', ', $abilities);
        }

        return "Actions que l'agent connecté est autorisé à effectuer (au-delà, refuse et explique que la permission manque) :\n"
            . implode("\n", $lines);
    }

    /**
     * @param  string[]  $permissionNames
     * @return array<string, string[]>
     */
    private function groupByResource(array $permissionNames): array
    {
        $byResource = [];

        foreach ($permissionNames as $name) {
            [$resource, $ability] = $this->splitPermissionName($name);
            if ($resource === null) {
                continue;
            }

            $label = self::ABILITY_LABELS[$ability] ?? $ability;
            $byResource[$resource][] = $label;
        }

        foreach ($byResource as $resource => $abilities) {
            $byResource[$resource] = array_values(array_unique($abilities));
        }

        return $byResource;
    }

    /** @return array{0: ?string, 1: string} */
    private function splitPermissionName(string $name): array
    {
        foreach (array_keys(self::ABILITY_LABELS) as $ability) {
            if (str_ends_with($name, "_{$ability}")) {
                return [substr($name, 0, -strlen("_{$ability}")), $ability];
            }
        }

        return [null, $name];
    }
}
