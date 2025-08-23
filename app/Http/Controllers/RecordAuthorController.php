<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\AuthorType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecordAuthorController extends Controller
{

    public function index()
    {
        $authors = Author::with(['authorType', 'records', 'parent'])->paginate(50);
        return view('records.authors.index', compact('authors'));
    }


    /**
     * Return a paginated and filtered list of authors
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function list(Request $request)
    {
        $query = Author::with('authorType');

        // Apply ids filter (for loading specific authors by their IDs)
        if ($request->has('ids') && !empty($request->ids)) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $authors = $query->whereIn('id', $ids)->get();

            $authors->transform(function ($author) {
                if (!$author->authorType) {
                    $author->authorType = (object)['name' => ''];
                }
                return $author;
            });

            return response()->json([
                'data' => $authors,
                'message' => $authors->isEmpty() ? 'No authors found' : null
            ]);
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Apply alphabet filter
        if ($request->has('filter') && $request->filter !== 'all') {
            $letter = $request->filter;
            $query->where(function($q) use ($letter) {
                // Filtre sur le premier mot
                $q->where('name', 'like', $letter . '%')
                // Ou filtre sur les mots après un espace
                ->orWhere('name', 'like', '% ' . $letter . '%');
            });
        }

        // Paginate results
        $perPage = 10; // Number of authors per page
        $authors = $query->orderBy('name')->paginate($perPage);

        $authors->getCollection()->transform(function ($author) {
            if (!$author->authorType) {
                $author->authorType = (object)['name' => ''];
            }
            return $author;
        });

        return response()->json([
            'data' => $authors->items(),
            'pagination' => [
                'total' => $authors->total(),
                'per_page' => $authors->perPage(),
                'current_page' => $authors->currentPage(),
                'total_pages' => $authors->lastPage()
            ],
            'message' => $authors->isEmpty() ? 'No authors found' : null
        ]);
    }



    public function storeAjax(Request $request)
    {
        try {
            $validated = $request->validate([
                'type_id' => 'required|exists:author_types,id',
                'name' => 'required|string|max:255',
                'parallel_name' => 'nullable|string|max:255',
                'other_name' => 'nullable|string|max:255',
                'lifespan' => 'nullable|string|max:255',
                'locations' => 'nullable|string|max:255',
                'parent_id' => 'nullable|exists:authors,id',
            ]);

            $author = Author::create($validated);

            // Load the authorType relationship for display
            $author->load('authorType');

            return response()->json([
                'success' => true,
                'message' => 'Author created successfully',
                'author' => $author
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating author: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating author: ' . $e->getMessage()
            ], 422);
        }
    }



    public function selectModal()
    {
        return view('partials.author_modal');
    }



    public function getAuthorTypes()
    {
        $types = AuthorType::orderBy('name')->get();
        return response()->json($types);
    }


    public function create()
    {
        $types = AuthorType::all();
        $parents = author::all();
        $parents->load('type');
        return view('records.authors.create', compact('types','parents'));
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

        return redirect()->route('record-author.index')->with('success', 'Author created successfully.');
    }


    public function show(Author $author)
    {
        $author->load('contacts');
        return view('records.authors.show', compact('author'));
    }


    public function edit(Author $author)
    {
        return view('records.authors.edit', compact('author'));
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

        return redirect()->route('record-author.index')->with('success', 'Author updated successfully');
    }


    public function destroy(Author $author)
    {
        $author->delete();

        return redirect()->route('record-author.index')->with('success', 'Author deleted successfully');
    }
}
