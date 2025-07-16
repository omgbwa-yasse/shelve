<?php

namespace App\Http\Controllers;

use App\Models\ThesaurusConcept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThesaurusTranslationController extends Controller
{
    public function index(ThesaurusConcept $term)
    {
        $translations = $term->translationsSource->merge($term->translationsTarget);
        return view('thesaurus.translations.index', compact('term', 'translations'));
    }

    public function create(ThesaurusConcept $term)
    {
        // Liste des termes dans d'autres langues
        $terms = ThesaurusConcept::where('id', '!=', $term->id)
                     ->where('language', '!=', $term->language)
                     ->orderBy('preferred_label')
                     ->get();

        return view('thesaurus.translations.create', compact('term', 'terms'));
    }

    public function store(Request $request, ThesaurusConcept $term)
    {
        $request->validate([
            'target_term_id' => 'required|exists:thesaurus_concepts,id',
        ]);

        // Vérifie que la traduction n'existe pas déjà
        $exists = DB::table('translations')
            ->where(function($query) use ($term, $request) {
                $query->where('source_term_id', $term->id)
                      ->where('target_term_id', $request->target_term_id);
            })
            ->orWhere(function($query) use ($term, $request) {
                $query->where('source_term_id', $request->target_term_id)
                      ->where('target_term_id', $term->id);
            })
            ->exists();

        if ($exists) {
            return back()->with('error', 'Cette traduction existe déjà.');
        }

        // Ajoute la traduction
        $term->translationsSource()->attach($request->target_term_id);

        return redirect()->route('terms.translations.index', $term->id)
            ->with('success', 'Traduction ajoutée avec succès.');
    }

    public function destroy($sourceTermId, $targetTermId)
    {
        // Supprime la traduction dans les deux sens
        DB::table('translations')
            ->where(function($query) use ($sourceTermId, $targetTermId) {
                $query->where('source_term_id', $sourceTermId)
                      ->where('target_term_id', $targetTermId);
            })
            ->orWhere(function($query) use ($sourceTermId, $targetTermId) {
                $query->where('source_term_id', $targetTermId)
                      ->where('target_term_id', $sourceTermId);
            })
            ->delete();

        return redirect()->back()
            ->with('success', 'Traduction supprimée avec succès.');
    }
}
