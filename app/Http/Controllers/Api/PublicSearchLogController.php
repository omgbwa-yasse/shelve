<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicSearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicSearchLogController extends Controller
{
    public function index()
    {
        $logs = PublicSearchLog::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($logs);
    }

    public function show(PublicSearchLog $log)
    {
        if ($log->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($log);
    }

    public function search(Request $request)
    {
        $query = PublicSearchLog::where('user_id', auth()->id());

        if ($request->has('query')) {
            $query->where('search_term', 'like', '%' . $request->query . '%');
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(10);
        return response()->json($logs);
    }

    public function analytics()
    {
        $userId = auth()->id();

        $stats = [
            'total_searches' => PublicSearchLog::where('user_id', $userId)->count(),
            'successful_searches' => PublicSearchLog::where('user_id', $userId)
                ->where('results_count', '>', 0)
                ->count(),
            'average_results' => PublicSearchLog::where('user_id', $userId)
                ->avg('results_count'),
            'popular_categories' => PublicSearchLog::where('user_id', $userId)
                ->whereNotNull('category')
                ->select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
            'recent_searches' => PublicSearchLog::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }
}
