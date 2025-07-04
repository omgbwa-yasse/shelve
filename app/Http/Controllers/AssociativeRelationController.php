<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssociativeRelationController extends Controller
{
    public function index(Term $term)
    {
        $associatedTerms = $term->associatedTerms;
        return view('thesaurus.associative_relations.index', compact('term', 'associatedTerms'));
    }

    public function create(Term $term)
    {
        $relationSubtypes = [
            'cause_effect' => 'Cause/Effet',
            'whole_part' => 'Tout/Partie',
            'action_agent' => 'Action/Agent',
            'action_product' => 'Action/Produit',
            'action_object' => 'Action/Objet',
            'action_location' => 'Action/Lieu',
            'science_object' => 'Science/Objet d\'étude',
            'object_property' => 'Objet/Propriété',
            'object_role' => 'Objet/Rôle',
            'raw_material_product' => 'Matière première/Produit',
            'process_neutralizer' => 'Processus/Neutraliseur',
            'object_origin' => 'Objet/Origine',
            'concept_measurement' => 'Concept/Mesure',
            'profession_person' => 'Profession/Personne',
            'general' => 'Association générale'
        ];

        $terms = Term::where('id', '!=', $term->id)
                     ->where('language', $term->language)
                     ->orderBy('preferred_label')
                     ->get();

        return view('thesaurus.associative_relations.create', compact('term', 'terms', 'relationSubtypes'));
    }

    public function store(Request $request, Term $term)
    {
        $request->validate([
            'related_term_id' => 'required|exists:terms,id',
            'relation_subtype' => 'required|string',
        ]);

        // Vérifie que le terme n'est pas déjà en relation
        $exists = $term->associatedTerms()->where('terms.id', $request->related_term_id)->exists();
        if ($exists) {
            return back()->with('error', 'Ce terme est déjà associé.');
        }

        // Ajoute la relation associative
        $term->belongsToMany(Term::class, 'associative_relations', 'term1_id', 'term2_id')
             ->withPivot('relation_subtype')
             ->withTimestamps()
             ->attach($request->related_term_id, [
                'relation_subtype' => $request->relation_subtype,
             ]);

        return redirect()->route('terms.associative-relations.index', $term->id)
            ->with('success', 'Relation associative ajoutée avec succès.');
    }

    public function destroyRelation(Term $term, $relatedTermId)
    {
        // On teste les deux directions possibles
        $relation = DB::table('associative_relations')
            ->where(function($query) use ($term, $relatedTermId) {
                $query->where('term1_id', $term->id)
                      ->where('term2_id', $relatedTermId);
            })
            ->orWhere(function($query) use ($term, $relatedTermId) {
                $query->where('term1_id', $relatedTermId)
                      ->where('term2_id', $term->id);
            })
            ->first();

        if ($relation) {
            DB::table('associative_relations')->delete($relation->id);
        }

        return redirect()->route('terms.associative-relations.index', $term->id)
            ->with('success', 'Relation associative supprimée avec succès.');
    }
}
