<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\OpacDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpacDocumentRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = OpacDocumentRequest::with(['user', 'responses'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Document requests retrieved successfully',
            'data' => $requests
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('opac.document-requests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'document_type' => 'required|string|max:100',
            'urgency_level' => 'required|in:low,medium,high',
            'requested_date' => 'required|date',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        $documentRequest = OpacDocumentRequest::create($validated);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/document-requests');
                $documentRequest->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Document request created successfully',
            'data' => $documentRequest
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OpacDocumentRequest $documentRequest)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Document request details retrieved successfully',
            'data' => $documentRequest->load(['user', 'responses'])
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OpacDocumentRequest $documentRequest)
    {
        return view('opac.document-requests.edit', compact('documentRequest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OpacDocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'document_type' => 'sometimes|string|max:100',
            'urgency_level' => 'sometimes|in:low,medium,high',
            'requested_date' => 'sometimes|date',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $documentRequest->update($validated);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/document-requests');
                $documentRequest->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Document request updated successfully',
            'data' => $documentRequest
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OpacDocumentRequest $documentRequest)
    {
        // Delete all attachments
        foreach ($documentRequest->attachments as $attachment) {
            Storage::delete($attachment->file_path);
            $attachment->delete();
        }

        $documentRequest->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Document request deleted successfully'
        ], 200);
    }

    /**
     * Update the status of the document request.
     */
    public function updateStatus(Request $request, OpacDocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,rejected'
        ]);

        $documentRequest->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully',
            'data' => $documentRequest
        ], 200);
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(OpacDocumentRequest $documentRequest, $attachmentId)
    {
        $attachment = $documentRequest->attachments()->findOrFail($attachmentId);

        return Storage::download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(OpacDocumentRequest $documentRequest, $attachmentId)
    {
        $attachment = $documentRequest->attachments()->findOrFail($attachmentId);

        Storage::delete($attachment->file_path);
        $attachment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Attachment deleted successfully'
        ], 200);
    }
}
