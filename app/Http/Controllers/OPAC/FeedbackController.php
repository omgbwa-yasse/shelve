<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * OPAC Feedback Controller - Public feedback and ratings system
 */
class FeedbackController extends Controller
{
    /**
     * Display feedback submission form
     */
    public function create()
    {
        return view('opac.feedback.create');
    }

    /**
     * Store feedback from public users
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:suggestion,complaint,compliment,question,bug_report',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'rating' => 'nullable|integer|min:1|max:5',
            'email' => 'nullable|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        // Add user info if authenticated
        if (Auth::guard('public')->check()) {
            $user = Auth::guard('public')->user();
            $validated['public_user_id'] = $user->id;
            $validated['email'] = $validated['email'] ?? $user->email;
            $validated['name'] = $validated['name'] ?? $user->name;
        }

        $validated['status'] = 'pending';
        $validated['ip_address'] = $request->ip();

        PublicFeedback::create($validated);

        return redirect()->route('opac.feedback.success')
            ->with('success', __('Your feedback has been submitted successfully. We appreciate your input!'));
    }

    /**
     * Success page after feedback submission
     */
    public function success()
    {
        return view('opac.feedback.success');
    }

    /**
     * Display user's own feedback (for authenticated users)
     */
    public function myFeedback()
    {
        $user = Auth::guard('public')->user();

        if (!$user) {
            return redirect()->route('opac.login');
        }

        $feedback = PublicFeedback::where('public_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('opac.feedback.my-feedback', compact('feedback'));
    }

    /**
     * Show specific feedback (only for the owner)
     */
    public function show($id)
    {
        $user = Auth::guard('public')->user();

        if (!$user) {
            return redirect()->route('opac.login');
        }

        $feedback = PublicFeedback::where('id', $id)
            ->where('public_user_id', $user->id)
            ->with('comments')
            ->firstOrFail();

        return view('opac.feedback.show', compact('feedback'));
    }
}
