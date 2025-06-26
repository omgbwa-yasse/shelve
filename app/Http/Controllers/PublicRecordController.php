<?php

namespace App\Http\Controllers;

use App\Models\PublicRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PublicRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = PublicRecord::with(['publisher', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('public.records.index', compact('records'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('public.records.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'record_type' => 'required|string|max:100',
            'reference_number' => 'required|string|max:100|unique:public_records',
            'status' => 'required|in:draft,published,archived',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        $validated['published_by'] = Auth::id();
        $record = PublicRecord::create($validated);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/records');
                $record->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('public.records.show', $record)
            ->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicRecord $record)
    {
        return view('public.records.show', compact('record'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicRecord $record)
    {
        return view('public.records.edit', compact('record'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicRecord $record)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'record_type' => 'required|string|max:100',
            'reference_number' => 'required|string|max:100|unique:public_records,reference_number,' . $record->id,
            'status' => 'required|in:draft,published,archived',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $record->update($validated);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/records');
                $record->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('public.records.show', $record)
            ->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicRecord $record)
    {
        // Delete all attachments
        foreach ($record->attachments as $attachment) {
            Storage::delete($attachment->file_path);
            $attachment->delete();
        }

        $record->delete();

        return redirect()->route('public.records.index')
            ->with('success', 'Record deleted successfully.');
    }

    /**
     * Update the status of the record.
     */
    public function updateStatus(Request $request, PublicRecord $record)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,published,archived'
        ]);

        $record->update($validated);

        return redirect()->back()
            ->with('success', 'Status updated successfully.');
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(PublicRecord $record, $attachmentId)
    {
        $attachment = $record->attachments()->findOrFail($attachmentId);

        return Storage::download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(PublicRecord $record, $attachmentId)
    {
        $attachment = $record->attachments()->findOrFail($attachmentId);

        Storage::delete($attachment->file_path);
        $attachment->delete();

        return redirect()->back()
            ->with('success', 'Attachment deleted successfully.');
    }

    /**
     * Preview an attachment.
     */
    public function previewAttachment(PublicRecord $record, $attachmentId)
    {
        $attachment = $record->attachments()->findOrFail($attachmentId);

        if (!in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'])) {
            return redirect()->back()->with('error', 'Preview not available for this file type.');
        }

        return view('public.records.preview-attachment', compact('record', 'attachment'));
    }

    // ========================================
    // API METHODS pour l'interface React
    // ========================================

    /**
     * API: Get paginated records for React frontend
     */
    public function apiIndex(Request $request)
    {
        $query = PublicRecord::with(['record', 'publisher'])
            ->whereHas('record'); // S'assurer que le record associé existe

        // Filtres
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('record', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where('published_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('published_at', '<=', $request->get('date_to'));
        }

        // Filtrer les records non expirés
        $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });

        // Tri par défaut : plus récents en premier
        $query->orderBy('published_at', 'desc');

        // Pagination
        $perPage = min($request->get('per_page', 10), 50); // Max 50 items par page
        $records = $query->paginate($perPage);

        // Transformer les données pour l'API
        $transformedRecords = $records->getCollection()->map(function ($publicRecord) {
            return [
                'id' => $publicRecord->id,
                'title' => $publicRecord->record->name ?? 'Titre non disponible',
                'description' => $publicRecord->record->content ?? '',
                'reference_number' => $publicRecord->record->code ?? '',
                'published_at' => $publicRecord->published_at,
                'expires_at' => $publicRecord->expires_at,
                'publication_notes' => $publicRecord->publication_notes,
                'publisher' => $publicRecord->publisher ? [
                    'id' => $publicRecord->publisher->id,
                    'name' => $publicRecord->publisher->name,
                ] : null,
                'record_details' => [
                    'date_start' => $publicRecord->record->date_start,
                    'date_end' => $publicRecord->record->date_end,
                    'biographical_history' => $publicRecord->record->biographical_history,
                    'language_material' => $publicRecord->record->language_material,
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedRecords,
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'from' => $records->firstItem(),
                'to' => $records->lastItem(),
            ],
            'filters' => [
                'available_languages' => \App\Models\Record::distinct('language_material')->pluck('language_material')->filter(),
            ]
        ]);
    }

    /**
     * API: Get single record for React frontend
     */
    public function apiShow(PublicRecord $record)
    {
        // Vérifier que le record n'est pas expiré
        if ($record->expires_at && $record->expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Record expired or not available'
            ], 404);
        }

        $record->load(['record', 'publisher']);

        if (!$record->record) {
            return response()->json([
                'success' => false,
                'message' => 'Associated record not found'
            ], 404);
        }

        $transformedRecord = [
            'id' => $record->id,
            'title' => $record->record->name ?? 'Titre non disponible',
            'description' => $record->record->content ?? '',
            'reference_number' => $record->record->code ?? '',
            'published_at' => $record->published_at,
            'expires_at' => $record->expires_at,
            'publication_notes' => $record->publication_notes,
            'publisher' => $record->publisher ? [
                'id' => $record->publisher->id,
                'name' => $record->publisher->name,
            ] : null,
            'record_details' => [
                'date_start' => $record->record->date_start,
                'date_end' => $record->record->date_end,
                'date_exact' => $record->record->date_exact,
                'biographical_history' => $record->record->biographical_history,
                'archival_history' => $record->record->archival_history,
                'content' => $record->record->content,
                'access_conditions' => $record->record->access_conditions,
                'reproduction_conditions' => $record->record->reproduction_conditions,
                'language_material' => $record->record->language_material,
                'characteristic' => $record->record->characteristic,
                'finding_aids' => $record->record->finding_aids,
                'location_original' => $record->record->location_original,
                'related_unit' => $record->record->related_unit,
                'publication_note' => $record->record->publication_note,
                'note' => $record->record->note,
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $transformedRecord
        ]);
    }

    /**
     * API: Search records with advanced filtering
     */
    public function apiSearch(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:255',
            'filters' => 'array',
            'filters.date_from' => 'nullable|date',
            'filters.date_to' => 'nullable|date',
            'filters.language' => 'nullable|string',
        ]);

        $query = PublicRecord::with(['record', 'publisher'])
            ->whereHas('record'); // S'assurer que le record associé existe

        // Recherche textuelle dans le record associé
        $searchTerm = $validated['query'];
        $query->whereHas('record', function ($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('content', 'like', "%{$searchTerm}%")
              ->orWhere('code', 'like', "%{$searchTerm}%")
              ->orWhere('biographical_history', 'like', "%{$searchTerm}%")
              ->orWhere('note', 'like', "%{$searchTerm}%");
        });

        // Appliquer les filtres
        $filters = $validated['filters'] ?? [];
        if (!empty($filters['date_from'])) {
            $query->where('published_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('published_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['language'])) {
            $query->whereHas('record', function ($q) use ($filters) {
                $q->where('language_material', 'like', "%{$filters['language']}%");
            });
        }

        // Filtrer les records non expirés
        $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });

        $records = $query->orderBy('published_at', 'desc')->paginate(20);

        // Transformer les données
        $transformedRecords = $records->getCollection()->map(function ($publicRecord) {
            return [
                'id' => $publicRecord->id,
                'title' => $publicRecord->record->name ?? 'Titre non disponible',
                'description' => $publicRecord->record->content ?? '',
                'reference_number' => $publicRecord->record->code ?? '',
                'published_at' => $publicRecord->published_at,
                'relevance_excerpt' => substr($publicRecord->record->content ?? '', 0, 200) . '...',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedRecords,
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
            'query' => $searchTerm,
            'filters' => $filters
        ]);
    }

    /**
     * API: Get search suggestions
     */
    public function apiSearchSuggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'suggestions' => []
            ]);
        }

        $suggestions = PublicRecord::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('reference_number', 'like', "%{$query}%");
            })
            ->select('title', 'reference_number')
            ->limit(10)
            ->get()
            ->map(function ($record) {
                return [
                    'title' => $record->title,
                    'reference' => $record->reference_number,
                    'suggestion' => $record->title . ' (' . $record->reference_number . ')'
                ];
            });

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * API: Get popular searches (mock implementation)
     */
    public function apiPopularSearches()
    {
        // Pour l'instant, retournons des recherches populaires fictives
        // Plus tard, vous pourrez implémenter un vrai système de tracking
        $popularSearches = [
            'Archives historiques',
            'Documents administratifs',
            'Correspondance officielle',
            'Rapports annuels',
            'Procès-verbaux'
        ];

        return response()->json([
            'success' => true,
            'popular_searches' => $popularSearches
        ]);
    }

    /**
     * API: Search with facets
     */
    public function apiSearchWithFacets(Request $request)
    {
        // Implementation similaire à apiSearch mais avec des facettes
        // Pour simplifier, nous retournons la même chose que apiSearch pour l'instant
        return $this->apiSearch($request);
    }

    /**
     * API: Export records (mock implementation)
     */
    public function apiExport(Request $request)
    {
        // Pour l'instant, retournons un message indiquant que l'export est en cours
        // Plus tard, vous pourrez implémenter un vrai système d'export
        return response()->json([
            'success' => true,
            'message' => 'Export request received. You will receive an email when ready.',
            'export_id' => uniqid('export_')
        ]);
    }

    /**
     * API: Export search results (mock implementation)
     */
    public function apiExportSearch(Request $request)
    {
        // Similaire à apiExport mais pour les résultats de recherche
        return response()->json([
            'success' => true,
            'message' => 'Search export request received. You will receive an email when ready.',
            'export_id' => uniqid('search_export_')
        ]);
    }
}
