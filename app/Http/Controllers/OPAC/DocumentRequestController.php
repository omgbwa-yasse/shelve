<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * OPAC Document Request Controller - Public document request system
 */
class DocumentRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:public');
    }

    /**
     * Display user's document requests
     */
    public function index()
    {
        $user = Auth::guard('public')->user();

        $requests = PublicDocumentRequest::where('public_user_id', $user->id)
            ->with('responses')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('opac.document-requests.index', compact('requests'));
    }

    /**
     * Show form for creating a new document request
     */
    public function create()
    {
        return view('opac.document-requests.create');
    }

    /**
     * Store a new document request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'document_type' => 'required|in:book,article,thesis,report,map,photo,video,audio,other',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'isbn_issn' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:50',
            'urgency' => 'required|in:low,normal,high,urgent',
            'usage_purpose' => 'required|in:research,education,personal,professional',
            'preferred_format' => 'required|in:physical,digital,both',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::guard('public')->user();
        $validated['public_user_id'] = $user->id;
        $validated['status'] = 'pending';
        $validated['request_number'] = 'REQ-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $documentRequest = PublicDocumentRequest::create($validated);

        return redirect()->route('opac.document-requests.show', $documentRequest)
            ->with('success', __('Your document request has been submitted successfully.'));
    }

    /**
     * Display the specified document request
     */
    public function show(PublicDocumentRequest $documentRequest)
    {
        $user = Auth::guard('public')->user();

        // Check if the request belongs to the current user
        if ($documentRequest->public_user_id !== $user->id) {
            abort(403);
        }

        $documentRequest->load('responses.attachments');

        return view('opac.document-requests.show', compact('documentRequest'));
    }

    /**
     * Show form for editing a document request (only if not processed)
     */
    public function edit(PublicDocumentRequest $documentRequest)
    {
        $user = Auth::guard('public')->user();

        // Check ownership and status
        if ($documentRequest->public_user_id !== $user->id) {
            abort(403);
        }

        if (!in_array($documentRequest->status, ['pending', 'under_review'])) {
            return redirect()->route('opac.document-requests.show', $documentRequest)
                ->with('error', __('This request can no longer be modified.'));
        }

        return view('opac.document-requests.edit', compact('documentRequest'));
    }

    /**
     * Update the document request
     */
    public function update(Request $request, PublicDocumentRequest $documentRequest)
    {
        $user = Auth::guard('public')->user();

        // Check ownership and status
        if ($documentRequest->public_user_id !== $user->id) {
            abort(403);
        }

        if (!in_array($documentRequest->status, ['pending', 'under_review'])) {
            return redirect()->route('opac.document-requests.show', $documentRequest)
                ->with('error', __('This request can no longer be modified.'));
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'document_type' => 'required|in:book,article,thesis,report,map,photo,video,audio,other',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'isbn_issn' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:50',
            'urgency' => 'required|in:low,normal,high,urgent',
            'usage_purpose' => 'required|in:research,education,personal,professional',
            'preferred_format' => 'required|in:physical,digital,both',
            'notes' => 'nullable|string|max:1000',
        ]);

        $documentRequest->update($validated);

        return redirect()->route('opac.document-requests.show', $documentRequest)
            ->with('success', __('Your document request has been updated successfully.'));
    }

    /**
     * Cancel a document request
     */
    public function cancel(PublicDocumentRequest $documentRequest)
    {
        $user = Auth::guard('public')->user();

        // Check ownership and status
        if ($documentRequest->public_user_id !== $user->id) {
            abort(403);
        }

        if (!in_array($documentRequest->status, ['pending', 'under_review'])) {
            return redirect()->route('opac.document-requests.show', $documentRequest)
                ->with('error', __('This request can no longer be cancelled.'));
        }

        $documentRequest->update(['status' => 'cancelled']);

        return redirect()->route('opac.document-requests.index')
            ->with('success', __('Your document request has been cancelled.'));
    }
}
