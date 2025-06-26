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
            $this->handleAttachments($request, $record);
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
            $this->handleAttachments($request, $record);
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

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($attachment->mime_type, $allowedMimeTypes)) {
            return redirect()->back()->with('error', 'Preview not available for this file type.');
        }

        return view('public.records.preview-attachment', compact('record', 'attachment'));
    }

    // ========================================
    // DEPRECATED API METHODS
    // Ces méthodes sont dépréciées - utiliser PublicRecordApiController
    // ========================================

    /**
     * @deprecated Use PublicRecordApiController@index instead
     * @see \App\Http\Controllers\Api\PublicRecordApiController::index()
     */
    public function apiIndex(Request $request)
    {
        return $this->deprecatedApiResponse('/api/public-records');
    }

    /**
     * @deprecated Use PublicRecordApiController@show instead
     * @see \App\Http\Controllers\Api\PublicRecordApiController::show()
     */
    public function apiShow(PublicRecord $record)
    {
        return $this->deprecatedApiResponse("/api/public-records/{$record->id}");
    }

    /**
     * @deprecated Use PublicRecordApiController@search instead
     * @see \App\Http\Controllers\Api\PublicRecordApiController::search()
     */
    public function apiSearch(Request $request)
    {
        return $this->deprecatedApiResponse('/api/public-records/search');
    }

    /**
     * @deprecated Use PublicRecordApiController@suggestions instead
     * @see \App\Http\Controllers\Api\PublicRecordApiController::suggestions()
     */
    public function apiSearchSuggestions(Request $request)
    {
        return $this->deprecatedApiResponse('/api/public-records/suggestions');
    }

    /**
     * @deprecated Use PublicRecordApiController@popularSearches instead
     * @see \App\Http\Controllers\Api\PublicRecordApiController::popularSearches()
     */
    public function apiPopularSearches()
    {
        return $this->deprecatedApiResponse('/api/public-records/popular-searches');
    }

    /**
     * @deprecated Use PublicRecordApiController@search instead
     * @see \App\Http\Controllers\Api\PublicRecordApiController::search()
     */
    public function apiSearchWithFacets(Request $request)
    {
        return $this->deprecatedApiResponse('/api/public-records/search');
    }

    /**
     * @deprecated Use PublicRecordApiController@export instead
     * @see \App\Http\Controllers\Api\PublicRecordApiController::export()
     */
    public function apiExport(Request $request)
    {
        return $this->deprecatedApiResponse('/api/public-records/export');
    }

    /**
     * @deprecated Use PublicRecordApiController@exportSearch instead
     * @see \App\Http\Controllers\Api\PublicRecordApiController::exportSearch()
     */
    public function apiExportSearch(Request $request)
    {
        return $this->deprecatedApiResponse('/api/public-records/export/search');
    }

    // ========================================
    // MÉTHODES PRIVÉES UTILITAIRES
    // ========================================

    /**
     * Handle file attachments for store/update operations
     */
    private function handleAttachments(Request $request, PublicRecord $record): void
    {
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

    /**
     * Return a standardized deprecated API response
     */
    private function deprecatedApiResponse(string $newEndpoint): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'This API endpoint is deprecated. Please use the new endpoint.',
            'new_endpoint' => $newEndpoint,
            'deprecated_since' => '2025-01-01',
            'will_be_removed' => '2025-06-01'
        ], 410);
    }
}
