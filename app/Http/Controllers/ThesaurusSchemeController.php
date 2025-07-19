<?php

namespace App\Http\Controllers;

use App\Models\ThesaurusScheme;
use App\Models\ThesaurusNamespace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ThesaurusSchemeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schemes = ThesaurusScheme::with(['namespace', 'concepts'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('thesaurus.Schemes.index', compact('schemes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('thesaurus.Schemes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'identifier' => 'required|string|max:50|unique:thesaurus_schemes',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'language' => 'required|string|max:10',
                'namespace_uri' => 'nullable|url|max:255',
            ]);

            DB::beginTransaction();

            // Générer une URI unique basée sur l'identifiant
            $baseUri = config('app.url') . '/thesaurus/schemes/';
            $uri = $baseUri . Str::slug($validated['identifier']);

            // Créer le schéma
            $scheme = new ThesaurusScheme();
            $scheme->identifier = $validated['identifier'];
            $scheme->title = $validated['title'];
            $scheme->description = $validated['description'] ?? null;
            $scheme->language = $validated['language'];
            $scheme->uri = $uri;
            $scheme->save();

            // Créer un namespace si URI fourni
            if (!empty($validated['namespace_uri'])) {
                try {
                    $namespace = new ThesaurusNamespace();
                    $namespace->prefix = $validated['identifier'];
                    $namespace->namespace_uri = $validated['namespace_uri'];
                    $namespace->description = 'Namespace for ' . $validated['title'];
                    $namespace->save();

                    // Mise à jour du schéma avec l'ID du namespace
                    $scheme->namespace_id = $namespace->id;
                    $scheme->save();
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la création du namespace: ' . $e->getMessage());
                    // On continue sans namespace
                }
            }

            DB::commit();
            return redirect()->route('schemes.index')
                ->with('success', 'Schéma de thésaurus créé avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la création du schéma de thésaurus: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ThesaurusScheme $scheme)
    {
        $scheme->load(['namespace', 'concepts.labels', 'concepts.relations', 'topConcepts']);
        return view('thesaurus.Schemes.show', compact('scheme'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ThesaurusScheme $scheme)
    {
        $scheme->load('namespace');
        return view('thesaurus.Schemes.edit', compact('scheme'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ThesaurusScheme $scheme)
    {
        try {
            $validated = $request->validate([
                'identifier' => 'required|string|max:50|unique:thesaurus_schemes,identifier,' . $scheme->id,
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'language' => 'required|string|max:10',
                'uri' => 'required|url|max:255|unique:thesaurus_schemes,uri,' . $scheme->id,
                'namespace_uri' => 'nullable|url|max:255',
            ]);

            DB::beginTransaction();

            // Mettre à jour le schéma
            $scheme->identifier = $validated['identifier'];
            $scheme->title = $validated['title'];
            $scheme->description = $validated['description'] ?? null;
            $scheme->language = $validated['language'];
            $scheme->uri = $validated['uri'];

            // Gérer le namespace
            if (!empty($validated['namespace_uri'])) {
                if ($scheme->namespace) {
                    // Mettre à jour le namespace existant
                    $scheme->namespace->namespace_uri = $validated['namespace_uri'];
                    $scheme->namespace->prefix = $validated['identifier'];
                    $scheme->namespace->description = 'Namespace for ' . $validated['title'];
                    $scheme->namespace->save();
                } else {
                    // Créer un nouveau namespace
                    $namespace = new ThesaurusNamespace();
                    $namespace->prefix = $validated['identifier'];
                    $namespace->namespace_uri = $validated['namespace_uri'];
                    $namespace->description = 'Namespace for ' . $validated['title'];
                    $namespace->save();

                    $scheme->namespace_id = $namespace->id;
                }
            } elseif ($scheme->namespace_id && empty($validated['namespace_uri'])) {
                // Supprimer la relation avec le namespace si on a retiré l'URI
                $scheme->namespace_id = null;
            }

            $scheme->save();

            DB::commit();
            return redirect()->route('schemes.show', $scheme->id)
                ->with('success', 'Schéma de thésaurus modifié avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la modification du schéma de thésaurus: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ThesaurusScheme $scheme)
    {
        try {
            DB::beginTransaction();

            // Vérifier si le schéma a des concepts
            $conceptsCount = $scheme->concepts->count();

            if ($conceptsCount > 0) {
                // Supprimer les concepts associés (sera fait par les contraintes de cascade en BDD)
                // Mais on peut ajouter un log pour information
                Log::info("Suppression de {$conceptsCount} concepts liés au schéma {$scheme->identifier}");
            }

            // Supprimer le namespace associé si celui-ci n'est plus utilisé par d'autres schémas
            if ($scheme->namespace && $scheme->namespace->schemes->count() <= 1) {
                $scheme->namespace->delete();
            }

            // Supprimer le schéma
            $scheme->delete();

            DB::commit();
            return redirect()->route('schemes.index')
                ->with('success', 'Schéma de thésaurus supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la suppression du schéma de thésaurus: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }
}
