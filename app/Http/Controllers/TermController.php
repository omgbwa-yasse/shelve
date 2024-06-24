<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\Language;
use App\Models\TermCategory;
use Illuminate\Http\Request;

class TermController extends Controller
{


    public function index()
    {
        $terms = Term::with('language','category')->get();
        return view('thesaurus.terms.index', compact('terms'));
    }




    public function create()
    {
        $languages = Language::all();
        $categories = TermCategory::all();

        return view('thesaurus.terms.create', compact('languages', 'categories'));
    }





    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'language_id' => 'required|exists:languages,id',
            'category_id' => 'required|exists:term_categories,id',
        ]);

        Term::create($request->all());

        return redirect()->route('terms.index')
            ->with('success', 'Term created successfully.');
    }



    public function edit(Term $term)
    {
        $languages = Language::all();
        $categories = TermCategory::all();
        return view('thesaurus.terms.edit', compact('term', 'languages', 'categories'));
    }




    public function show(Term $term)
    {
        $term->load('category');
        return view('thesaurus.terms.show', compact('term'));
    }



    public function update(Request $request, Term $term)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'language_id' => 'required|exists:languages,id',
            'category_id' => 'required|exists:term_categories,id',
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


