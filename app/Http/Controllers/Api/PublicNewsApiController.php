<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicNews;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicNewsApiController extends Controller
{
    /**
     * Get paginated news for frontend
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'featured' => 'nullable|boolean',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => 'nullable|string|in:published_at,name,title,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = PublicNews::with(['author'])
            ->where('is_published', true);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
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
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['published_at', 'name', 'title', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('published_at', 'desc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 10), 50);
        $news = $query->paginate($perPage);

        // Transform data for consistent API response
        $transformedNews = $news->getCollection()->map(function ($article) {
            return [
                'id' => $article->id,
                'name' => $article->name,
                'title' => $article->title,
                'slug' => $article->slug,
                'summary' => $article->summary,
                'content' => $article->content,
                'featured' => $article->featured,
                'published_at' => $article->published_at,
                'image_url' => $article->image_url,
                'author' => $article->author ? [
                    'id' => $article->author->id,
                    'name' => $article->author->name,
                ] : null,
                'created_at' => $article->created_at,
                'updated_at' => $article->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedNews,
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
     * Get single news article
     */
    public function show(PublicNews $news): JsonResponse
    {
        if (!$news->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'News article not found or not published'
            ], 404);
        }

        $news->load(['author']);

        $transformedNews = [
            'id' => $news->id,
            'name' => $news->name,
            'title' => $news->title,
            'slug' => $news->slug,
            'summary' => $news->summary,
            'content' => $news->content,
            'featured' => $news->featured,
            'published_at' => $news->published_at,
            'image_url' => $news->image_url,
            'author' => $news->author ? [
                'id' => $news->author->id,
                'name' => $news->author->name,
                'email' => $news->author->email,
            ] : null,
            'created_at' => $news->created_at,
            'updated_at' => $news->updated_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $transformedNews
        ]);
    }

    /**
     * Get latest news articles
     */
    public function latest(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:20',
            'featured_only' => 'nullable|boolean',
        ]);

        $limit = $request->get('limit', 5);

        $query = PublicNews::with(['author'])
            ->where('is_published', true)
            ->orderBy('published_at', 'desc');

        if ($request->boolean('featured_only')) {
            $query->where('featured', true);
        }

        $news = $query->limit($limit)->get();

        $transformedNews = $news->map(function ($article) {
            return [
                'id' => $article->id,
                'name' => $article->name,
                'title' => $article->title,
                'slug' => $article->slug,
                'summary' => $article->summary,
                'featured' => $article->featured,
                'published_at' => $article->published_at,
                'image_url' => $article->image_url,
                'author' => $article->author ? [
                    'id' => $article->author->id,
                    'name' => $article->author->name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedNews,
            'total' => $news->count()
        ]);
    }

    /**
     * Get featured news articles
     */
    public function featured(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $limit = $request->get('limit', 10);

        $news = PublicNews::with(['author'])
            ->where('is_published', true)
            ->where('featured', true)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();

        $transformedNews = $news->map(function ($article) {
            return [
                'id' => $article->id,
                'name' => $article->name,
                'title' => $article->title,
                'slug' => $article->slug,
                'summary' => $article->summary,
                'published_at' => $article->published_at,
                'image_url' => $article->image_url,
                'author' => $article->author ? [
                    'id' => $article->author->id,
                    'name' => $article->author->name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedNews,
            'total' => $news->count()
        ]);
    }
}
