<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\PublicPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpacPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = PublicPage::orderBy('title')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Pages retrieved successfully',
            'data' => $pages
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('opac.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:public_pages',
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ]);

        $page = PublicPage::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Page created successfully',
            'data' => $page
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicPage $page)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Page details retrieved successfully',
            'data' => $page
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicPage $page)
    {
        return view('opac.pages.edit', compact('page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicPage $page)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:public_pages,slug,' . $page->id,
            'content' => 'sometimes|string',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ]);

        $page->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Page updated successfully',
            'data' => $page
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicPage $page)
    {
        $page->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Page deleted successfully'
        ], 200);
    }

    /**
     * Update the status of the page.
     */
    public function updateStatus(Request $request, PublicPage $page)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,published,archived'
        ]);

        $page->update($validated);

        return redirect()->back()
            ->with('success', 'Status updated successfully.');
    }
}
