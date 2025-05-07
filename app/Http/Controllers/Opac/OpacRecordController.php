<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\OpacRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpacRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = OpacRecord::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('call_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'message' => 'Records retrieved successfully',
            'data' => $records
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('opac.records.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:13',
            'call_number' => 'required|string|max:50',
            'type' => 'required|in:book,journal,newspaper,other',
            'status' => 'required|in:available,checked_out,reserved,maintenance',
            'description' => 'nullable|string',
            'publication_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'publisher' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        $record = OpacRecord::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Record created successfully',
            'data' => $record
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OpacRecord $record)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Record details retrieved successfully',
            'data' => $record
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OpacRecord $record)
    {
        return view('opac.records.edit', compact('record'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OpacRecord $record)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'record_type' => 'required|string|max:100',
            'reference_number' => 'required|string|max:100|unique:opac_records,reference_number,' . $record->id,
            'status' => 'required|in:draft,published,archived',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $record->update($validated);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('opac/records');
                $record->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('opac.records.show', $record)
            ->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OpacRecord $record)
    {
        // Delete all attachments
        foreach ($record->attachments as $attachment) {
            Storage::delete($attachment->file_path);
            $attachment->delete();
        }

        $record->delete();

        return redirect()->route('opac.records.index')
            ->with('success', 'Record deleted successfully.');
    }

    /**
     * Update the status of the record.
     */
    public function updateStatus(Request $request, OpacRecord $record)
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
    public function downloadAttachment(OpacRecord $record, $attachmentId)
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
    public function deleteAttachment(OpacRecord $record, $attachmentId)
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
    public function previewAttachment(OpacRecord $record, $attachmentId)
    {
        $attachment = $record->attachments()->findOrFail($attachmentId);

        if (!in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'])) {
            return redirect()->back()->with('error', 'Preview not available for this file type.');
        }

        return view('opac.records.preview-attachment', compact('record', 'attachment'));
    }
}
