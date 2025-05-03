<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicFeedback;
use Illuminate\Http\Request;

class PublicFeedbackController extends Controller
{
    public function index()
    {
        $feedback = PublicFeedback::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($feedback);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'related_id' => 'nullable|integer',
            'related_type' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $validated['user_id'] = auth()->id();
        $feedback = PublicFeedback::create($validated);

        return response()->json($feedback, 201);
    }

    public function show(PublicFeedback $feedback)
    {
        if ($feedback->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($feedback);
    }

    public function update(Request $request, PublicFeedback $feedback)
    {
        if ($feedback->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($feedback->status !== 'pending') {
            return response()->json(['message' => 'Cannot update non-pending feedback'], 400);
        }

        $validated = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $feedback->update($validated);
        return response()->json($feedback);
    }

    public function destroy(PublicFeedback $feedback)
    {
        if ($feedback->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($feedback->status !== 'pending') {
            return response()->json(['message' => 'Cannot delete non-pending feedback'], 400);
        }

        $feedback->delete();
        return response()->json(null, 204);
    }

    public function respond(Request $request, PublicFeedback $feedback)
    {
        if ($feedback->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'response' => 'required|string',
        ]);

        $feedback->update([
            'response' => $validated['response'],
            'responded_at' => now(),
            'responded_by' => auth()->id(),
            'status' => 'responded'
        ]);

        return response()->json($feedback);
    }
}
