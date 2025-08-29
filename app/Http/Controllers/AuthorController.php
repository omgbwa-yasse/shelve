<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\AuthorType;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function indexApi()
    {
        $authors = Author::with('authorType')->get()->map(function($author) {
            return [
                'id' => $author->id,
                'name' => $author->name,
                'type_name' => $author->authorType->name ?? '',
                'type_id' => $author->type_id,
            ];
        });
        return response()->json($authors);
    }

    public function storeApi(Request $request)
    {
        $validated = $request->validate([
            'type_id' => 'required|exists:author_types,id',
            'name' => 'required|string|max:255',
            'parallel_name' => 'nullable|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'lifespan' => 'nullable|string|max:255',
            'locations' => 'nullable|string|max:255',
        ]);

        $author = Author::create($validated);
        $author->load('authorType');

        return response()->json([
            'id' => $author->id,
            'name' => $author->name,
            'type_name' => $author->authorType->name ?? '',
            'type_id' => $author->type_id,
        ]);
    }


    public function authorTypesApi()
    {
        return response()->json(AuthorType::select('id', 'name')->get());
    }




    public function index()
    {
        $authors = Author::with(['authorType', 'records', 'parent'])->paginate(25);
        $authorTypes = AuthorType::all();
        return view('authors.index', compact('authors', 'authorTypes'));
    }


    public function create()
    {
        $authorTypes = AuthorType::all();
        $parents = author::all();
        $parents->load('authorType');
        return view('authors.create', compact('authorTypes','parents'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:author_types,id',
            'name' => 'required|string|max:255',
            'parallel_name' => 'nullable|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'lifespan' => 'nullable|string|max:255',
            'locations' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:authors,id',
        ]);

        Author::create($request->all());

        return redirect()->route('mail-author.index')->with('success', 'Author created successfully.');
    }


    public function show(Author $author)
    {
        $author->load('contacts');
        return view('authors.show', compact('author'));
    }


    public function edit(Author $author)
    {
        return view('authors.edit', compact('author'));
    }


    public function update(Request $request, Author $author)
    {
        $request->validate([
            'type_id' => 'required|exists:author_types,id',
            'name' => 'required|string|max:255',
            'parallel_name' => 'nullable|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'lifespan' => 'nullable|string|max:255',
            'locations' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:authors,id',
        ]);

        $author->update($request->all());

        return redirect()->route('mail-author.index')->with('success', 'Author updated successfully');
    }


    public function destroy(Author $author)
    {
        $author->delete();

        return redirect()->route('mail-author.index')->with('success', 'Author deleted successfully');
    }
}
