<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;

class HierarchicalRelationController extends Controller
{
    public function index(Term $term)
    {
        $broaderTerms = $term->broaderTerms;
        $narrowerTerms = $term->narrowerTerms;
        return view('thesaurus.hierarchical_relations.index', compact('term', 'broaderTerms', 'narrowerTerms'));
    }

    public function createBroader(Term $term)
    {
        $relationTypes = [
            'generic' => 'Générique (TG/TS)',
            'partitive' => 'Partitif (TGP/TSP)',
            'instance' => 'Instance (TGI/TSI)'
        ];
        $terms = Term::where('id', '!=', $term->id)
                     ->where('language', $term->language)
                     ->orderBy('preferred_label')
                     ->get();

        return view('thesaurus.hierarchical_relations.create_broader', compact('term', 'terms', 'relationTypes'));
    }

    public function storeBroader(Request $request, Term $term)
    {
        $request->validate([
            'broader_term_id' => 'required|exists:terms,id',
            'relation_type' => 'required|string',
        ]);

        // Vérifie que le terme n'est pas déjà en relation
        $exists = $term->broaderTerms()->where('terms.id', $request->broader_term_id)->exists();
        if ($exists) {
            return back()->with('error', 'Ce terme est déjà associé comme terme générique.');
        }

        // Ajoute le terme générique
        $term->broaderTerms()->attach($request->broader_term_id, [
            'relation_type' => $request->relation_type,
        ]);

        return redirect()->route('terms.hierarchical-relations.index', $term->id)
            ->with('success', 'Relation hiérarchique ajoutée avec succès.');
    }

    public function createNarrower(Term $term)
    {
        $relationTypes = [
            'generic' => 'Générique (TG/TS)',
            'partitive' => 'Partitif (TGP/TSP)',
            'instance' => 'Instance (TGI/TSI)'
        ];
        $terms = Term::where('id', '!=', $term->id)
                     ->where('language', $term->language)
                     ->orderBy('preferred_label')
                     ->get();

        return view('thesaurus.hierarchical_relations.create_narrower', compact('term', 'terms', 'relationTypes'));
    }

    public function storeNarrower(Request $request, Term $term)
    {
        $request->validate([
            'narrower_term_id' => 'required|exists:terms,id',
            'relation_type' => 'required|string',
        ]);

        // Vérifie que le terme n'est pas déjà en relation
        $exists = $term->narrowerTerms()->where('terms.id', $request->narrower_term_id)->exists();
        if ($exists) {
            return back()->with('error', 'Ce terme est déjà associé comme terme spécifique.');
        }

        // Ajoute le terme spécifique
        $term->narrowerTerms()->attach($request->narrower_term_id, [
            'relation_type' => $request->relation_type,
        ]);

        return redirect()->route('terms.hierarchical-relations.index', $term->id)
            ->with('success', 'Relation hiérarchique ajoutée avec succès.');
    }

    public function destroyRelation(Term $term, $relationType, $relatedTermId)
    {
        if ($relationType === 'broader') {
            $term->broaderTerms()->detach($relatedTermId);
        } else if ($relationType === 'narrower') {
            $term->narrowerTerms()->detach($relatedTermId);
        }

        return redirect()->route('terms.hierarchical-relations.index', $term->id)
            ->with('success', 'Relation hiérarchique supprimée avec succès.');
    }
}
