<?php

use App\Services\SettingService;

if (!function_exists('setting')) {
    /**
     * Helper pour accéder aux paramètres de configuration
     *
     * @param string $name Nom du paramètre
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    function setting($name, $default = null)
    {
        return app(SettingService::class)->get($name, $default);
    }
}

if (!function_exists('setSetting')) {
    /**
     * Helper pour définir une valeur de paramètre
     *
     * @param string $name Nom du paramètre
     * @param mixed $value Valeur à définir
     * @return bool
     */
    function setSetting($name, $value)
    {
        return app(SettingService::class)->set($name, $value);
    }
}

if (!function_exists('resetSetting')) {
    /**
     * Helper pour réinitialiser un paramètre
     *
     * @param string $name Nom du paramètre
     * @return bool
     */
    function resetSetting($name)
    {
        return app(SettingService::class)->reset($name);
    }
}
