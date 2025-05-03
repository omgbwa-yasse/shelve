<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicPageController extends Controller
{
    public function index()
    {
        $pages = PublicPage::where('is_published', true)
            ->orderBy('order')
            ->paginate(10);
        return response()->json($pages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'order' => 'integer',
            'parent_id' => 'nullable|exists:public_pages,id',
            'is_published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $page = PublicPage::create($validated);

        return response()->json($page, 201);
    }

    public function show(PublicPage $page)
    {
        if (!$page->is_published) {
            return response()->json(['message' => 'Page not found'], 404);
        }
        return response()->json($page);
    }

    public function update(Request $request, PublicPage $page)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'order' => 'integer',
            'parent_id' => 'nullable|exists:public_pages,id',
            'is_published' => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $page->update($validated);
        return response()->json($page);
    }

    public function destroy(PublicPage $page)
    {
        $page->delete();
        return response()->json(null, 204);
    }

    public function bySlug($slug)
    {
        $page = PublicPage::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
        return response()->json($page);
    }

    public function category($category)
    {
        $pages = PublicPage::where('is_published', true)
            ->where('category', $category)
            ->orderBy('order')
            ->paginate(10);
        return response()->json($pages);
    }

    public function search(Request $request)
    {
        $query = PublicPage::where('is_published', true);

        if ($request->has('query')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->query . '%')
                  ->orWhere('content', 'like', '%' . $request->query . '%');
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $pages = $query->orderBy('order')->paginate(10);
        return response()->json($pages);
    }

    public function sitemap()
    {
        $pages = PublicPage::where('is_published', true)
            ->orderBy('order')
            ->get(['slug', 'updated_at']);
        return response()->json($pages);
    }
}
