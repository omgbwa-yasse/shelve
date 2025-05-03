<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicDocumentRequest;
use Illuminate\Http\Request;

class PublicDocumentRequestController extends Controller
{
    public function index()
    {
        $requests = PublicDocumentRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'record_id' => 'required|exists:public_records,id',
            'request_type' => 'required|in:digital,physical',
            'reason' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $documentRequest = PublicDocumentRequest::create($validated);

        return response()->json($documentRequest, 201);
    }

    public function show(PublicDocumentRequest $request)
    {
        if ($request->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($request->load('responses'));
    }

    public function update(Request $request, PublicDocumentRequest $documentRequest)
    {
        if ($documentRequest->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($documentRequest->status !== 'pending') {
            return response()->json(['message' => 'Cannot update non-pending request'], 400);
        }

        $validated = $request->validate([
            'request_type' => 'sometimes|in:digital,physical',
            'reason' => 'nullable|string',
        ]);

        $documentRequest->update($validated);
        return response()->json($documentRequest);
    }

    public function destroy(PublicDocumentRequest $documentRequest)
    {
        if ($documentRequest->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($documentRequest->status !== 'pending') {
            return response()->json(['message' => 'Cannot delete non-pending request'], 400);
        }

        $documentRequest->delete();
        return response()->json(null, 204);
    }

    public function cancel(PublicDocumentRequest $documentRequest)
    {
        if ($documentRequest->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($documentRequest->status !== 'pending') {
            return response()->json(['message' => 'Cannot cancel non-pending request'], 400);
        }

        $documentRequest->update(['status' => 'cancelled']);
        return response()->json($documentRequest);
    }

    public function respond(Request $request, PublicDocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'instructions' => 'required|string',
            'attachments' => 'array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ]);

        $response = $documentRequest->responses()->create([
            'responded_by' => auth()->id(),
            'instructions' => $validated['instructions'],
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/responses');
                $response->attachments()->create([
                    'attachment_id' => $path,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        $documentRequest->update(['status' => 'completed']);
        return response()->json($response->load('attachments'));
    }
}
