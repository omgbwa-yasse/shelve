<?php

namespace App\Http\Controllers;

use App\Models\PublicResponse;
use App\Models\PublicDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $responses = PublicResponse::with(['documentRequest', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('public.responses.index', compact('responses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(PublicDocumentRequest $documentRequest)
    {
        return view('public.responses.create', compact('documentRequest'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PublicDocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'status' => 'required|in:draft,sent',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        $validated['user_id'] = auth()->id();
        $validated['document_request_id'] = $documentRequest->id;

        $response = PublicResponse::create($validated);

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

        return redirect()->route('public.document-requests.show', $documentRequest)
            ->with('success', 'Response submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicResponse $response)
    {
        return view('public.responses.show', compact('response'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicResponse $response)
    {
        return view('public.responses.edit', compact('response'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicResponse $response)
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

        return redirect()->route('public.document-requests.show', $response->documentRequest)
            ->with('success', 'Response updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicResponse $response)
    {
        // Delete all attachments
        foreach ($response->attachments as $attachment) {
            Storage::delete($attachment->file_path);
            $attachment->delete();
        }

        $documentRequest = $response->documentRequest;
        $response->delete();

        return redirect()->route('public.document-requests.show', $documentRequest)
            ->with('success', 'Response deleted successfully.');
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(PublicResponse $response, $attachmentId)
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
    public function deleteAttachment(PublicResponse $response, $attachmentId)
    {
        $attachment = $response->attachments()->findOrFail($attachmentId);

        Storage::delete($attachment->file_path);
        $attachment->delete();

        return redirect()->back()
            ->with('success', 'Attachment deleted successfully.');
    }
}
