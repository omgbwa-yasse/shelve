<?php

namespace App\Http\Controllers;

use App\Models\PublicRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = PublicRecord::with(['user', 'attachments'])
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

        $validated['user_id'] = auth()->id();
        $record = PublicRecord::create($validated);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/records');
                $record->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
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
}
