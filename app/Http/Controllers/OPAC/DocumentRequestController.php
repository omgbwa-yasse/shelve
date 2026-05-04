<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:public');
    }

    public function index()
    {
        $user = Auth::guard('public')->user();

        $requests = PublicDocumentRequest::where('user_id', $user->id)
            ->with('record')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('opac.document-requests.index', compact('requests'));
    }

    public function create()
    {
        return view('opac.document-requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required|in:consultation,copy,loan',
            'reason'       => 'required|string|max:2000',
            'record_id'    => 'nullable|exists:public_records,id',
        ]);

        $user = Auth::guard('public')->user();
        $validated['user_id'] = $user->id;
        $validated['status']  = 'pending';

        $documentRequest = PublicDocumentRequest::create($validated);

        return redirect()->route('opac.document-requests.show', $documentRequest)
            ->with('success', __('Your document request has been submitted successfully.'));
    }

    public function show(PublicDocumentRequest $documentRequest)
    {
        $user = Auth::guard('public')->user();

        if ($documentRequest->user_id !== $user->id) {
            abort(403);
        }

        $documentRequest->load('responses', 'record');

        return view('opac.document-requests.show', compact('documentRequest'));
    }

    public function edit(PublicDocumentRequest $documentRequest)
    {
        $user = Auth::guard('public')->user();

        if ($documentRequest->user_id !== $user->id) {
            abort(403);
        }

        if ($documentRequest->status !== 'pending') {
            return redirect()->route('opac.document-requests.show', $documentRequest)
                ->with('error', __('This request can no longer be modified.'));
        }

        return view('opac.document-requests.edit', compact('documentRequest'));
    }

    public function update(Request $request, PublicDocumentRequest $documentRequest)
    {
        $user = Auth::guard('public')->user();

        if ($documentRequest->user_id !== $user->id) {
            abort(403);
        }

        if ($documentRequest->status !== 'pending') {
            return redirect()->route('opac.document-requests.show', $documentRequest)
                ->with('error', __('This request can no longer be modified.'));
        }

        $validated = $request->validate([
            'request_type' => 'required|in:consultation,copy,loan',
            'reason'       => 'required|string|max:2000',
        ]);

        $documentRequest->update($validated);

        return redirect()->route('opac.document-requests.show', $documentRequest)
            ->with('success', __('Your document request has been updated successfully.'));
    }

    public function cancel(PublicDocumentRequest $documentRequest)
    {
        $user = Auth::guard('public')->user();

        if ($documentRequest->user_id !== $user->id) {
            abort(403);
        }

        if ($documentRequest->status !== 'pending') {
            return redirect()->route('opac.document-requests.show', $documentRequest)
                ->with('error', __('This request can no longer be cancelled.'));
        }

        $documentRequest->update(['status' => 'cancelled']);

        return redirect()->route('opac.document-requests.index')
            ->with('success', __('Your document request has been cancelled.'));
    }
}
