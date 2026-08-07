<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingService
{
    /**
     * Récupère la valeur d'un paramètre pour l'utilisateur et l'organisation actuels
     *
     * @param string $name Nom du paramètre
     * @param mixed $default Valeur par défaut si le paramètre n'existe pas
     * @param int|null $userId ID de l'utilisateur (null = utilisateur actuel)
     * @param int|null $organisationId ID de l'organisation (null = organisation active de l'utilisateur)
     * @return mixed
     */
    public function get(string $name, $default = null, $userId = null, $organisationId = null)
    {
        $userId = $userId ?? Auth::id();
        $organisationId = $organisationId ?? (Auth::user()->organisation_active_id ?? null);

        $cacheKey = "setting.{$name}.{$userId}.{$organisationId}";

        return Cache::remember($cacheKey, 3600, function () use ($name, $default, $userId, $organisationId) {
            // Recherche par ordre de priorité :
            // 1. Paramètre spécifique utilisateur + organisation
            // 2. Paramètre spécifique utilisateur seul
            // 3. Paramètre spécifique organisation seule
            // 4. Paramètre global avec valeur par défaut

            $setting = Setting::where('name', $name)
                ->where(function ($query) use ($userId, $organisationId) {
                    $query->where(function ($q) use ($userId, $organisationId) {
                        // Priorité 1 : utilisateur + organisation
                        $q->where('user_id', $userId)
                          ->where('organisation_id', $organisationId);
                    })->orWhere(function ($q) use ($userId) {
                        // Priorité 2 : utilisateur seul
                        $q->where('user_id', $userId)
                          ->whereNull('organisation_id');
                    })->orWhere(function ($q) use ($organisationId) {
                        // Priorité 3 : organisation seule
                        $q->whereNull('user_id')
                          ->where('organisation_id', $organisationId);
                    })->orWhere(function ($q) {
                        // Priorité 4 : paramètre global
                        $q->whereNull('user_id')
                          ->whereNull('organisation_id');
                    });
                })
                ->orderByRaw('
                    CASE
                        WHEN user_id = ? AND organisation_id = ? THEN 1
                        WHEN user_id = ? AND organisation_id IS NULL THEN 2
                        WHEN user_id IS NULL AND organisation_id = ? THEN 3
                        WHEN user_id IS NULL AND organisation_id IS NULL THEN 4
                        ELSE 5
                    END
                ', [$userId, $organisationId, $userId, $organisationId])
                ->first();

            if ($setting) {
                return $setting->getEffectiveValue();
            }



            return $default;
        });
    }

    /**
     * Définit une valeur de paramètre pour l'utilisateur et l'organisation actuels
     *
     * @param string $name Nom du paramètre
     * @param mixed $value Valeur à définir
     * @param int|null $userId ID de l'utilisateur (null = utilisateur actuel)
     * @param int|null $organisationId ID de l'organisation (null = organisation active)
     * @return bool
     */
    public function set(string $name, $value, $userId = null, $organisationId = null): bool
    {
        $userId = $userId ?? Auth::id();
        $organisationId = $organisationId ?? (Auth::user()->organisation_active_id ?? null);

        // Récupère le paramètre de base (global)
        $baseSetting = Setting::where('name', $name)
            ->whereNull('user_id')
            ->whereNull('organisation_id')
            ->first();

        if (!$baseSetting) {
            return false;
        }

        // Crée ou met à jour le paramètre personnalisé
        Setting::updateOrCreate(
            [
                'name' => $name,
                'user_id' => $userId,
                'organisation_id' => $organisationId,
            ],
            [
                'category_id' => $baseSetting->category_id,
                'type' => $baseSetting->type,
                'default_value' => $baseSetting->default_value,
                'description' => $baseSetting->description,
                'is_system' => $baseSetting->is_system,
                'constraints' => $baseSetting->constraints,
                'value' => $value,
            ]
        );

        // Vide le cache
        $this->clearCache($name, $userId, $organisationId);

        return true;
    }

    /**
     * Réinitialise un paramètre à sa valeur par défaut
     *
     * @param string $name Nom du paramètre
     * @param int|null $userId ID de l'utilisateur (null = utilisateur actuel)
     * @param int|null $organisationId ID de l'organisation (null = organisation active)
     * @return bool
     */
    public function reset(string $name, $userId = null, $organisationId = null): bool
    {
        $userId = $userId ?? Auth::id();
        $organisationId = $organisationId ?? (Auth::user()->organisation_active_id ?? null);

        Setting::where('name', $name)
            ->where('user_id', $userId)
            ->where('organisation_id', $organisationId)
            ->delete();

        $this->clearCache($name, $userId, $organisationId);

        return true;
    }

    /**
     * Récupère tous les paramètres pour l'utilisateur et l'organisation actuels
     *
     * @param int|null $userId ID de l'utilisateur (null = utilisateur actuel)
     * @param int|null $organisationId ID de l'organisation (null = organisation active)
     * @return array
     */
    public function all($userId = null, $organisationId = null): array
    {
        $userId = $userId ?? Auth::id();
        $organisationId = $organisationId ?? (Auth::user()->organisation_active_id ?? null);

        $settings = Setting::forUserAndOrganisation($userId, $organisationId)->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->name] = $setting->getEffectiveValue();
        }

        return $result;
    }

    /**
     * Vide le cache pour un paramètre spécifique
     *
     * @param string $name Nom du paramètre
     * @param int|null $userId ID de l'utilisateur
     * @param int|null $organisationId ID de l'organisation
     * @return void
     */
    private function clearCache(string $name, $userId = null, $organisationId = null): void
    {
        $cacheKey = "setting.{$name}.{$userId}.{$organisationId}";
        Cache::forget($cacheKey);
    }

    /**
     * Vide tout le cache des paramètres
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        Cache::flush(); // Ou utiliser un tag spécifique si vous implémentez le cache taggé
    }

    /**
     * Vérifie si un paramètre existe
     *
     * @param string $name Nom du paramètre
     * @return bool
     */
    public function exists(string $name): bool
    {
        return Setting::where('name', $name)->exists();
    }
}
