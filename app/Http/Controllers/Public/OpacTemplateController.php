<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicTemplate;
use App\Services\OPAC\OpacConfigurationService;
use App\Services\OPAC\TemplateEngineService;
use App\Services\OPAC\ThemeManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur pour la gestion des templates OPAC depuis le module portail
 * Version améliorée avec intégration des services OPAC
 */
class OpacTemplateController extends Controller
{
    /**
     * Affichage de la liste des templates OPAC
     */
    public function index(Request $request)
    {
        $query = PublicTemplate::where('type', 'opac');

        // Filtrage par recherche
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtrage par statut
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Tri
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        switch ($sort) {
            case 'created_at':
            case 'updated_at':
            case 'name':
                $query->orderBy($sort, $direction);
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $templates = $query->with('author')->paginate(12)->withQueryString();

        return view('public.opac-templates.index', compact('templates'));
    }

    /**
     * Affichage du formulaire de création
     */
    public function create()
    {
        return view('public.opac-templates.form');
    }

    /**
     * Sauvegarde d'un nouveau template
     */
    public function store(Request $request)
    {
        $validator = $this->validateTemplateData($request);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $template = PublicTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => 'opac',
            'content' => $request->content,
            'variables' => $request->variables ?? [],
            'parameters' => $request->variables ?? [],
            'values' => [],
            'status' => $request->status ?? 'active',
            'is_active' => $request->status === 'active',
            'author_id' => auth()->id(),
        ]);

        // Invalider le cache des templates
        Cache::forget('opac_templates');
        Cache::forget("opac_template_{$template->id}");

        return redirect()->route('public.opac-templates.index')
                        ->with('success', 'Template créé avec succès.');
    }

    /**
     * Affichage des détails d'un template
     */
    public function show(PublicTemplate $template)
    {
        if ($template->type !== 'opac') {
            abort(404);
        }

        return view('public.opac-templates.show', compact('template'));
    }

    /**
     * Affichage du formulaire d'édition
     */
    public function edit(PublicTemplate $template)
    {
        if ($template->type !== 'opac') {
            abort(404);
        }

        return view('public.opac-templates.form', compact('template'));
    }

    /**
     * Mise à jour d'un template
     */
    public function update(Request $request, PublicTemplate $template)
    {
        if ($template->type !== 'opac') {
            abort(404);
        }

        $validator = $this->validateTemplateData($request, $template->id);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $template->update([
            'name' => $request->name,
            'description' => $request->description,
            'content' => $request->content,
            'variables' => $request->variables ?? [],
            'parameters' => $request->variables ?? [],
            'values' => [],
            'status' => $request->status ?? 'active',
            'is_active' => $request->status === 'active',
        ]);

        // Invalider le cache
        Cache::forget('opac_templates');
        Cache::forget("opac_template_{$template->id}");

        return redirect()->route('public.opac-templates.show', $template)
                        ->with('success', 'Template mis à jour avec succès.');
    }

    /**
     * Suppression d'un template
     */
    public function destroy(PublicTemplate $template)
    {
        if ($template->type !== 'opac') {
            abort(404);
        }

        $templateName = $template->name;
        $template->delete();

        // Invalider le cache
        Cache::forget('opac_templates');
        Cache::forget("opac_template_{$template->id}");

        return redirect()->route('public.opac-templates.index')
                        ->with('success', "Template '{$templateName}' supprimé avec succès.");
    }

    private TemplateEngineService $templateEngine;
    private ThemeManagerService $themeManager;
    private OpacConfigurationService $configService;

    public function __construct(
        TemplateEngineService $templateEngine,
        ThemeManagerService $themeManager,
        OpacConfigurationService $configService
    ) {
        $this->templateEngine = $templateEngine;
        $this->themeManager = $themeManager;
        $this->configService = $configService;
    }

    /**
     * Aperçu d'un template avec le nouveau système de rendu
     */
    public function preview(PublicTemplate $template, Request $request)
    {
        if ($template->type !== 'opac') {
            abort(404);
        }

        // Données d'exemple pour l'aperçu
        $sampleData = [
            'documents' => $this->getSampleDocuments(),
            'pagination' => $this->getSamplePagination(),
            'searchQuery' => 'architecture moderne',
            'totalResults' => 1250,
            'filters' => [
                'type' => 'book',
                'language' => 'fr',
                'year_from' => 2020
            ]
        ];

        // Paramètres de thème temporaires si fournis
        if ($request->has('theme_preview')) {
            $themeSettings = $request->only([
                'primary_color', 'secondary_color', 'accent_color',
                'background_color', 'text_color', 'font_family', 'custom_css'
            ]);

            $previewData = $this->themeManager->previewTheme($themeSettings, $template->id);
            $sampleData['preview_css'] = $previewData['css'];
            $sampleData['preview_variables'] = $previewData['variables'];
        }

        // Rendu du template avec le nouveau moteur
        try {
            $renderedContent = $this->templateEngine->render($template, $sampleData);
        } catch (\Exception $e) {
            return view('public.opac-templates.preview-error', [
                'template' => $template,
                'error' => $e->getMessage(),
                'sampleData' => $sampleData
            ]);
        }

        return view('public.opac-templates.preview', [
            'template' => $template,
            'renderedContent' => $renderedContent,
            'sampleData' => $sampleData,
            'themeVariables' => $this->themeManager->getThemeVariables($template->id)
        ]);
    }

    /**
     * Validation AJAX de template avec le nouveau système
     */
    public function ajaxValidate(Request $request)
    {
        $content = $request->input('content', '');

        // Validation avec le moteur de templates
        $validation = $this->templateEngine->validateTemplate($content);

        // Validation des paramètres de thème si fournis
        $themeValidation = ['valid' => true, 'errors' => [], 'warnings' => []];
        if ($request->has('theme_settings')) {
            $themeSettings = $request->input('theme_settings', []);
            $themeValidation = $this->themeManager->validateThemeSettings($themeSettings);
        }

        return response()->json([
            'template' => $validation,
            'theme' => $themeValidation,
            'overall_valid' => $validation['valid'] && $themeValidation['valid']
        ]);
    }

    /**
     * Compilation et sauvegarde des assets d'un template
     */
    public function compile(PublicTemplate $template)
    {
        if ($template->type !== 'opac') {
            abort(404);
        }

        try {
            // Compiler le thème CSS
            $cssUrl = $this->themeManager->compileTheme($template->id);

            // Nettoyer le cache du template
            $this->templateEngine->clearTemplateCache($template->id);

            return response()->json([
                'success' => true,
                'css_url' => $cssUrl,
                'message' => 'Template compilé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir des documents d'exemple pour l'aperçu
     */
    private function getSampleDocuments()
    {
        return [
            (object) [
                'id' => 1,
                'title' => 'Architecture moderne et urbanisme durable',
                'author' => 'Marie Dubois',
                'description' => 'Une exploration complète des tendances architecturales contemporaines et de leur impact sur le développement urbain durable.',
                'type' => 'book',
                'language' => 'fr',
                'publication_date' => now()->subYears(2),
                'thumbnail_url' => null,
                'downloadable' => true,
                'availability_status' => 'available'
            ],
            (object) [
                'id' => 2,
                'title' => 'Digital Transformation in Libraries',
                'author' => 'John Smith',
                'description' => 'A comprehensive guide to implementing digital solutions in modern library systems.',
                'type' => 'article',
                'language' => 'en',
                'publication_date' => now()->subMonths(6),
                'thumbnail_url' => null,
                'downloadable' => false,
                'availability_status' => 'borrowed'
            ],
            (object) [
                'id' => 3,
                'title' => 'Histoire des bibliothèques françaises',
                'author' => 'Pierre Martin',
                'description' => 'Documentaire retraçant l\'évolution des bibliothèques en France depuis le Moyen Âge.',
                'type' => 'multimedia',
                'language' => 'fr',
                'publication_date' => now()->subYears(1),
                'thumbnail_url' => '/images/sample-video-thumb.jpg',
                'downloadable' => true,
                'availability_status' => 'reserved'
            ]
        ];
    }

    /**
     * Obtenir des données de pagination d'exemple
     */
    private function getSamplePagination()
    {
        return (object) [
            'currentPage' => 2,
            'lastPage' => 25,
            'total' => 1250,
            'perPage' => 20,
            'from' => 21,
            'to' => 40,
            'hasPages' => true,
            'onFirstPage' => false,
            'hasMorePages' => true
        ];
    }

    /**
     * Duplication d'un template
     */
    public function duplicate(PublicTemplate $template)
    {
        if ($template->type !== 'opac') {
            abort(404);
        }

        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copie)';
        $newTemplate->author_id = auth()->id();
        $newTemplate->status = 'inactive';
        $newTemplate->is_active = false;
        $newTemplate->save();

        Cache::forget('opac_templates');

        return redirect()->route('public.opac-templates.edit', $newTemplate)
                        ->with('success', 'Template dupliqué avec succès. Vous pouvez maintenant le modifier.');
    }

    /**
     * Export d'un template en JSON
     */
    public function export(PublicTemplate $template)
    {
        if ($template->type !== 'opac') {
            abort(404);
        }

        $exportData = [
            'name' => $template->name,
            'description' => $template->description,
            'type' => $template->type,
            'content' => $template->content,
            'variables' => $template->variables,
            'status' => $template->status,
            'exported_at' => now()->toISOString(),
            'exported_by' => auth()->user()->name ?? 'Inconnu',
        ];

        $fileName = 'template_opac_' . \Str::slug($template->name) . '_' . now()->format('Y-m-d') . '.json';

        return response()->json($exportData)
                        ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                        ->header('Content-Type', 'application/json');
    }

    /**
     * Validation des données du template
     */
    private function validateTemplateData(Request $request, $templateId = null)
    {
        $colorRegex = 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/';

        $rules = [
            'name' => 'required|string|max:255|unique:public_templates,name,' . $templateId,
            'description' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'status' => 'required|in:active,inactive',
            'variables' => 'nullable|array',
            'variables.primary_color' => $colorRegex,
            'variables.secondary_color' => $colorRegex,
            'variables.accent_color' => $colorRegex,
            'variables.background_color' => $colorRegex,
            'variables.text_color' => $colorRegex,
            'variables.custom_css' => 'nullable|string|max:10000',
        ];

        $messages = [
            'name.required' => 'Le nom du template est obligatoire.',
            'name.unique' => 'Ce nom de template existe déjà.',
            'content.required' => 'Le contenu HTML du template est obligatoire.',
            'variables.*.regex' => 'Les couleurs doivent être au format hexadécimal (#RRGGBB).',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}
