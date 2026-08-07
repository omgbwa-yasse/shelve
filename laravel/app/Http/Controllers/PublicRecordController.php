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
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
            'publication_notes' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
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
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
            'publication_notes' => 'nullable|string',
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

    /**
     * Autocomplétion AJAX pour la recherche de records
     */
    public function autocomplete(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3|max:255',
            'limit' => 'nullable|integer|min:1|max:5',
        ]);

        $query = $request->get('q');
        $limit = $request->get('limit', 5);

        // Recherche dans les records par nom et code
        $records = \App\Models\RecordPhysical::where(function($q) use ($query) {
            $q->where('name', 'LIKE', '%' . $query . '%')
              ->orWhere('code', 'LIKE', '%' . $query . '%');
        })
        ->select('id', 'name', 'code')
        ->limit($limit)
        ->get();

        $suggestions = $records->map(function ($record) {
            return [
                'id' => $record->id,
                'label' => $record->name . ' (' . $record->code . ')',
                'name' => $record->name,
                'code' => $record->code
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }
}
