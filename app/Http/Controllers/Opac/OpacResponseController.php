<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\OpacResponse;
use App\Models\OpacDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class OpacResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $responses = OpacResponse::with(['documentRequest', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Responses retrieved successfully',
            'data' => $responses
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(OpacDocumentRequest $documentRequest)
    {
        return view('opac.responses.create', compact('documentRequest'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, OpacDocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'status' => 'required|in:draft,sent',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        $validated['user_id'] = auth()->id();
        $validated['document_request_id'] = $documentRequest->id;

        $response = OpacResponse::create($validated);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/responses');
                $response->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Update document request status if response is sent
        if ($validated['status'] === 'sent') {
            $documentRequest->update(['status' => 'processing']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Response created successfully',
            'data' => $response
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OpacResponse $response)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Response details retrieved successfully',
            'data' => $response->load(['user', 'attachments'])
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OpacResponse $response)
    {
        return view('opac.responses.edit', compact('response'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OpacResponse $response)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'status' => 'required|in:draft,sent',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $response->update($validated);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/responses');
                $response->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Update document request status if response is sent
        if ($validated['status'] === 'sent') {
            $response->documentRequest->update(['status' => 'processing']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Response updated successfully',
            'data' => $response
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OpacResponse $response)
    {
        // Delete all attachments
        foreach ($response->attachments as $attachment) {
            Storage::delete($attachment->file_path);
            $attachment->delete();
        }

        $documentRequest = $response->documentRequest;
        $response->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Response deleted successfully'
        ], 200);
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(OpacResponse $response, $attachmentId)
    {
        $attachment = $response->attachments()->findOrFail($attachmentId);

        return Storage::download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(OpacResponse $response, $attachmentId)
    {
        $attachment = $response->attachments()->findOrFail($attachmentId);

        Storage::delete($attachment->file_path);
        $attachment->delete();

        return redirect()->back()
            ->with('success', 'Attachment deleted successfully.');
    }
}
