<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\ExternalAlignment;
use Illuminate\Http\Request;

class ExternalAlignmentController extends Controller
{
    public function index(Term $term)
    {
        $alignments = $term->externalAlignments;
        return view('thesaurus.external_alignments.index', compact('term', 'alignments'));
    }

    public function create(Term $term)
    {
        $matchTypes = [
            'exact' => 'Correspondance exacte',
            'close' => 'Correspondance proche',
            'broad' => 'Correspondance large',
            'narrow' => 'Correspondance étroite',
            'related' => 'Correspondance associée'
        ];

        return view('thesaurus.external_alignments.create', compact('term', 'matchTypes'));
    }

    public function store(Request $request, Term $term)
    {
        $request->validate([
            'external_uri' => 'required|string|max:500',
            'external_label' => 'nullable|string|max:200',
            'external_vocabulary' => 'required|string|max:100',
            'match_type' => 'required|string',
        ]);

        ExternalAlignment::create([
            'term_id' => $term->id,
            'external_uri' => $request->external_uri,
            'external_label' => $request->external_label,
            'external_vocabulary' => $request->external_vocabulary,
            'match_type' => $request->match_type,
        ]);

        return redirect()->route('terms.external-alignments.index', $term->id)
            ->with('success', 'Alignement externe ajouté avec succès.');
    }

    public function edit(Term $term, ExternalAlignment $externalAlignment)
    {
        $matchTypes = [
            'exact' => 'Correspondance exacte',
            'close' => 'Correspondance proche',
            'broad' => 'Correspondance large',
            'narrow' => 'Correspondance étroite',
            'related' => 'Correspondance associée'
        ];

        return view('thesaurus.external_alignments.edit', compact('term', 'externalAlignment', 'matchTypes'));
    }

    public function update(Request $request, Term $term, ExternalAlignment $externalAlignment)
    {
        $request->validate([
            'external_uri' => 'required|string|max:500',
            'external_label' => 'nullable|string|max:200',
            'external_vocabulary' => 'required|string|max:100',
            'match_type' => 'required|string',
        ]);

        $externalAlignment->update([
            'external_uri' => $request->external_uri,
            'external_label' => $request->external_label,
            'external_vocabulary' => $request->external_vocabulary,
            'match_type' => $request->match_type,
        ]);

        return redirect()->route('terms.external-alignments.index', $term->id)
            ->with('success', 'Alignement externe mis à jour avec succès.');
    }

    public function destroy(Term $term, ExternalAlignment $externalAlignment)
    {
        $externalAlignment->delete();

        return redirect()->route('terms.external-alignments.index', $term->id)
            ->with('success', 'Alignement externe supprimé avec succès.');
    }
}
