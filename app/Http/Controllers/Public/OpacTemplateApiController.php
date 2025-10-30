<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicTemplate;
use App\Services\OPAC\OpacConfigurationService;
use App\Services\OPAC\TemplateEngineService;
use App\Services\OPAC\ThemeManagerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * Contrôleur API pour les fonctionnalités avancées des templates OPAC
 *
 * Gère l'auto-sauvegarde, la prévisualisation temps réel,
 * la validation, l'import/export et le rendu de composants
 */
class OpacTemplateApiController extends Controller
{
    protected OpacConfigurationService $configService;
    protected TemplateEngineService $templateEngine;
    protected ThemeManagerService $themeManager;

    public function __construct(
        OpacConfigurationService $configService,
        TemplateEngineService $templateEngine,
        ThemeManagerService $themeManager
    ) {
        $this->configService = $configService;
        $this->templateEngine = $templateEngine;
        $this->themeManager = $themeManager;

        // Middleware pour les API (rate limiting, auth)
        $this->middleware('throttle:60,1')->except(['generatePreview']);
        $this->middleware('throttle:30,1')->only(['generatePreview']);
    }

    /**
     * Auto-sauvegarde d'un template en cours d'édition
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function autoSave(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'template_id' => 'required|exists:public_templates,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'category' => 'nullable|string|in:general,academic,corporate,traditional,modern',
                'status' => 'nullable|string|in:draft,active,inactive',
                'layout' => 'nullable|string',
                'css' => 'nullable|string',
                'js' => 'nullable|string',
                'variables' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $template = PublicTemplate::findOrFail($request->template_id);

            // Vérifier les permissions
            if (!$this->canEditTemplate($template)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Permissions insuffisantes'
                ], 403);
            }

            // Mise à jour des champs
            $template->update([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category ?? $template->category,
                'status' => $request->status ?? $template->status,
                'layout' => $request->layout,
                'css' => $request->css,
                'js' => $request->js,
                'variables' => $request->variables ?? [],
                'auto_saved_at' => now()
            ]);

            // Invalider le cache
            $this->clearTemplateCache($template);

            Log::info("Auto-sauvegarde template {$template->id}", [
                'user_id' => Auth::id(),
                'template_name' => $template->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template sauvegardé automatiquement',
                'timestamp' => now()->toISOString(),
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'updated_at' => $template->updated_at->toISOString()
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Erreur auto-sauvegarde template: ' . $e->getMessage(), [
                'template_id' => $request->template_id ?? null,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la sauvegarde automatique'
            ], 500);
        }
    }

    /**
     * Génération de prévisualisation temps réel
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generatePreview(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'layout' => 'required|string',
                'css' => 'nullable|string',
                'js' => 'nullable|string',
                'variables' => 'nullable|array',
                'device' => 'nullable|string|in:desktop,tablet,mobile'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Variables par défaut si non fournies
            $variables = array_merge([
                'primary_color' => '#4f46e5',
                'secondary_color' => '#6b7280',
                'accent_color' => '#f59e0b',
                'font_family' => 'Inter, system-ui, sans-serif',
                'border_radius' => '0.5rem'
            ], $request->variables ?? []);

            // Générer le CSS avec les variables
            $cssWithVariables = $this->generateThemeCSS($variables, $request->css);

            // Construire le HTML complet
            $previewHtml = $this->buildPreviewHTML(
                $request->layout,
                $cssWithVariables,
                $request->js ?? '',
                $request->device ?? 'desktop'
            );

            // Cache pour éviter les régénérations répétées
            $cacheKey = 'preview_' . md5($previewHtml);
            Cache::put($cacheKey, $previewHtml, 300); // 5 minutes

            return response()->json([
                'success' => true,
                'html' => $previewHtml,
                'cache_key' => $cacheKey,
                'generated_at' => now()->toISOString()
            ]);

        } catch (Exception $e) {
            Log::error('Erreur génération prévisualisation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la génération de la prévisualisation'
            ], 500);
        }
    }

    /**
     * Validation d'un template
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateTemplate(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'layout' => 'required|string',
                'css' => 'nullable|string',
                'js' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validationResults = [
                'html_valid' => true,
                'css_valid' => true,
                'js_valid' => true,
                'errors' => [],
                'warnings' => []
            ];

            // Validation HTML basique
            $htmlErrors = $this->validateHTML($request->layout);
            if (!empty($htmlErrors)) {
                $validationResults['html_valid'] = false;
                $validationResults['errors'] = array_merge($validationResults['errors'], $htmlErrors);
            }

            // Validation CSS
            if ($request->css) {
                $cssErrors = $this->validateCSS($request->css);
                if (!empty($cssErrors)) {
                    $validationResults['css_valid'] = false;
                    $validationResults['errors'] = array_merge($validationResults['errors'], $cssErrors);
                }
            }

            // Validation JavaScript
            if ($request->js) {
                $jsErrors = $this->validateJavaScript($request->js);
                if (!empty($jsErrors)) {
                    $validationResults['js_valid'] = false;
                    $validationResults['errors'] = array_merge($validationResults['errors'], $jsErrors);
                }
            }

            // Vérification des composants OPAC utilisés
            $componentWarnings = $this->validateOpacComponents($request->layout);
            if (!empty($componentWarnings)) {
                $validationResults['warnings'] = array_merge($validationResults['warnings'], $componentWarnings);
            }

            $validationResults['overall_valid'] =
                $validationResults['html_valid'] &&
                $validationResults['css_valid'] &&
                $validationResults['js_valid'];

            return response()->json([
                'success' => true,
                'validation' => $validationResults
            ]);

        } catch (Exception $e) {
            Log::error('Erreur validation template: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la validation'
            ], 500);
        }
    }

    /**
     * Chargement d'un template prédéfini
     *
     * @param string $templateType
     * @return JsonResponse
     */
    public function loadPredefined(string $templateType): JsonResponse
    {
        try {
            $predefinedTemplates = [
                'modern-academic' => [
                    'name' => 'Modern Academic',
                    'description' => 'Template moderne pour institutions académiques',
                    'layout' => view('opac.templates.modern-academic')->render(),
                    'css' => $this->getPredefinedCSS('modern-academic'),
                    'js' => $this->getPredefinedJS('modern-academic'),
                    'variables' => [
                        'primary_color' => '#1e3a8a',
                        'secondary_color' => '#3b82f6',
                        'accent_color' => '#f59e0b',
                        'font_family' => 'Inter, system-ui, sans-serif',
                        'border_radius' => '0.5rem'
                    ]
                ],
                'classic-library' => [
                    'name' => 'Classic Library',
                    'description' => 'Template classique pour bibliothèques traditionnelles',
                    'layout' => view('opac.templates.classic-library')->render(),
                    'css' => $this->getPredefinedCSS('classic-library'),
                    'js' => $this->getPredefinedJS('classic-library'),
                    'variables' => [
                        'primary_color' => '#7c2d12',
                        'secondary_color' => '#a16207',
                        'accent_color' => '#dc2626',
                        'font_family' => 'Georgia, serif',
                        'border_radius' => '0.25rem'
                    ]
                ],
                'corporate-clean' => [
                    'name' => 'Corporate Clean',
                    'description' => 'Template épuré pour environnements corporate',
                    'layout' => view('opac.templates.corporate-clean')->render(),
                    'css' => $this->getPredefinedCSS('corporate-clean'),
                    'js' => $this->getPredefinedJS('corporate-clean'),
                    'variables' => [
                        'primary_color' => '#1f2937',
                        'secondary_color' => '#4b5563',
                        'accent_color' => '#059669',
                        'font_family' => 'Roboto, sans-serif',
                        'border_radius' => '0.375rem'
                    ]
                ]
            ];

            if (!isset($predefinedTemplates[$templateType])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Template prédéfini non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'template' => $predefinedTemplates[$templateType]
            ]);

        } catch (Exception $e) {
            Log::error('Erreur chargement template prédéfini: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement du template prédéfini'
            ], 500);
        }
    }

    /**
     * Import d'un template depuis JSON
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'template_json' => 'required|string',
                'name' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $templateData = json_decode($request->template_json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'error' => 'JSON invalide'
                ], 400);
            }

            // Validation de la structure
            $requiredFields = ['name', 'layout'];
            foreach ($requiredFields as $field) {
                if (!isset($templateData[$field])) {
                    return response()->json([
                        'success' => false,
                        'error' => "Champ requis manquant: {$field}"
                    ], 400);
                }
            }

            return response()->json([
                'success' => true,
                'template' => [
                    'name' => $request->name ?? $templateData['name'],
                    'description' => $templateData['description'] ?? '',
                    'layout' => $templateData['layout'],
                    'css' => $templateData['css'] ?? '',
                    'js' => $templateData['js'] ?? '',
                    'variables' => $templateData['variables'] ?? []
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Erreur import template: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'import du template'
            ], 500);
        }
    }

    /**
     * Liste des composants disponibles pour un template
     *
     * @param PublicTemplate $template
     * @return JsonResponse
     */
    public function getComponents(PublicTemplate $template): JsonResponse
    {
        try {
            $availableComponents = [
                'search-bar' => [
                    'name' => 'Barre de recherche',
                    'description' => 'Barre de recherche avec filtres optionnels',
                    'props' => [
                        'showFilters' => 'boolean',
                        'placeholder' => 'string',
                        'showAdvancedLink' => 'boolean'
                    ]
                ],
                'document-card' => [
                    'name' => 'Carte document',
                    'description' => 'Affichage d\'un document avec métadonnées',
                    'props' => [
                        'showMetadata' => 'boolean',
                        'showBookmark' => 'boolean',
                        'imageHeight' => 'string'
                    ]
                ],
                'navigation' => [
                    'name' => 'Navigation',
                    'description' => 'Menu de navigation principal',
                    'props' => [
                        'style' => 'string',
                        'showSearch' => 'boolean',
                        'position' => 'string'
                    ]
                ],
                'pagination' => [
                    'name' => 'Pagination',
                    'description' => 'Navigation entre les pages de résultats',
                    'props' => [
                        'showInfo' => 'boolean',
                        'showFirstLast' => 'boolean',
                        'maxLinks' => 'number'
                    ]
                ],
                'filters' => [
                    'name' => 'Filtres',
                    'description' => 'Filtres de recherche avancée',
                    'props' => [
                        'collapsible' => 'boolean',
                        'showCounts' => 'boolean',
                        'position' => 'string'
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'components' => $availableComponents
            ]);

        } catch (Exception $e) {
            Log::error('Erreur récupération composants: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des composants'
            ], 500);
        }
    }

    /**
     * Rendu d'un composant spécifique
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function renderComponent(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'component' => 'required|string',
                'props' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $componentName = $request->component;
            $props = $request->props ?? [];

            // Vérifier si le composant existe
            $componentPath = "opac.components.{$componentName}";
            if (!view()->exists($componentPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Composant non trouvé'
                ], 404);
            }

            // Rendu du composant
            $renderedHtml = view($componentPath, $props)->render();

            return response()->json([
                'success' => true,
                'html' => $renderedHtml,
                'component' => $componentName,
                'props' => $props
            ]);

        } catch (Exception $e) {
            Log::error('Erreur rendu composant: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du rendu du composant'
            ], 500);
        }
    }

    // === Méthodes privées utilitaires ===

    /**
     * Vérifier si l'utilisateur peut éditer le template
     */
    private function canEditTemplate(PublicTemplate $template): bool
    {
        // Vérifier que l'utilisateur est authentifié
        if (!Auth::check()) {
            return false;
        }

        // Pour l'instant, tous les utilisateurs authentifiés peuvent éditer
        // TODO: Implémenter une logique de permissions plus fine basée sur les rôles
        return true;
    }

    /**
     * Vider le cache du template
     */
    private function clearTemplateCache(PublicTemplate $template): void
    {
        $cacheKeys = [
            "template_rendered_{$template->id}",
            "template_variables_{$template->id}",
            "template_css_{$template->id}"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Générer le CSS avec les variables de thème
     */
    private function generateThemeCSS(array $variables, ?string $customCSS = ''): string
    {
        $cssVariables = '';
        foreach ($variables as $key => $value) {
            $cssVariables .= "--{$key}: {$value};\n";
        }

        return ":root {\n{$cssVariables}}\n\nbody {\n    font-family: var(--font-family);\n    margin: 0;\n    padding: 20px;\n    background: #f8f9fa;\n}\n\n{$customCSS}";
    }

    /**
     * Construire le HTML complet pour la prévisualisation
     */
    private function buildPreviewHTML(string $layout, string $css, string $js, string $device = 'desktop'): string
    {
        $deviceClass = "preview-{$device}";

        return "<!DOCTYPE html>
<html lang=\"fr\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Aperçu Template</title>
    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
    <link href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css\" rel=\"stylesheet\">
    <style>{$css}</style>
</head>
<body class=\"{$deviceClass}\">
    {$layout}
    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js\"></script>
    <script>{$js}</script>
</body>
</html>";
    }

    /**
     * Validation HTML basique
     */
    private function validateHTML(string $html): array
    {
        $errors = [];

        // Vérifications basiques
        if (strpos($html, '<script') !== false && strpos($html, 'eval(') !== false) {
            $errors[] = 'JavaScript potentiellement dangereux détecté (eval)';
        }

        if (strpos($html, 'javascript:') !== false) {
            $errors[] = 'JavaScript inline détecté dans les attributs';
        }

        return $errors;
    }

    /**
     * Validation CSS basique
     */
    private function validateCSS(string $css): array
    {
        $errors = [];

        // Vérifications basiques
        if (strpos($css, 'expression(') !== false) {
            $errors[] = 'CSS expression() détecté (potentiellement dangereux)';
        }

        if (strpos($css, '@import') !== false && strpos($css, 'url(') !== false) {
            $errors[] = '@import avec URL externe détecté';
        }

        return $errors;
    }

    /**
     * Validation JavaScript basique
     */
    private function validateJavaScript(string $js): array
    {
        $errors = [];

        // Vérifications basiques
        $dangerousFunctions = ['eval', 'Function', 'setTimeout', 'setInterval'];
        foreach ($dangerousFunctions as $func) {
            if (strpos($js, $func . '(') !== false) {
                $errors[] = "Fonction potentiellement dangereuse détectée: {$func}()";
            }
        }

        return $errors;
    }

    /**
     * Validation des composants OPAC
     */
    private function validateOpacComponents(string $layout): array
    {
        $warnings = [];

        // Rechercher les composants utilisés
        if (preg_match_all('/@include\([\'"]opac\.components\.(\w+)[\'"]/', $layout, $matches)) {
            $usedComponents = $matches[1];
            $availableComponents = ['search-bar', 'document-card', 'navigation', 'pagination', 'filters'];

            foreach ($usedComponents as $component) {
                if (!in_array($component, $availableComponents)) {
                    $warnings[] = "Composant non standard utilisé: {$component}";
                }
            }
        }

        return $warnings;
    }

    /**
     * CSS prédéfini pour les templates
     */
    private function getPredefinedCSS(string $templateType): string
    {
        $cssFiles = [
            'modern-academic' => '.academic-layout { font-family: var(--font-family); }',
            'classic-library' => '.classic-layout { background: #f5f5dc; font-family: Georgia, serif; }',
            'corporate-clean' => '.corporate-layout { font-family: "Roboto", sans-serif; }'
        ];

        return $cssFiles[$templateType] ?? '';
    }

    /**
     * JavaScript prédéfini pour les templates
     */
    private function getPredefinedJS(string $templateType): string
    {
        return "console.log('Template {$templateType} chargé');";
    }
}
