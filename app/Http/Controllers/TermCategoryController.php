<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\TermCategory;


class TermCategoryController extends Controller
{

    public function index()
    {
        $categories = TermCategory::all();
        return view('thesaurus.categories.index', compact('categories'));
    }


    public function create()
    {
        return view('thesaurus.categories.create');
    }



    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        TermCategory::create($request->all());

        return redirect()->route('term-categories.index')
            ->with('success', 'Term category created successfully.');
    }




    public function show(INT $id)
    {
        $category = TermCategory ::with('category')->findOrFail($id);
        return view('thesaurus.categories.show', compact('category'));
    }




    public function edit(INT $id)
    {
        $category = TermCategory ::findOrFail($id);
        return view('thesaurus.categories.edit', compact('category'));
    }



    public function update(Request $request, INT $id)
    {
        $category = TermCategory ::findOrFail($id);

        $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        $category->update($request->all());

        return redirect()->route('term-categories.index')
            ->with('success', 'Term category updated successfully.');
    }



    public function destroy(INT $id)
    {
        $category = TermCategory ::findOrFail($id);

        if ($category->terms->isEmpty()) {
            $category->delete();
            return redirect()->route('term-categories.index')
                ->with('success', 'Term category deleted successfully.');
        } else {
            return redirect()->route('term-categories.index')
                ->with('error', 'This category is used and cannot be deleted.');
        }
    }

}


