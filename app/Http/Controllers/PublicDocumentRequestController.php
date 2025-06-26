<?php

namespace App\Http\Controllers;

use App\Models\PublicDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicDocumentRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documentRequests = PublicDocumentRequest::with(['user', 'responses'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('public.document-requests.index', compact('documentRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('public.document-requests.create');
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

        $documentRequest = PublicDocumentRequest::create($validated);

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

        return redirect()->route('public.document-requests.show', $documentRequest)
            ->with('success', 'Document request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicDocumentRequest $documentRequest)
    {
        return view('public.document-requests.show', compact('documentRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicDocumentRequest $documentRequest)
    {
        return view('public.document-requests.edit', compact('documentRequest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicDocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'document_type' => 'required|string|max:100',
            'urgency_level' => 'required|in:low,medium,high',
            'requested_date' => 'required|date',
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

        return redirect()->route('public.document-requests.show', $documentRequest)
            ->with('success', 'Document request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicDocumentRequest $documentRequest)
    {
        // Delete all attachments
        foreach ($documentRequest->attachments as $attachment) {
            Storage::delete($attachment->file_path);
            $attachment->delete();
        }

        $documentRequest->delete();

        return redirect()->route('public.document-requests.index')
            ->with('success', 'Document request deleted successfully.');
    }

    /**
     * Update the status of the document request.
     */
    public function updateStatus(Request $request, PublicDocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,rejected'
        ]);

        $documentRequest->update($validated);

        return redirect()->back()
            ->with('success', 'Status updated successfully.');
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(PublicDocumentRequest $documentRequest, $attachmentId)
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
    public function deleteAttachment(PublicDocumentRequest $documentRequest, $attachmentId)
    {
        $attachment = $documentRequest->attachments()->findOrFail($attachmentId);

        Storage::delete($attachment->file_path);
        $attachment->delete();

        return redirect()->back()
            ->with('success', 'Attachment deleted successfully.');
    }

    // ========================================
    // API METHODS pour l'interface React
    // ========================================

    /**
     * API: Store new document request
     */
    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'document_type' => 'required|string|max:100',
            'urgency_level' => 'required|in:low,medium,high',
            'requested_date' => 'required|date',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $validated['status'] = 'pending';

        // Si l'utilisateur est authentifié, associer la demande
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        $documentRequest = PublicDocumentRequest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Document request submitted successfully',
            'data' => $documentRequest
        ], 201);
    }

    /**
     * API: Get user's document requests
     */
    public function apiIndex(Request $request)
    {
        $user = $request->user();

        $requests = PublicDocumentRequest::with(['responses'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $requests->items(),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ]
        ]);
    }

    /**
     * API: Get single document request
     */
    public function apiShow(Request $request, PublicDocumentRequest $documentRequest)
    {
        $user = $request->user();

        // Vérifier que l'utilisateur peut voir cette demande
        if ($documentRequest->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $documentRequest->load(['responses']);

        return response()->json([
            'success' => true,
            'data' => $documentRequest
        ]);
    }
}
