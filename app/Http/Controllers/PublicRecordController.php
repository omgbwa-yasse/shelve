<?php

namespace App\Http\Controllers;

use App\Models\PublicRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PublicRecordController extends Controller
{
    // Constantes pour les règles de validation
    private const VALIDATION_NULLABLE_DATE = 'nullable|date';
    private const VALIDATION_NULLABLE_STRING = 'nullable|string';
    private const VALIDATION_FILE_ATTACHMENT = 'nullable|file|max:10240';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = PublicRecord::with(['publisher', 'attachments', 'record'])
            ->available()
            ->orderBy('published_at', 'desc')
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
            'record_id' => 'required|exists:records,id',
            'published_at' => self::VALIDATION_NULLABLE_DATE,
            'expires_at' => self::VALIDATION_NULLABLE_DATE . '|after:published_at',
            'publication_notes' => self::VALIDATION_NULLABLE_STRING,
            'attachments.*' => self::VALIDATION_FILE_ATTACHMENT,
        ]);

        $validated['published_by'] = Auth::id();

        // Si pas de date de publication spécifiée, utiliser maintenant
        if (!isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

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
        // Load the record relationship to access essential data
        $record->load('record');
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
            'record_id' => 'required|exists:records,id',
            'published_at' => self::VALIDATION_NULLABLE_DATE,
            'expires_at' => self::VALIDATION_NULLABLE_DATE . '|after:published_at',
            'publication_notes' => self::VALIDATION_NULLABLE_STRING,
            'attachments.*' => self::VALIDATION_FILE_ATTACHMENT,
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
            ->available(); // Utilise le scope pour les records disponibles

        // Filtres
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->searchContent($search); // Utilise le scope de recherche
        }

        if ($request->filled('date_from')) {
            $query->where('published_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('published_at', '<=', $request->get('date_to'));
        }

        if ($request->filled('language')) {
            $query->whereHas('record', function ($q) use ($request) {
                $q->where('language_material', 'like', "%{$request->get('language')}%");
            });
        }

        // Tri par défaut : plus récents en premier
        $query->orderBy('published_at', 'desc');

        // Pagination
        $perPage = min($request->get('per_page', 10), 50); // Max 50 items par page
        $records = $query->paginate($perPage);

        // Transformer les données pour l'API en utilisant les nouveaux accesseurs
        $transformedRecords = $records->getCollection()->map(function ($publicRecord) {
            return $this->formatRecordForApi($publicRecord);
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
                'available_languages' => \App\Models\Record::distinct('language_material')
                    ->whereNotNull('language_material')
                    ->pluck('language_material')
                    ->filter()
                    ->unique()
                    ->values(),
            ]
        ]);
    }

    /**
     * API: Get single record for React frontend
     */
    public function apiShow(PublicRecord $record)
    {
        // Vérifier que le record n'est pas expiré en utilisant l'accesseur
        if ($record->is_expired) {
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
            'title' => $record->title,
            'description' => $record->content,
            'reference_number' => $record->code,
            'published_at' => $record->published_at,
            'expires_at' => $record->expires_at,
            'publication_notes' => $record->publication_notes,
            'formatted_date_range' => $record->formatted_date_range,
            'is_available' => $record->is_available,
            'is_expired' => $record->is_expired,
            'publisher' => $record->publisher ? [
                'id' => $record->publisher->id,
                'name' => $record->publisher->name,
            ] : null,
            'record_details' => [
                'date_start' => $record->date_start,
                'date_end' => $record->date_end,
                'date_exact' => $record->date_exact,
                'biographical_history' => $record->biographical_history,
                'archival_history' => $record->record->archival_history ?? '',
                'content' => $record->content,
                'access_conditions' => $record->access_conditions,
                'reproduction_conditions' => $record->record->reproduction_conditions ?? '',
                'language_material' => $record->language_material,
                'characteristic' => $record->record->characteristic ?? '',
                'finding_aids' => $record->record->finding_aids ?? '',
                'location_original' => $record->record->location_original ?? '',
                'related_unit' => $record->record->related_unit ?? '',
                'publication_note' => $record->record->publication_note ?? '',
                'note' => $record->record->note ?? '',
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
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = PublicRecord::with(['record', 'publisher'])
            ->available(); // Utilise le scope pour les records disponibles

        // Recherche textuelle dans le record associé
        $searchTerm = $validated['query'];
        $query->searchContent($searchTerm); // Utilise le scope de recherche

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

        // Pagination
        $perPage = $validated['per_page'] ?? 20;
        $records = $query->orderBy('published_at', 'desc')->paginate($perPage);

        // Transformer les données en utilisant les accesseurs
        $transformedRecords = $records->getCollection()->map(function ($publicRecord) {
            return [
                'id' => $publicRecord->id,
                'title' => $publicRecord->title,
                'description' => $publicRecord->content,
                'reference_number' => $publicRecord->code,
                'published_at' => $publicRecord->published_at,
                'formatted_date_range' => $publicRecord->formatted_date_range,
                'relevance_excerpt' => $this->generateExcerpt($publicRecord->content, $publicRecord->title),
                'is_available' => $publicRecord->is_available,
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

        $suggestions = PublicRecord::with('record')
            ->available()
            ->searchContent($query)
            ->limit(10)
            ->get()
            ->map(function ($publicRecord) {
                return [
                    'id' => $publicRecord->id,
                    'title' => $publicRecord->title,
                    'reference' => $publicRecord->code,
                    'suggestion' => $publicRecord->title . ($publicRecord->code ? ' (' . $publicRecord->code . ')' : ''),
                    'type' => 'record'
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

    // ========================================
    // MÉTHODES UTILITAIRES POUR LES DONNÉES ESSENTIELLES
    // ========================================

    /**
     * Get a formatted record for API responses
     */
    private function formatRecordForApi(PublicRecord $record)
    {
        return [
            'id' => $record->id,
            'title' => $record->title,
            'code' => $record->code,
            'content' => $record->content,
            'formatted_date_range' => $record->formatted_date_range,
            'published_at' => $record->published_at,
            'expires_at' => $record->expires_at,
            'publication_notes' => $record->publication_notes,
            'is_available' => $record->is_available,
            'is_expired' => $record->is_expired,
            'publisher' => $record->publisher ? [
                'id' => $record->publisher->id,
                'name' => $record->publisher->name,
            ] : null,
            'record_details' => [
                'date_start' => $record->date_start,
                'date_end' => $record->date_end,
                'biographical_history' => $record->biographical_history,
                'language_material' => $record->language_material,
                'access_conditions' => $record->access_conditions,
            ]
        ];
    }

    /**
     * Generate a smart excerpt from content
     */
    private function generateExcerpt($content, $title = '', $maxLength = 200)
    {
        if (empty($content)) {
            return $title ? "Document: {$title}" : 'Contenu non disponible';
        }

        if (strlen($content) <= $maxLength) {
            return $content;
        }

        // Coupe au mot le plus proche
        $excerpt = substr($content, 0, $maxLength);
        $lastSpace = strrpos($excerpt, ' ');

        if ($lastSpace !== false && $lastSpace > $maxLength * 0.8) {
            $excerpt = substr($excerpt, 0, $lastSpace);
        }

        return $excerpt . '...';
    }

    /**
     * Get statistics for API dashboard
     */
    public function apiStats()
    {
        $total = PublicRecord::count();
        $available = PublicRecord::available()->count();
        $expired = PublicRecord::where('expires_at', '<=', now())->count();
        $published_this_month = PublicRecord::where('published_at', '>=', now()->startOfMonth())->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_records' => $total,
                'available_records' => $available,
                'expired_records' => $expired,
                'published_this_month' => $published_this_month,
                'availability_rate' => $total > 0 ? round(($available / $total) * 100, 2) : 0,
            ]
        ]);
    }

    /**
     * Get filters data for frontend
     */
    public function apiFilters()
    {
        $languages = \App\Models\Record::whereNotNull('language_material')
            ->distinct('language_material')
            ->pluck('language_material')
            ->filter()
            ->sort()
            ->values();

        $publishers = \App\Models\User::whereIn('id',
            PublicRecord::distinct('published_by')->pluck('published_by')
        )->select('id', 'name')->get();

        $dateRange = PublicRecord::selectRaw('MIN(published_at) as min_date, MAX(published_at) as max_date')
            ->first();

        return response()->json([
            'success' => true,
            'filters' => [
                'languages' => $languages,
                'publishers' => $publishers,
                'date_range' => [
                    'min' => $dateRange->min_date,
                    'max' => $dateRange->max_date,
                ]
            ]
        ]);
    }
}
