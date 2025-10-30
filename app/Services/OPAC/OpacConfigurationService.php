<?php

namespace App\Services\OPAC;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Service de configuration OPAC
 * Gestion centralisée des paramètres et personnalisations
 */
class OpacConfigurationService
{
    private const CACHE_PREFIX = 'opac_config_';
    private const CACHE_TTL = 3600; // 1 heure

    /**
     * Configuration par défaut de l'OPAC
     */
    private array $defaultConfig = [
        // Interface utilisateur
        'ui' => [
            'items_per_page' => 20,
            'max_search_results' => 100,
            'search_auto_complete' => true,
            'enable_advanced_search' => true,
            'show_breadcrumbs' => true,
            'show_search_history' => true,
        ],

        // Templates et thèmes
        'templates' => [
            'allow_user_themes' => true,
            'cache_rendered_templates' => true,
            'template_cache_ttl' => 3600,
            'enable_template_customization' => true,
            'max_custom_css_length' => 10000,
        ],

        // Fonctionnalités
        'features' => [
            'enable_bookmarks' => true,
            'enable_document_sharing' => true,
            'enable_user_comments' => false,
            'enable_ratings' => false,
            'enable_download_requests' => true,
            'enable_document_preview' => true,
        ],

        // Performance
        'performance' => [
            'enable_caching' => true,
            'cache_search_results' => true,
            'cache_document_metadata' => true,
            'image_optimization' => true,
            'lazy_load_images' => true,
        ],

        // Sécurité
        'security' => [
            'rate_limit_searches' => 100, // par minute
            'session_timeout' => 120, // minutes
            'require_login_for_download' => false,
            'enable_captcha_after_failed_logins' => 3,
        ],

        // Métadonnées
        'metadata' => [
            'show_creation_date' => true,
            'show_modification_date' => false,
            'show_author_info' => true,
            'show_document_stats' => true,
            'show_related_documents' => true,
            'max_related_documents' => 5,
        ],

        // Formats et exports
        'exports' => [
            'enable_pdf_export' => true,
            'enable_csv_export' => true,
            'enable_json_export' => false,
            'max_export_items' => 1000,
        ],
    ];

    /**
     * Obtenir la configuration complète de l'OPAC
     */
    public function getConfig(string $organization = 'default'): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . $organization,
            self::CACHE_TTL,
            fn() => $this->loadConfiguration($organization)
        );
    }

    /**
     * Obtenir une valeur de configuration spécifique
     */
    public function get(string $key, $default = null, string $organization = 'default')
    {
        $config = $this->getConfig($organization);
        return data_get($config, $key, $default);
    }

    /**
     * Définir une valeur de configuration
     */
    public function set(string $key, $value, string $organization = 'default'): void
    {
        $config = $this->getConfig($organization);
        data_set($config, $key, $value);

        $this->saveConfiguration($config, $organization);
        $this->clearCache($organization);
    }

    /**
     * Obtenir les paramètres de thème pour une organisation
     */
    public function getThemeSettings(string $organization = 'default'): array
    {
        return $this->get('theme_settings', [
            'primary_color' => '#007bff',
            'secondary_color' => '#6c757d',
            'accent_color' => '#28a745',
            'background_color' => '#ffffff',
            'text_color' => '#212529',
            'font_family' => 'Inter, sans-serif',
            'custom_css' => '',
            'logo_url' => '',
            'favicon_url' => '',
            'layout_style' => 'modern',
            'sidebar_position' => 'left',
            'header_style' => 'fixed',
            'footer_style' => 'simple',
        ], $organization);
    }

    /**
     * Sauvegarder les paramètres de thème
     */
    public function setThemeSettings(array $themeSettings, string $organization = 'default'): void
    {
        // Valider les couleurs
        foreach (['primary_color', 'secondary_color', 'accent_color', 'background_color', 'text_color'] as $colorKey) {
            if (isset($themeSettings[$colorKey]) && !$this->isValidColor($themeSettings[$colorKey])) {
                throw new \InvalidArgumentException("Couleur invalide pour {$colorKey}: {$themeSettings[$colorKey]}");
            }
        }

        // Valider le CSS personnalisé
        if (isset($themeSettings['custom_css'])) {
            $maxLength = $this->get('templates.max_custom_css_length', 10000, $organization);
            if (strlen($themeSettings['custom_css']) > $maxLength) {
                throw new \InvalidArgumentException("CSS personnalisé trop long (max: {$maxLength} caractères)");
            }
        }

        $this->set('theme_settings', $themeSettings, $organization);
    }

    /**
     * Vérifier si une fonctionnalité est activée
     */
    public function isFeatureEnabled(string $feature, string $organization = 'default'): bool
    {
        return $this->get("features.enable_{$feature}", false, $organization);
    }

    /**
     * Obtenir les paramètres de performance
     */
    public function getPerformanceSettings(string $organization = 'default'): array
    {
        return $this->get('performance', $this->defaultConfig['performance'], $organization);
    }

    /**
     * Obtenir les paramètres de sécurité
     */
    public function getSecuritySettings(string $organization = 'default'): array
    {
        return $this->get('security', $this->defaultConfig['security'], $organization);
    }

    /**
     * Exporter la configuration vers un fichier JSON
     */
    public function exportConfiguration(string $organization = 'default'): string
    {
        $config = $this->getConfig($organization);

        $exportData = [
            'organization' => $organization,
            'exported_at' => now()->toISOString(),
            'version' => '1.0',
            'configuration' => $config,
        ];

        return json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Méthodes privées
     */
    private function loadConfiguration(string $organization): array
    {
        // Pour l'instant, retourner la configuration par défaut
        // TODO: Charger depuis la base de données
        return $this->defaultConfig;
    }

    private function saveConfiguration(array $config, string $organization): void
    {
        // TODO: Sauvegarder en base de données
    }

    private function clearCache(string $organization): void
    {
        Cache::forget(self::CACHE_PREFIX . $organization);
        Cache::tags(['opac_config', "opac_config_{$organization}"])->flush();
    }

    private function isValidColor(string $color): bool
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ||
               preg_match('/^#[0-9A-Fa-f]{3}$/', $color) ||
               in_array(strtolower($color), [
                   'red', 'green', 'blue', 'yellow', 'orange', 'purple', 'pink',
                   'black', 'white', 'gray', 'grey', 'brown', 'cyan', 'magenta'
               ]);
    }
}
