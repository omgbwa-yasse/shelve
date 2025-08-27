<?php

namespace App\Services\AI;

use App\Services\SettingService;

class DefaultValueService
{
    public function __construct(
        private SettingService $settingService
    ) {}

    /**
     * Récupère le provider par défaut avec fallback
     * Utilise la valeur actuelle si définie, sinon la valeur par défaut
     */
    public function getDefaultProvider(?string $currentValue = null): string
    {
        // Si une valeur actuelle est fournie et non vide, l'utiliser
        if (!empty($currentValue)) {
            return strtolower(trim($currentValue));
        }

        // Sinon récupérer depuis les paramètres avec fallback
        $settingValue = $this->settingService->get('ai_default_provider');

        // Si le paramètre existe et n'est pas vide, l'utiliser
        if (!empty($settingValue)) {
            return strtolower(trim($settingValue));
        }

        // Fallback final
        return 'ollama';
    }

    /**
     * Récupère le modèle par défaut avec fallback
     * Utilise la valeur actuelle si définie, sinon la valeur par défaut
     */
    public function getDefaultModel(?string $currentValue = null): string
    {
        // Si une valeur actuelle est fournie et non vide, l'utiliser
        if (!empty($currentValue)) {
            return trim($currentValue);
        }

        // Récupérer depuis les paramètres avec fallback
        $settingValue = $this->settingService->get('ai_default_model');
        if (!empty($settingValue)) {
            return trim($settingValue);
        }

        // Fallback vers la configuration Laravel ou valeur par défaut
        return trim(config('ollama-laravel.model', 'gemma3:4b'));
    }

    /**
     * Récupère la valeur effective d'un paramètre avec fallback multiple
     *
     * @param string $settingName Nom du paramètre
     * @param mixed $currentValue Valeur actuelle (peut être null)
     * @param mixed $defaultFallback Valeur de fallback final
     * @return mixed
     */
    public function getEffectiveValue(string $settingName, $currentValue = null, $defaultFallback = null)
    {
        // Si une valeur actuelle est fournie et non vide/null, l'utiliser
        if ($currentValue !== null && $currentValue !== '') {
            return $currentValue;
        }

        // Sinon récupérer depuis les paramètres
        $settingValue = $this->settingService->get($settingName);

        // Si le paramètre existe et n'est pas vide/null, l'utiliser
        if ($settingValue !== null && $settingValue !== '') {
            return $settingValue;
        }

        // Fallback final
        return $defaultFallback;
    }

    /**
     * Normalise le nom d'un provider
     */
    public function normalizeProviderName(?string $providerName): string
    {
        if (empty($providerName)) {
            return $this->getDefaultProvider();
        }

        $normalized = strtolower(trim($providerName));

        // Gestion des aliases
        return match ($normalized) {
            'openai-custom', 'openai custom' => 'openai_custom',
            'ollama-turbo', 'ollama turbo' => 'ollama_turbo',
            default => $normalized,
        };
    }

    /**
     * Valide et retourne un provider avec fallback
     */
    public function getValidatedProvider(?string $providerName): string
    {
        $provider = $this->normalizeProviderName($providerName);

        // Liste des providers supportés
        $supportedProviders = [
            'ollama',
            'openai',
            'gemini',
            'claude',
            'openrouter',
            'onn',
            'grok',
            'ollama_turbo',
            'openai_custom',
        ];

        if (in_array($provider, $supportedProviders)) {
            return $provider;
        }

        // Si le provider n'est pas supporté, utiliser le défaut
        return $this->getDefaultProvider();
    }

    /**
     * Récupère le timeout avec fallback
     */
    public function getRequestTimeout(?int $currentValue = null): int
    {
        // Si une valeur actuelle est fournie et valide, l'utiliser
        if ($currentValue !== null && $currentValue > 0) {
            return max(15, min(600, $currentValue));
        }

        // Sinon récupérer depuis les paramètres
        $settingValue = $this->settingService->get('ai_request_timeout', 120);

        // Valider et contraindre la valeur
        $timeout = (int) ($settingValue ?? 120);

        return max(15, min(600, $timeout));
    }
}
