<?php

namespace App\Services\OPAC;

use App\Models\PublicTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/**
 * Service de moteur de templates OPAC
 * Gestion du rendu et de la compilation des templates
 */
class TemplateEngineService
{
    private const CACHE_PREFIX = 'opac_template_';
    private const CACHE_TTL = 3600; // 1 heure

    private OpacConfigurationService $configService;
    private ThemeManagerService $themeService;

    public function __construct(
        OpacConfigurationService $configService,
        ThemeManagerService $themeService
    ) {
        $this->configService = $configService;
        $this->themeService = $themeService;
    }

    /**
     * Rendre un template avec des données
     */
    public function render(PublicTemplate $template, array $data = []): string
    {
        $cacheEnabled = $this->configService->get('templates.cache_rendered_templates', true);
        $cacheKey = self::CACHE_PREFIX . $template->id . '_' . md5(serialize($data));

        if ($cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $renderedContent = $this->compileTemplate($template, $data);

        if ($cacheEnabled) {
            $cacheTtl = $this->configService->get('templates.template_cache_ttl', self::CACHE_TTL);
            Cache::put($cacheKey, $renderedContent, $cacheTtl);
        }

        return $renderedContent;
    }

    /**
     * Compiler un template avec les composants et variables
     */
    public function compileTemplate(PublicTemplate $template, array $data = []): string
    {
        $content = $template->content;

        // Injecter les variables du thème
        $themeVariables = $this->themeService->getThemeVariables($template->id);
        $content = $this->injectThemeVariables($content, $themeVariables);

        // Parser et remplacer les composants
        $content = $this->parseComponents($content, $data);

        // Remplacer les variables personnalisées
        $content = $this->replaceCustomVariables($content, $template->variables ?? [], $data);

        // Appliquer les filtres et transformations
        $content = $this->applyFilters($content, $template);

        return $content;
    }

    /**
     * Parser et remplacer les composants dans le template
     */
    private function parseComponents(string $content, array $data): string
    {
        // Pattern pour identifier les composants : {{component:nom-composant param1="valeur1" param2="valeur2"}}
        $pattern = '/\{\{component:([a-z\-]+)([^}]*)\}\}/';

        return preg_replace_callback($pattern, function ($matches) use ($data) {
            $componentName = $matches[1];
            $parametersString = trim($matches[2]);

            // Parser les paramètres
            $parameters = $this->parseComponentParameters($parametersString);

            // Merger avec les données globales
            $componentData = array_merge($data, $parameters);

            return $this->renderComponent($componentName, $componentData);
        }, $content);
    }

    /**
     * Parser les paramètres d'un composant
     */
    private function parseComponentParameters(string $parametersString): array
    {
        $parameters = [];

        if (empty($parametersString)) {
            return $parameters;
        }

        // Pattern pour capturer param="valeur" ou param='valeur'
        $pattern = '/(\w+)=(["\'])([^"\']*)\2/';

        preg_match_all($pattern, $parametersString, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $parameters[$match[1]] = $match[3];
        }

        return $parameters;
    }

    /**
     * Rendre un composant spécifique
     */
    private function renderComponent(string $componentName, array $data): string
    {
        $componentViewPath = "opac.components.{$componentName}";

        if (!View::exists($componentViewPath)) {
            return "<!-- Composant {$componentName} introuvable -->";
        }

        try {
            return View::make($componentViewPath, $data)->render();
        } catch (\Exception $e) {
            return "<!-- Erreur lors du rendu du composant {$componentName}: {$e->getMessage()} -->";
        }
    }

    /**
     * Injecter les variables CSS du thème
     */
    private function injectThemeVariables(string $content, array $themeVariables): string
    {
        $cssVariables = '';

        foreach ($themeVariables as $key => $value) {
            $cssVariables .= "--{$key}: {$value};\n";
        }

        // Injecter dans la balise :root ou style
        if (strpos($content, ':root') !== false) {
            $content = preg_replace(
                '/(:root\s*{[^}]*)(})/',
                '$1' . $cssVariables . '$2',
                $content
            );
        } else {
            // Ajouter un bloc :root si inexistant
            $styleBlock = "<style>:root {\n{$cssVariables}}</style>\n";
            $content = $styleBlock . $content;
        }

        return $content;
    }

    /**
     * Remplacer les variables personnalisées
     */
    private function replaceCustomVariables(string $content, array $templateVariables, array $data): string
    {
        $variables = array_merge($templateVariables, $data);

        foreach ($variables as $key => $value) {
            $placeholder = "{{$key}}";
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    /**
     * Appliquer les filtres et transformations
     */
    private function applyFilters(string $content, PublicTemplate $template): string
    {
        // Minification HTML si activée
        if ($this->configService->get('performance.minify_html', true)) {
            $content = $this->minifyHtml($content);
        }

        // Optimisation des images si activée
        if ($this->configService->get('performance.image_optimization', true)) {
            $content = $this->optimizeImages($content);
        }

        // Lazy loading des images si activé
        if ($this->configService->get('performance.lazy_load_images', true)) {
            $content = $this->addLazyLoading($content);
        }

        return $content;
    }

    /**
     * Minifier le HTML
     */
    private function minifyHtml(string $html): string
    {
        // Supprimer les commentaires HTML (sauf ceux contenant des conditions IE)
        $html = preg_replace('/<!--(?!\[if).*?-->/s', '', $html);

        // Réduire les espaces multiples
        $html = preg_replace('/\s+/', ' ', $html);

        // Supprimer les espaces autour des balises
        $html = preg_replace('/>\s+</', '><', $html);

        return trim($html);
    }

    /**
     * Optimiser les balises d'images
     */
    private function optimizeImages(string $content): string
    {
        // Ajouter des attributs de chargement optimisé
        $content = preg_replace(
            '/<img([^>]*)src="([^"]*)"([^>]*)>/',
            '<img$1src="$2"$3 loading="lazy" decoding="async">',
            $content
        );

        return $content;
    }

    /**
     * Ajouter le lazy loading aux images
     */
    private function addLazyLoading(string $content): string
    {
        // Ajouter loading="lazy" si pas déjà présent
        $content = preg_replace(
            '/<img(?![^>]*loading=)([^>]*)>/',
            '<img loading="lazy"$1>',
            $content
        );

        return $content;
    }

    /**
     * Obtenir les templates disponibles pour l'OPAC
     */
    public function getAvailableTemplates(): \Illuminate\Database\Eloquent\Collection
    {
        return PublicTemplate::where('type', 'opac')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Valider la syntaxe d'un template
     */
    public function validateTemplate(string $content): array
    {
        $errors = [];
        $warnings = [];

        // Vérifier la syntaxe des composants
        $componentPattern = '/\{\{component:([a-z\-]+)([^}]*)\}\}/';
        preg_match_all($componentPattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $componentName = $match[1];

            if (!View::exists("opac.components.{$componentName}")) {
                $errors[] = "Composant '{$componentName}' introuvable";
            }
        }

        // Vérifier les variables non fermées
        $variablePattern = '/\{[^}]*(?!\})/';
        if (preg_match($variablePattern, $content)) {
            $warnings[] = "Variables potentiellement mal fermées détectées";
        }

        // Vérifier la présence de balises HTML de base
        $requiredTags = ['html', 'head', 'body'];
        foreach ($requiredTags as $tag) {
            if (strpos($content, "<{$tag}") === false) {
                $warnings[] = "Balise '{$tag}' manquante";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Nettoyer le cache des templates
     */
    public function clearTemplateCache(int $templateId = null): void
    {
        if ($templateId) {
            $pattern = self::CACHE_PREFIX . $templateId . '_*';
            Cache::flush(); // Laravel ne supporte pas les patterns, on vide tout
        } else {
            Cache::flush();
        }
    }
}
