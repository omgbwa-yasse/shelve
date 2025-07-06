<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\NonDescriptor;
use Illuminate\Http\Request;

class NonDescriptorController extends Controller
{
    public function index(Term $term)
    {
        $nonDescriptors = $term->nonDescriptors;
        return view('thesaurus.non_descriptors.index', compact('term', 'nonDescriptors'));
    }

    public function create(Term $term)
    {
        $relationTypes = [
            'synonym' => 'Synonyme',
            'quasi_synonym' => 'Quasi-synonyme',
            'abbreviation' => 'Abréviation',
            'acronym' => 'Acronyme',
            'scientific_name' => 'Nom scientifique',
            'common_name' => 'Nom commun',
            'brand_name' => 'Nom de marque',
            'variant_spelling' => 'Variante orthographique',
            'old_form' => 'Forme ancienne',
            'modern_form' => 'Forme moderne',
            'antonym' => 'Antonyme'
        ];

        return view('thesaurus.non_descriptors.create', compact('term', 'relationTypes'));
    }

    public function store(Request $request, Term $term)
    {
        $request->validate([
            'non_descriptor_label' => 'required|string|max:100',
            'relation_type' => 'required|string',
            'hidden' => 'boolean',
        ]);

        NonDescriptor::create([
            'descriptor_id' => $term->id,
            'non_descriptor_label' => $request->non_descriptor_label,
            'relation_type' => $request->relation_type,
            'hidden' => $request->hidden ?? false,
        ]);

        return redirect()->route('terms.non-descriptors.index', $term->id)
            ->with('success', 'Non-descripteur ajouté avec succès.');
    }

    public function edit(Term $term, NonDescriptor $nonDescriptor)
    {
        $relationTypes = [
            'synonym' => 'Synonyme',
            'quasi_synonym' => 'Quasi-synonyme',
            'abbreviation' => 'Abréviation',
            'acronym' => 'Acronyme',
            'scientific_name' => 'Nom scientifique',
            'common_name' => 'Nom commun',
            'brand_name' => 'Nom de marque',
            'variant_spelling' => 'Variante orthographique',
            'old_form' => 'Forme ancienne',
            'modern_form' => 'Forme moderne',
            'antonym' => 'Antonyme'
        ];

        return view('thesaurus.non_descriptors.edit', compact('term', 'nonDescriptor', 'relationTypes'));
    }

    public function update(Request $request, Term $term, NonDescriptor $nonDescriptor)
    {
        $request->validate([
            'non_descriptor_label' => 'required|string|max:100',
            'relation_type' => 'required|string',
            'hidden' => 'boolean',
        ]);

        $nonDescriptor->update([
            'non_descriptor_label' => $request->non_descriptor_label,
            'relation_type' => $request->relation_type,
            'hidden' => $request->hidden ?? false,
        ]);

        return redirect()->route('terms.non-descriptors.index', $term->id)
            ->with('success', 'Non-descripteur mis à jour avec succès.');
    }

    public function destroy(Term $term, NonDescriptor $nonDescriptor)
    {
        $nonDescriptor->delete();

        return redirect()->route('terms.non-descriptors.index', $term->id)
            ->with('success', 'Non-descripteur supprimé avec succès.');
    }
}
