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
            ->available()
            ->with([
                'record.authors',
                'record.thesaurusConcepts',
                'record.attachments',
                'publisher'
            ]);

        // Search functionality
        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->searchContent($search);
        }

        // Filter by category (via record relation)
        if ($request->filled('category')) {
            $query->whereHas('record', function($q) use ($request) {
                $q->where('category', $request->get('category'));
            });
        }

        // Filter by type (via record relation)
        if ($request->filled('type')) {
            $query->whereHas('record', function($q) use ($request) {
                $q->where('type', $request->get('type'));
            });
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

        // Get available categories and types for filters from published records
        $publicRecords = PublicRecord::available()
            ->with([
                'record.authors',
                'record.thesaurusConcepts',
                'record.attachments',
                'publisher'
            ])
            ->get();

        $categories = $publicRecords
            ->map(function($publicRecord) {
                return $publicRecord->record->category ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $types = $publicRecords
            ->map(function($publicRecord) {
                return $publicRecord->record->type ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('opac.records.index', compact('records', 'categories', 'types'));
    }

    /**
     * Display the specified record
     */
    public function show($id)
    {
        $record = PublicRecord::available()
            ->with([
                'record.authors',
                'record.thesaurusConcepts',
                'record.attachments',
                'publisher'
            ])
            ->findOrFail($id);

        // Log search/view if user is authenticated
        if (Auth::guard('public')->check()) {
            // Could log the view here
        }

        // Get related records
        $relatedRecords = PublicRecord::available()
            ->with([
                'record.authors',
                'record.thesaurusConcepts',
                'record.attachments',
                'publisher'
            ])
            ->where('id', '!=', $record->id)
            ->whereHas('record', function($q) use ($record) {
                if ($record->record->category ?? null) {
                    $q->where('category', $record->record->category);
                }
                if ($record->record->type ?? null) {
                    $q->orWhere('type', $record->record->type);
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
        $publicRecords = PublicRecord::available()
            ->with([
                'record.authors',
                'record.thesaurusConcepts',
                'record.attachments',
                'publisher'
            ])
            ->get();

        $categories = $publicRecords
            ->map(function($publicRecord) {
                return $publicRecord->record->category ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $types = $publicRecords
            ->map(function($publicRecord) {
                return $publicRecord->record->type ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

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

        $suggestions = PublicRecord::available()
            ->with([
                'record.authors',
                'record.thesaurusConcepts',
                'record.attachments',
                'publisher'
            ])
            ->searchContent($term)
            ->limit(10)
            ->get();

        return response()->json($suggestions->map(function($publicRecord) {
            return [
                'id' => $publicRecord->id,
                'value' => $publicRecord->title,
                'label' => $publicRecord->title . ($publicRecord->authors ? ' - ' . $publicRecord->authors : ''),
            ];
        }));
    }
}
