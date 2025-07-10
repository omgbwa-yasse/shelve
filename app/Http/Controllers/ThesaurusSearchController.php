<?php

namespace App\Http\Controllers;

use App\Models\ThesaurusConcept;
use App\Models\ThesaurusLabel;
use App\Models\ThesaurusScheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThesaurusSearchController extends Controller
{
    public function index()
    {
        $languages = ['fr-fr' => 'Français', 'en-us' => 'Anglais', 'es-es' => 'Espagnol',
                      'de-de' => 'Allemand', 'it-it' => 'Italien', 'pt-pt' => 'Portugais'];
        $statuses = ['approved' => 'Approuvé', 'candidate' => 'Candidat', 'deprecated' => 'Obsolète'];
        
        // Get categories from existing concept properties or schemes
        $categories = ThesaurusScheme::select('title')->distinct()->pluck('title');

        return view('thesaurus.search.index', compact('languages', 'statuses', 'categories'));
    }

    public function search(Request $request)
    {
        // Initialiser la requête de base
        $query = ThesaurusConcept::with(['labels', 'scheme', 'notes', 'sourceRelations', 'targetRelations']);

        // Recherche par terme préféré dans les labels
        if ($request->filled('query')) {
            $searchTerm = $request->input('query');

            $query->whereHas('labels', function($q) use ($searchTerm) {
                $q->where('label_value', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filtres additionnels
        if ($request->filled('language')) {
            $query->whereHas('labels', function($q) use ($request) {
                $q->where('language', $request->language);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->whereHas('scheme', function($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->category}%");
            });
        }

        // Recherche dans les notes et définitions
        if ($request->filled('content_search')) {
            $contentSearch = $request->content_search;

            $query->whereHas('notes', function($q) use ($contentSearch) {
                $q->where('note_value', 'LIKE', "%{$contentSearch}%");
            });
        }

        // Recherche par URI externe (dans les propriétés)
        if ($request->filled('external_uri')) {
            $externalUri = $request->external_uri;

            $query->whereHas('properties', function($q) use ($externalUri) {
                $q->where('property_key', 'external_uri')
                  ->where('property_value', 'LIKE', "%{$externalUri}%");
            });
        }

        // Recherche par vocabulaire externe
        if ($request->filled('external_vocabulary')) {
            $externalVocabulary = $request->external_vocabulary;

            $query->whereHas('properties', function($q) use ($externalVocabulary) {
                $q->where('property_key', 'external_vocabulary')
                  ->where('property_value', 'LIKE', "%{$externalVocabulary}%");
            });
        }

        // Recherche par relations
        if ($request->filled('has_narrower')) {
            $query->whereHas('narrowerConcepts');
        }

        if ($request->filled('has_broader')) {
            $query->whereHas('broaderConcepts');
        }

        if ($request->filled('has_related')) {
            $query->whereHas('relatedConcepts');
        }

        // Top termes (concepts sans parent)
        if ($request->filled('is_top_term')) {
            $query->whereDoesntHave('broaderConcepts');
        }

        // Ordonner par pertinence puis par label préféré
        $concepts = $query->paginate(50);

        // Préparer les données pour la vue
        $languages = ['fr-fr' => 'Français', 'en-us' => 'Anglais', 'es-es' => 'Espagnol'];
        $statuses = ['approved' => 'Approuvé', 'candidate' => 'Candidat', 'deprecated' => 'Obsolète'];
        $categories = ThesaurusScheme::select('title')->distinct()->pluck('title');

        // Pour la compatibilité avec la vue existante, on renomme $concepts en $terms
        $terms = $concepts;

        // Réponse AJAX vs réponse normale
        if ($request->ajax()) {
            if ($request->get('action') === 'search') {
                return response()->json([
                    'html' => view('thesaurus.search.results_partial', compact('terms', 'languages', 'statuses', 'categories'))->render(),
                    'count' => $terms->total()
                ]);
            } else {
                return view('thesaurus.search.results_partial', compact('terms', 'languages', 'statuses', 'categories'));
            }
        }

        return view('thesaurus.search.results', compact('terms', 'request', 'languages', 'statuses', 'categories'));
    }
}
