<?php

namespace App\Services\OPAC;

use App\Models\PublicTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Service de gestion des thèmes OPAC
 * Gestion des couleurs, styles et personnalisations visuelles
 */
class ThemeManagerService
{
    private const CACHE_PREFIX = 'opac_theme_';
    private const CACHE_TTL = 3600; // 1 heure

    private OpacConfigurationService $configService;

    public function __construct(OpacConfigurationService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Obtenir les variables CSS d'un thème
     */
    public function getThemeVariables(int $templateId): array
    {
        $cacheKey = self::CACHE_PREFIX . "variables_{$templateId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($templateId) {
            $template = PublicTemplate::find($templateId);

            if (!$template) {
                return $this->getDefaultThemeVariables();
            }

            $themeSettings = $this->configService->getThemeSettings();
            $templateVariables = $template->variables ?? [];

            return $this->mergeThemeVariables($themeSettings, $templateVariables);
        });
    }

    /**
     * Variables de thème par défaut
     */
    public function getDefaultThemeVariables(): array
    {
        return [
            'primary-color' => '#007bff',
            'secondary-color' => '#6c757d',
            'success-color' => '#28a745',
            'info-color' => '#17a2b8',
            'warning-color' => '#ffc107',
            'danger-color' => '#dc3545',
            'light-color' => '#f8f9fa',
            'dark-color' => '#343a40',
            'white-color' => '#ffffff',
            'body-bg' => '#ffffff',
            'body-color' => '#212529',
            'font-family-base' => 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'font-size-base' => '1rem',
            'line-height-base' => '1.5',
            'border-radius' => '0.375rem',
            'border-width' => '1px',
            'border-color' => '#dee2e6',
            'box-shadow' => '0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)',
            'header-bg' => '#ffffff',
            'header-color' => '#212529',
            'sidebar-bg' => '#f8f9fa',
            'sidebar-color' => '#495057',
            'footer-bg' => '#343a40',
            'footer-color' => '#ffffff',
            'card-bg' => '#ffffff',
            'card-border-color' => '#dee2e6',
            'input-bg' => '#ffffff',
            'input-border-color' => '#ced4da',
            'input-focus-border-color' => '#80bdff',
            'btn-border-radius' => '0.375rem',
            'navbar-height' => '4rem',
            'sidebar-width' => '16rem',
            'content-padding' => '1.5rem',
        ];
    }

    /**
     * Fusionner les variables de thème
     */
    private function mergeThemeVariables(array $globalSettings, array $templateVariables): array
    {
        $defaultVariables = $this->getDefaultThemeVariables();

        // Convertir les paramètres globaux en variables CSS
        $globalVariables = $this->convertSettingsToVariables($globalSettings);

        // Fusionner dans l'ordre : défaut -> global -> template
        return array_merge($defaultVariables, $globalVariables, $templateVariables);
    }

    /**
     * Convertir les paramètres de thème en variables CSS
     */
    private function convertSettingsToVariables(array $settings): array
    {
        $variables = [];

        $mapping = [
            'primary_color' => 'primary-color',
            'secondary_color' => 'secondary-color',
            'accent_color' => 'accent-color',
            'background_color' => 'body-bg',
            'text_color' => 'body-color',
            'font_family' => 'font-family-base',
        ];

        foreach ($mapping as $settingKey => $variableKey) {
            if (isset($settings[$settingKey])) {
                $variables[$variableKey] = $settings[$settingKey];
            }
        }

        return $variables;
    }

    /**
     * Générer le CSS personnalisé pour un template
     */
    public function generateCustomCSS(int $templateId): string
    {
        $variables = $this->getThemeVariables($templateId);
        $template = PublicTemplate::find($templateId);

        $css = ":root {\n";

        foreach ($variables as $name => $value) {
            $css .= "  --{$name}: {$value};\n";
        }

        $css .= "}\n\n";

        // Ajouter le CSS personnalisé du template
        if ($template && !empty($template->parameters['custom_css'])) {
            $css .= "/* CSS personnalisé */\n";
            $css .= $template->parameters['custom_css'] . "\n\n";
        }

        // Ajouter le CSS global personnalisé
        $globalCustomCSS = $this->configService->get('theme_settings.custom_css', '');
        if (!empty($globalCustomCSS)) {
            $css .= "/* CSS global personnalisé */\n";
            $css .= $globalCustomCSS . "\n";
        }

        return $css;
    }

    /**
     * Sauvegarder le CSS compilé dans un fichier
     */
    public function saveCompiledCSS(int $templateId, string $css): string
    {
        $filename = "opac-theme-{$templateId}.css";
        $path = "css/opac/themes/{$filename}";

        Storage::disk('public')->put($path, $css);

        return Storage::disk('public')->url($path);
    }

    /**
     * Obtenir l'URL du CSS compilé pour un template
     */
    public function getCompiledCSSUrl(int $templateId): ?string
    {
        $filename = "opac-theme-{$templateId}.css";
        $path = "css/opac/themes/{$filename}";

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }

    /**
     * Compiler et sauvegarder le thème pour un template
     */
    public function compileTheme(int $templateId): string
    {
        $css = $this->generateCustomCSS($templateId);
        return $this->saveCompiledCSS($templateId, $css);
    }

    /**
     * Prévisualiser un thème avec des paramètres temporaires
     */
    public function previewTheme(array $themeSettings, int $templateId): array
    {
        // Fusionner avec les variables existantes
        $currentVariables = $this->getThemeVariables($templateId);
        $previewVariables = $this->convertSettingsToVariables($themeSettings);
        $mergedVariables = array_merge($currentVariables, $previewVariables);

        return [
            'variables' => $mergedVariables,
            'css' => $this->generateCSSFromVariables($mergedVariables),
            'preview_url' => $this->generatePreviewUrl($templateId, $themeSettings),
        ];
    }

    /**
     * Générer du CSS à partir de variables
     */
    private function generateCSSFromVariables(array $variables): string
    {
        $css = ":root {\n";

        foreach ($variables as $name => $value) {
            $css .= "  --{$name}: {$value};\n";
        }

        $css .= "}";

        return $css;
    }

    /**
     * Générer une URL de prévisualisation
     */
    private function generatePreviewUrl(int $templateId, array $themeSettings): string
    {
        $params = [
            'template' => $templateId,
            'preview' => 1,
            'theme' => base64_encode(json_encode($themeSettings)),
        ];

        return route('opac.preview') . '?' . http_build_query($params);
    }

    /**
     * Valider les paramètres de thème
     */
    public function validateThemeSettings(array $settings): array
    {
        $errors = [];
        $warnings = [];

        // Validation des couleurs
        $colorFields = ['primary_color', 'secondary_color', 'accent_color', 'background_color', 'text_color'];

        foreach ($colorFields as $field) {
            if (isset($settings[$field])) {
                if (!$this->isValidColor($settings[$field])) {
                    $errors[] = "Couleur invalide pour {$field}: {$settings[$field]}";
                }
            }
        }

        // Validation de la police
        if (isset($settings['font_family'])) {
            if (strlen($settings['font_family']) > 200) {
                $errors[] = "Nom de police trop long (max 200 caractères)";
            }
        }

        // Validation du CSS personnalisé
        if (isset($settings['custom_css'])) {
            $maxLength = $this->configService->get('templates.max_custom_css_length', 10000);
            if (strlen($settings['custom_css']) > $maxLength) {
                $errors[] = "CSS personnalisé trop long (max {$maxLength} caractères)";
            }

            // Vérifier la sécurité du CSS
            $dangerousPatterns = ['javascript:', 'expression(', 'behavior:', '@import'];
            foreach ($dangerousPatterns as $pattern) {
                if (stripos($settings['custom_css'], $pattern) !== false) {
                    $errors[] = "CSS personnalisé contient du code potentiellement dangereux: {$pattern}";
                }
            }
        }

        // Validation des URLs
        $urlFields = ['logo_url', 'favicon_url'];
        foreach ($urlFields as $field) {
            if (isset($settings[$field]) && !empty($settings[$field])) {
                if (!filter_var($settings[$field], FILTER_VALIDATE_URL)) {
                    $errors[] = "URL invalide pour {$field}";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Vérifier si une couleur est valide
     */
    private function isValidColor(string $color): bool
    {
        // Couleurs hexadécimales
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color) || preg_match('/^#[0-9A-Fa-f]{3}$/', $color)) {
            return true;
        }

        // Couleurs RGB/RGBA
        if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+)?\s*\)$/', $color)) {
            return true;
        }

        // Couleurs HSL/HSLA
        if (preg_match('/^hsla?\(\s*\d+\s*,\s*\d+%\s*,\s*\d+%\s*(,\s*[\d.]+)?\s*\)$/', $color)) {
            return true;
        }

        // Couleurs nommées CSS
        $namedColors = [
            'red', 'green', 'blue', 'yellow', 'orange', 'purple', 'pink', 'brown',
            'black', 'white', 'gray', 'grey', 'cyan', 'magenta', 'lime', 'navy',
            'maroon', 'olive', 'aqua', 'fuchsia', 'silver', 'teal', 'transparent'
        ];

        return in_array(strtolower($color), $namedColors);
    }

    /**
     * Nettoyer le cache des thèmes
     */
    public function clearThemeCache(int $templateId = null): void
    {
        if ($templateId) {
            Cache::forget(self::CACHE_PREFIX . "variables_{$templateId}");
        } else {
            Cache::tags(['opac_theme'])->flush();
        }
    }

    /**
     * Obtenir les thèmes prédéfinis
     */
    public function getPredefinedThemes(): array
    {
        return [
            'default' => [
                'name' => 'Défaut',
                'description' => 'Thème par défaut moderne et épuré',
                'primary_color' => '#007bff',
                'secondary_color' => '#6c757d',
                'accent_color' => '#28a745',
            ],
            'dark' => [
                'name' => 'Sombre',
                'description' => 'Thème sombre pour réduire la fatigue oculaire',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6c757d',
                'background_color' => '#212529',
                'text_color' => '#ffffff',
            ],
            'corporate' => [
                'name' => 'Corporatif',
                'description' => 'Thème professionnel pour organisations',
                'primary_color' => '#0056b3',
                'secondary_color' => '#495057',
                'accent_color' => '#17a2b8',
            ],
            'academic' => [
                'name' => 'Académique',
                'description' => 'Thème sobre pour institutions éducatives',
                'primary_color' => '#6f42c1',
                'secondary_color' => '#495057',
                'accent_color' => '#e83e8c',
                'font_family' => 'Georgia, serif',
            ],
            'colorful' => [
                'name' => 'Coloré',
                'description' => 'Thème vibrant et dynamique',
                'primary_color' => '#ff6b6b',
                'secondary_color' => '#4ecdc4',
                'accent_color' => '#ffe66d',
            ],
        ];
    }
}
