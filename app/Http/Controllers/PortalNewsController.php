<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PortalNewsController extends Controller
{
    /**
     * Display a listing of the news.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $news = News::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    /**
     * Display the specified news.
     *
     * @param News $news
     * @return JsonResponse
     */
    public function show(News $news): JsonResponse
    {
        if ($news->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'News not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    /**
     * Get latest news.
     *
     * @return JsonResponse
     */
    public function latest(): JsonResponse
    {
        $news = News::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    /**
     * Search news.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = News::where('status', 'published');

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('content')) {
            $query->where('content', 'like', '%' . $request->content . '%');
        }

        if ($request->has('start_date')) {
            $query->where('published_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('published_at', '<=', $request->end_date);
        }

        $news = $query->orderBy('published_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }
}
