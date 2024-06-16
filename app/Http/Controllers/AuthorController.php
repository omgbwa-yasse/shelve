<?php

namespace App\Http\Controllers;
use App\Models\Author;
use App\Models\AuthorType;
use Illuminate\Http\Request;


class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::with('authorType')->get();
        return view('authors.contacts.index', compact('authors'));
    }

    public function create()
    {
        $authorTypes = AuthorType::all();
        $parents = Author::all();
        return view('authors.contacts.create', compact('authorTypes', 'parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:author_types,id',
            'name' => 'required|unique:authors|max:100',
            'parallel_name' => 'nullable|max:100',
            'other_name' => 'nullable|max:100',
            'lifespan' => 'nullable|max:100',
            'locations' => 'nullable|max:100',
            'parent_id' => 'nullable|exists:authors,id',
        ]);

        Author::create($request->all());

        return redirect()->route('mail-author.index')
            ->with('success', 'Author created successfully.');
    }


        public function show(Author $author)
    {
        return view('authors.contacts.show', compact('author'));
    }

    public function edit(Author $author)
    {
        $authorTypes = AuthorType::all();
        $parents = Author::all();
        return view('authors.contacts.edit', compact('author', 'authorTypes', 'parents'));
    }

    public function update(Request $request, Author $author)
    {
        $request->validate([
            'type_id' => 'required|exists:author_types,id',
            'name' => 'required|unique:authors,name,' . $author->id . '|max:100',
            'parallel_name' => 'nullable|max:100',
            'other_name' => 'nullable|max:100',
            'lifespan' => 'nullable|max:100',
            'locations' => 'nullable|max:100',
            'parent_id' => 'nullable|exists:authors,id',
        ]);

        $author->update($request->all());

        return redirect()->route('mail-author.index')
            ->with('success', 'Author updated successfully.');
    }

    public function destroy(Author $author)
    {
        $author->delete();

        return redirect()->route('mail-author.index')
            ->with('success', 'Author deleted successfully.');
    }
}
