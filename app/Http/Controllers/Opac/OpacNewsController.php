<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\PublicNews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpacNewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $news = PublicNews::orderBy('published_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'News retrieved successfully',
            'data' => $news
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('opac.news.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'required|string|max:255',
            'published_at' => 'required|date',
            'is_published' => 'boolean',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('opac/news');
            $validated['featured_image_path'] = $path;
        }

        $news = PublicNews::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'News created successfully',
            'data' => $news
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicNews $news)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'News details retrieved successfully',
            'data' => $news
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicNews $news)
    {
        return view('opac.news.edit', compact('news'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicNews $news)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'summary' => 'sometimes|string|max:255',
            'published_at' => 'sometimes|date',
            'is_published' => 'boolean',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($news->featured_image_path) {
                Storage::delete($news->featured_image_path);
            }
            $path = $request->file('featured_image')->store('opac/news');
            $validated['featured_image_path'] = $path;
        }

        $news->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'News updated successfully',
            'data' => $news
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicNews $news)
    {
        if ($news->featured_image_path) {
            Storage::delete($news->featured_image_path);
        }

        $news->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'News deleted successfully'
        ], 200);
    }

    /**
     * Toggle the featured status of the news article.
     */
    public function toggleFeatured(PublicNews $news)
    {
        $news->update(['featured' => !$news->featured]);

        return redirect()->back()
            ->with('success', 'Featured status updated successfully.');
    }

    /**
     * Update the status of the news article.
     */
    public function updateStatus(Request $request, PublicNews $news)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,published,archived'
        ]);

        $news->update($validated);

        return redirect()->back()
            ->with('success', 'Status updated successfully.');
    }
}
