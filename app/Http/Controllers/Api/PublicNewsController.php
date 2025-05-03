<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicNews;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicNewsController extends Controller
{
    public function index()
    {
        $news = PublicNews::where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(12);
        return response()->json($news);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['user_id'] = auth()->id();

        if ($validated['is_published'] && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $news = PublicNews::create($validated);
        return response()->json($news, 201);
    }

    public function show(PublicNews $news)
    {
        if (!$news->is_published) {
            return response()->json(['message' => 'News not found'], 404);
        }
        return response()->json($news);
    }

    public function update(Request $request, PublicNews $news)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($validated['is_published'] && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $news->update($validated);
        return response()->json($news);
    }

    public function destroy(PublicNews $news)
    {
        $news->delete();
        return response()->json(null, 204);
    }

    public function latest()
    {
        $news = PublicNews::where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get();
        return response()->json($news);
    }

    public function category($category)
    {
        $news = PublicNews::where('is_published', true)
            ->where('category', $category)
            ->orderBy('published_at', 'desc')
            ->paginate(12);
        return response()->json($news);
    }

    public function search(Request $request)
    {
        $query = PublicNews::where('is_published', true);

        if ($request->has('query')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->query . '%')
                  ->orWhere('content', 'like', '%' . $request->query . '%');
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('start_date')) {
            $query->where('published_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('published_at', '<=', $request->end_date);
        }

        $news = $query->orderBy('published_at', 'desc')->paginate(12);
        return response()->json($news);
    }
}
