<?php

namespace App\Http\Controllers;

use App\Models\PublicNews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicNewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $news = PublicNews::with(['author'])
            ->orderBy('published_at', 'desc')
            ->paginate(10);
        return view('public.news.index', compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('public.news.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'required|string|max:500',
            'image' => 'nullable|image|max:2048',
            'published_at' => 'required|date',
            'status' => 'required|in:draft,published,archived',
            'featured' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/news');
            $validated['image_path'] = $path;
        }

        $validated['author_id'] = auth()->id();
        $news = PublicNews::create($validated);

        return redirect()->route('public.news.show', $news)
            ->with('success', 'News article created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicNews $news)
    {
        return view('public.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicNews $news)
    {
        return view('public.news.edit', compact('news'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicNews $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'required|string|max:500',
            'image' => 'nullable|image|max:2048',
            'published_at' => 'required|date',
            'status' => 'required|in:draft,published,archived',
            'featured' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($news->image_path) {
                Storage::delete($news->image_path);
            }
            $path = $request->file('image')->store('public/news');
            $validated['image_path'] = $path;
        }

        $news->update($validated);

        return redirect()->route('public.news.show', $news)
            ->with('success', 'News article updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicNews $news)
    {
        if ($news->image_path) {
            Storage::delete($news->image_path);
        }

        $news->delete();

        return redirect()->route('public.news.index')
            ->with('success', 'News article deleted successfully.');
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

    // ========================================
    // API METHODS pour l'interface React
    // ========================================

    /**
     * API: Get paginated news for React frontend
     */
    public function apiIndex(Request $request)
    {
        $query = PublicNews::with(['author'])
            ->where('status', 'published');

        // Filtres
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        if ($request->filled('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        if ($request->filled('date_from')) {
            $query->where('published_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('published_at', '<=', $request->get('date_to'));
        }

        // Tri
        $sortBy = $request->get('sort_by', 'published_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = min($request->get('per_page', 10), 50);
        $news = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $news->items(),
            'pagination' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
                'from' => $news->firstItem(),
                'to' => $news->lastItem(),
            ]
        ]);
    }

    /**
     * API: Get single news article for React frontend
     */
    public function apiShow(PublicNews $news)
    {
        if ($news->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'News article not found or not published'
            ], 404);
        }

        $news->load(['author']);

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    /**
     * API: Get latest news articles
     */
    public function apiLatest(Request $request)
    {
        $limit = min($request->get('limit', 5), 20); // Max 20 articles

        $news = PublicNews::with(['author'])
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }
}
