<?php

namespace App\Http\Controllers;

use App\Models\PublicFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicFeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feedback = PublicFeedback::with(['user', 'comments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('public.feedback.index', compact('feedback'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('public.feedback.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:bug,feature,improvement,other',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:new,in_progress,resolved,closed',
        ]);

        $validated['user_id'] = Auth::id();
        $feedback = PublicFeedback::create($validated);

        return redirect()->route('public.feedback.show', $feedback)
            ->with('success', 'Feedback submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicFeedback $feedback)
    {
        return view('public.feedback.show', compact('feedback'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicFeedback $feedback)
    {
        return view('public.feedback.edit', compact('feedback'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicFeedback $feedback)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:bug,feature,improvement,other',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:new,in_progress,resolved,closed',
        ]);

        $feedback->update($validated);

        return redirect()->route('public.feedback.show', $feedback)
            ->with('success', 'Feedback updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicFeedback $feedback)
    {
        $feedback->delete();

        return redirect()->route('public.feedback.index')
            ->with('success', 'Feedback deleted successfully.');
    }

    /**
     * Update the status of the feedback.
     */
    public function updateStatus(Request $request, PublicFeedback $feedback)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,in_progress,resolved,closed'
        ]);

        $feedback->update($validated);

        return redirect()->back()
            ->with('success', 'Status updated successfully.');
    }

    /**
     * Add a comment to the feedback.
     */
    public function addComment(Request $request, PublicFeedback $feedback)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $validated['user_id'] = Auth::id();
        $feedback->comments()->create($validated);

        return redirect()->back()
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Delete a comment from the feedback.
     */
    public function deleteComment(PublicFeedback $feedback, $commentId)
    {
        $comment = $feedback->comments()->findOrFail($commentId);
        $comment->delete();

        return redirect()->back()
            ->with('success', 'Comment deleted successfully.');
    }

    // ========================================
    // API METHODS pour l'interface React
    // ========================================

    /**
     * API: Store new feedback
     */
    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:bug,feature,improvement,other',
            'priority' => 'required|in:low,medium,high',
            'contact_email' => 'required|email',
            'contact_name' => 'required|string|max:255',
        ]);

        $validated['status'] = 'new';

        // Si l'utilisateur est authentifié, associer le feedback
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        $feedback = PublicFeedback::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully',
            'data' => $feedback
        ], 201);
    }

    /**
     * API: Get user's feedback
     */
    public function apiIndex(Request $request)
    {
        $user = $request->user();

        $feedback = PublicFeedback::with(['comments'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $feedback->items(),
            'pagination' => [
                'current_page' => $feedback->currentPage(),
                'last_page' => $feedback->lastPage(),
                'per_page' => $feedback->perPage(),
                'total' => $feedback->total(),
            ]
        ]);
    }
}
