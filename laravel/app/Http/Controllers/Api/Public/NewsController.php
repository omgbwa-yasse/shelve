<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Public\NewsResource;
use App\Models\PublicNews;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * D15 — news du portail public. Lecture publique (guard public, sans token) :
 * seule la production publiée (`is_published`) est exposée, comme le fait le
 * contrôleur Blade OPAC\NewsController.
 */
class NewsController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/public/news — liste paginée des news publiées.
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

        $query = PublicNews::with('author')->where('is_published', true);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
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
            $query->where('published_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('published_at', '<=', $request->date('date_to'));
        }

        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder)->orderBy('created_at', 'desc');

        $page = $query->paginate(min((int) $request->get('per_page', 10), 50))->withQueryString();

        return response()->json($this->paginatedResponse($page, NewsResource::class));
    }

    /**
     * GET /api/public/news/{news} — détail d'une news publiée (404 sinon).
     */
    public function show(PublicNews $news): JsonResponse
    {
        abort_unless($news->is_published, 404);

        return response()->json(['data' => new NewsResource($news->load('author'))]);
    }

    /**
     * GET /api/public/news/latest — dernières news (équivalent du widget portail).
     */
    public function latest(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:20',
            'featured_only' => 'nullable|boolean',
        ]);

        $limit = (int) $request->get('limit', 5);

        $query = PublicNews::with('author')
            ->where('is_published', true)
            ->orderBy('published_at', 'desc');

        if ($request->boolean('featured_only')) {
            $query->where('featured', true);
        }

        $news = $query->limit($limit)->get();

        return response()->json(['data' => NewsResource::collection($news)]);
    }
}
