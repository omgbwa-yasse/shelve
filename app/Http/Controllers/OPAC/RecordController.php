<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * OPAC Records Controller - Public interface for record search and consultation
 */
class RecordController extends Controller
{
    /**
     * Display a listing of public records with search functionality
     */
    public function index(Request $request)
    {
        $query = PublicRecord::query()
            ->where('is_public', true)
            ->where('status', 'active');

        // Search functionality
        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%')
                  ->orWhere('author', 'like', '%' . $search . '%')
                  ->orWhere('keywords', 'like', '%' . $search . '%');
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $records = $query->orderBy('created_at', 'desc')
                        ->paginate(20)
                        ->appends($request->query());

        // Get available categories and types for filters
        $categories = PublicRecord::where('is_public', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort();

        $types = PublicRecord::where('is_public', true)
            ->distinct()
            ->pluck('type')
            ->filter()
            ->sort();

        return view('opac.records.index', compact('records', 'categories', 'types'));
    }

    /**
     * Display the specified record
     */
    public function show($id)
    {
        $record = PublicRecord::where('is_public', true)
            ->where('status', 'active')
            ->findOrFail($id);

        // Log search/view if user is authenticated
        if (Auth::guard('public')->check()) {
            // Could log the view here
        }

        // Get related records
        $relatedRecords = PublicRecord::where('is_public', true)
            ->where('status', 'active')
            ->where('id', '!=', $record->id)
            ->where(function($q) use ($record) {
                if ($record->category) {
                    $q->where('category', $record->category);
                }
                if ($record->type) {
                    $q->orWhere('type', $record->type);
                }
            })
            ->limit(5)
            ->get();

        return view('opac.records.show', compact('record', 'relatedRecords'));
    }

    /**
     * Advanced search form
     */
    public function search()
    {
        $categories = PublicRecord::where('is_public', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort();

        $types = PublicRecord::where('is_public', true)
            ->distinct()
            ->pluck('type')
            ->filter()
            ->sort();

        return view('opac.records.search', compact('categories', 'types'));
    }

    /**
     * Autocomplete for search
     */
    public function autocomplete(Request $request)
    {
        if (!$request->filled('term')) {
            return response()->json([]);
        }

        $term = $request->get('term');

        $suggestions = PublicRecord::where('is_public', true)
            ->where('status', 'active')
            ->where(function($q) use ($term) {
                $q->where('title', 'like', '%' . $term . '%')
                  ->orWhere('author', 'like', '%' . $term . '%')
                  ->orWhere('keywords', 'like', '%' . $term . '%');
            })
            ->select('id', 'title', 'author')
            ->limit(10)
            ->get();

        return response()->json($suggestions->map(function($record) {
            return [
                'id' => $record->id,
                'value' => $record->title,
                'label' => $record->title . ($record->author ? ' - ' . $record->author : ''),
            ];
        }));
    }
}
