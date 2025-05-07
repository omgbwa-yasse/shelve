<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\PublicFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpacFeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feedback = PublicFeedback::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Feedback retrieved successfully',
            'data' => $feedback
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('opac.feedbacks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:suggestion,bug,other',
            'user_id' => 'required|exists:users,id',
        ]);

        $feedback = PublicFeedback::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Feedback submitted successfully',
            'data' => $feedback
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicFeedback $feedback)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Feedback details retrieved successfully',
            'data' => $feedback->load('user')
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicFeedback $feedback)
    {
        return view('opac.feedbacks.edit', compact('feedback'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicFeedback $feedback)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved',
            'admin_response' => 'nullable|string',
        ]);

        $feedback->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Feedback updated successfully',
            'data' => $feedback
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicFeedback $feedback)
    {
        $feedback->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Feedback deleted successfully'
        ], 200);
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
    public function addComment(Request $request, OpacFeedback $feedback)
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
    public function deleteComment(OpacFeedback $feedback, $commentId)
    {
        $comment = $feedback->comments()->findOrFail($commentId);
        $comment->delete();

        return redirect()->back()
            ->with('success', 'Comment deleted successfully.');
    }
}
