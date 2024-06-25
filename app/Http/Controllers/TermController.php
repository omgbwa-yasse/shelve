<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\Language;
use App\Models\TermCategory;
use App\Models\TermType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermController extends Controller
{
    public function index()
    {
        $terms = Term::all();
        return view('thesaurus.terms.index', compact('terms'));
    }


    public function create()
    {
        $languages = Language::all();
        $categories = TermCategory::all();
        $types = TermType::all();
        $parents = Term::all();
        return view('thesaurus.terms.create', compact('languages', 'categories','types','parents'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'language_id' => 'required|exists:languages,id',
            'category_id' => 'required|exists:term_categories,id',
            'type_id' => 'required|exists:term_relation_types,id',
            'parent_id' => 'nullable|exists:terms,id',
        ]);

        Term::create($request->all());

        return redirect()->route('terms.index')
            ->with('success', 'Term created successfully.');
    }



    public function edit(Term $term)
    {
        $languages = Language::all();
        $categories = TermCategory::all();
        $types = TermType::all();
        $parents = Term::all();
        $term->load('parent');
        return view('thesaurus.terms.edit', compact('term', 'languages', 'categories','types','parents'));
    }




    public function show(Term $term)
    {
        $term->load('category', 'language','translations','equivalents','records','equivalentType','type','parent','children');
        return view('thesaurus.terms.show', compact('term'));
    }



    public function update(Request $request, Term $term)
{
    $request->validate([
        'name' => 'required|string|max:100',
        'description' => 'nullable|string',
        'language_id' => 'required|exists:languages,id',
        'category_id' => 'required|exists:term_categories,id',
        'type_id' => 'required|exists:term_relation_types,id',
        'parent_id' => ['nullable', Rule::exists('terms', 'id')->where(function ($query) use ($term) {
            $query->where('id', '!=', $term->id);
        })],
    ]);

    $term->update($request->all());

    return redirect()->route('terms.index')
        ->with('success', 'Term updated successfully');
}




    public function destroy(Term $term)
    {
        $term->delete();

        return redirect()->route('terms.index')
            ->with('success', 'Term deleted successfully');
    }
}


