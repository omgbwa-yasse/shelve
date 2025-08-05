<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitService
{
    /**
     * Limites de taux pour différentes actions
     */
    private const LIMITS = [
        'communication_create' => ['max' => 10, 'decay' => 3600], // 10 par heure
        'reservation_create' => ['max' => 15, 'decay' => 3600],   // 15 par heure
        'search' => ['max' => 100, 'decay' => 3600],              // 100 recherches par heure
        'export' => ['max' => 5, 'decay' => 3600],                // 5 exports par heure
        'api_general' => ['max' => 1000, 'decay' => 3600],        // 1000 requêtes API par heure
    ];

    /**
     * Vérifier si l'utilisateur a dépassé la limite pour une action
     */
    public function tooManyAttempts(string $action, ?int $userId = null): bool
    {
        $key = $this->getKey($action, $userId);
        $limit = $this->getLimit($action);

        return RateLimiter::tooManyAttempts($key, $limit['max']);
    }

    /**
     * Incrémenter le compteur pour une action
     */
    public function increment(string $action, ?int $userId = null, int $amount = 1): void
    {
        $key = $this->getKey($action, $userId);
        $limit = $this->getLimit($action);

        RateLimiter::increment($key, $amount, $limit['decay']);
    }

    /**
     * Exécuter une action avec rate limiting
     */
    public function attempt(string $action, callable $callback, ?int $userId = null)
    {
        $key = $this->getKey($action, $userId);
        $limit = $this->getLimit($action);

        return RateLimiter::attempt(
            $key,
            $limit['max'],
            $callback,
            $limit['decay']
        );
    }

    /**
     * Obtenir le temps restant avant la prochaine tentative
     */
    public function availableIn(string $action, ?int $userId = null): int
    {
        $key = $this->getKey($action, $userId);
        return RateLimiter::availableIn($key);
    }

    /**
     * Obtenir le nombre de tentatives restantes
     */
    public function remaining(string $action, ?int $userId = null): int
    {
        $key = $this->getKey($action, $userId);
        $limit = $this->getLimit($action);

        return RateLimiter::remaining($key, $limit['max']);
    }

    /**
     * Effacer les tentatives pour une action
     */
    public function clear(string $action, ?int $userId = null): void
    {
        $key = $this->getKey($action, $userId);
        RateLimiter::clear($key);
    }

    /**
     * Générer la clé de rate limiting
     */
    private function getKey(string $action, ?int $userId = null): string
    {
        $userId = $userId ?? Auth::id() ?? request()->ip();
        return "{$action}:{$userId}";
    }

    /**
     * Obtenir les limites pour une action
     */
    private function getLimit(string $action): array
    {
        return self::LIMITS[$action] ?? self::LIMITS['api_general'];
    }

    /**
     * Obtenir un message d'erreur formaté
     */
    public function getErrorMessage(string $action, ?int $userId = null): string
    {
        $seconds = $this->availableIn($action, $userId);
        $minutes = ceil($seconds / 60);

        return "Trop de tentatives pour cette action. Veuillez patienter {$minutes} minute(s) avant de réessayer.";
    }

    /**
     * Obtenir les statistiques de rate limiting pour un utilisateur
     */
    public function getStats(?int $userId = null): array
    {
        $stats = [];

        foreach (array_keys(self::LIMITS) as $action) {
            $stats[$action] = [
                'remaining' => $this->remaining($action, $userId),
                'max' => self::LIMITS[$action]['max'],
                'available_in' => $this->tooManyAttempts($action, $userId)
                    ? $this->availableIn($action, $userId)
                    : 0
            ];
        }

        return $stats;
    }
}
