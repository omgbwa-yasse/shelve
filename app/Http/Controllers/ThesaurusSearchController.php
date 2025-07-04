<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\NonDescriptor;
use App\Models\ExternalAlignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThesaurusSearchController extends Controller
{
    public function index()
    {
        $languages = ['fr' => 'Français', 'en' => 'Anglais', 'es' => 'Espagnol',
                      'de' => 'Allemand', 'it' => 'Italien', 'pt' => 'Portugais'];
        $statuses = ['approved' => 'Approuvé', 'candidate' => 'Candidat', 'deprecated' => 'Obsolète'];
        $categories = Term::select('category')->whereNotNull('category')->distinct()->pluck('category');

        return view('thesaurus.search.index', compact('languages', 'statuses', 'categories'));
    }

    public function search(Request $request)
    {
        // Initialiser la requête de base
        $query = Term::query();

        // Recherche par terme préféré ou non-descripteur
        if ($request->filled('query')) {
            $searchTerm = $request->input('query');

            $query->where(function($q) use ($searchTerm) {
                // Recherche dans le terme préféré
                $q->where('preferred_label', 'LIKE', "%{$searchTerm}%");

                // Recherche dans les non-descripteurs
                $q->orWhereHas('nonDescriptors', function($subQuery) use ($searchTerm) {
                    $subQuery->where('non_descriptor_label', 'LIKE', "%{$searchTerm}%");
                });
            });
        }

        // Filtres additionnels
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_top_term')) {
            $query->where('is_top_term', true);
        }

        // Recherche dans les notes et définitions
        if ($request->filled('content_search')) {
            $contentSearch = $request->content_search;

            $query->where(function($q) use ($contentSearch) {
                $q->where('definition', 'LIKE', "%{$contentSearch}%")
                  ->orWhere('scope_note', 'LIKE', "%{$contentSearch}%")
                  ->orWhere('history_note', 'LIKE', "%{$contentSearch}%")
                  ->orWhere('example', 'LIKE', "%{$contentSearch}%")
                  ->orWhere('editorial_note', 'LIKE', "%{$contentSearch}%");
            });
        }

        // Recherche par URI externe
        if ($request->filled('external_uri')) {
            $externalUri = $request->external_uri;

            $query->whereHas('externalAlignments', function($q) use ($externalUri) {
                $q->where('external_uri', 'LIKE', "%{$externalUri}%");
            });
        }

        // Recherche par vocabulaire externe
        if ($request->filled('external_vocabulary')) {
            $externalVocabulary = $request->external_vocabulary;

            $query->whereHas('externalAlignments', function($q) use ($externalVocabulary) {
                $q->where('external_vocabulary', 'LIKE', "%{$externalVocabulary}%");
            });
        }

        // Recherche par relations
        if ($request->filled('has_narrower')) {
            $query->whereHas('narrowerTerms');
        }

        if ($request->filled('has_broader')) {
            $query->whereHas('broaderTerms');
        }

        if ($request->filled('has_related')) {
            $query->whereHas('associatedTerms');
        }

        if ($request->filled('has_translations')) {
            $query->where(function($q) {
                $q->whereHas('translationsSource')
                  ->orWhereHas('translationsTarget');
            });
        }

        // Tri des résultats
        $sortBy = $request->filled('sort_by') ? $request->sort_by : 'preferred_label';
        $sortDirection = $request->filled('sort_direction') ? $request->sort_direction : 'asc';

        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $terms = $query->paginate(20)->withQueryString();

        // Préparer les filtres pour la vue
        $languages = ['fr' => 'Français', 'en' => 'Anglais', 'es' => 'Espagnol',
                      'de' => 'Allemand', 'it' => 'Italien', 'pt' => 'Portugais'];
        $statuses = ['approved' => 'Approuvé', 'candidate' => 'Candidat', 'deprecated' => 'Obsolète'];
        $categories = Term::select('category')->whereNotNull('category')->distinct()->pluck('category');

        return view('thesaurus.search.results', compact('terms', 'request', 'languages', 'statuses', 'categories'));
    }
}
