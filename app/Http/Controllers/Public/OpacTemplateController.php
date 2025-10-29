<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur pour la gestion des templates OPAC depuis le module portail
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
        $validator = $this->validateTemplate($request);

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

        $validator = $this->validateTemplate($request, $template->id);

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

    /**
     * Aperçu d'un template
     */
    public function preview(PublicTemplate $template, Request $request)
    {
        if ($template->type !== 'opac') {
            abort(404);
        }

        // Variables par défaut
        $variables = $template->variables ?? [];

        // Surcharge avec les variables de personnalisation si présentes
        if ($request->has('customize')) {
            $customVars = $request->only([
                'primary_color', 'secondary_color', 'accent_color',
                'background_color', 'text_color'
            ]);
            $variables = array_merge($variables, array_filter($customVars));
        }

        // Variables globales pour le rendu
        $globalVars = [
            'library_name' => config('app.name', 'Bibliothèque'),
            'locale' => app()->getLocale(),
            'current_date' => now()->format('Y'),
            'total_records' => 1250, // Exemple
        ];

        $allVars = array_merge($variables, $globalVars);

        // Traitement du contenu HTML
        $processedContent = $template->content;
        foreach ($allVars as $key => $value) {
            $processedContent = str_replace("{{{$key}}}", $value, $processedContent);
        }

        // Traitement du CSS
        $processedCss = $variables['custom_css'] ?? '';
        foreach ($allVars as $key => $value) {
            $processedCss = str_replace("{{{$key}}}", $value, $processedCss);
        }

        return view('public.opac-templates.preview', [
            'template' => $template,
            'processedContent' => $processedContent,
            'processedCss' => $processedCss,
            'libraryName' => $globalVars['library_name']
        ]);
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
    private function validateTemplate(Request $request, $templateId = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:public_templates,name,' . $templateId,
            'description' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'status' => 'required|in:active,inactive',
            'variables' => 'nullable|array',
            'variables.primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'variables.secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'variables.accent_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'variables.background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'variables.text_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
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
