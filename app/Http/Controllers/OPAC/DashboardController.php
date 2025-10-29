<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicEvent;
use App\Models\PublicNews;
use App\Models\PublicPage;
use App\Models\PublicRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * OPAC Dashboard Controller - Main dashboard for authenticated users
 */
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:public');
    }

    /**
     * Display the user dashboard
     */
    public function index()
    {
        $user = Auth::guard('public')->user();

        // Recent activities summary
        $recentEvents = PublicEvent::upcoming()
            ->where('is_published', true)
            ->limit(5)
            ->get();

        $recentNews = PublicNews::where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        // User's recent activities
        $myReservations = collect(); // Placeholder - would get user's reservations
        $myRequests = collect(); // Placeholder - would get user's document requests
        $myFeedback = collect(); // Placeholder - would get user's feedback

        // Quick statistics
        $stats = [
            'total_records' => PublicRecord::where('is_public', true)->count(),
            'total_events' => PublicEvent::count(),
            'total_news' => PublicNews::where('is_published', true)->count(),
            'upcoming_events' => PublicEvent::upcoming()->count(),
        ];

        // User's activity summary
        $userStats = [
            'reservations_count' => 0, // Would count user's reservations
            'requests_count' => 0, // Would count user's requests
            'feedback_count' => 0, // Would count user's feedback
        ];

        return view('opac.dashboard.index', compact(
            'user',
            'recentEvents',
            'recentNews',
            'myReservations',
            'myRequests',
            'myFeedback',
            'stats',
            'userStats'
        ));
    }

    /**
     * Display user's activity summary
     */
    public function activity()
    {
        $user = Auth::guard('public')->user();

        // Get user's complete activity history
        $activities = [
            'reservations' => collect(), // User's reservations
            'requests' => collect(), // User's document requests
            'feedback' => collect(), // User's feedback
            'searches' => collect(), // User's search history
        ];

        return view('opac.dashboard.activity', compact('activities'));
    }

    /**
     * Display quick actions page
     */
    public function quickActions()
    {
        return view('opac.dashboard.quick-actions');
    }

    /**
     * Display user preferences
     */
    public function preferences()
    {
        $user = Auth::guard('public')->user();

        return view('opac.dashboard.preferences', compact('user'));
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::guard('public')->user();

        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'newsletter_subscription' => 'boolean',
            'language' => 'nullable|string|in:en,fr',
            'timezone' => 'nullable|string',
            'theme' => 'nullable|string|in:light,dark,auto',
        ]);

        // Update user preferences
        // This would typically be stored in a user_preferences table
        // or as JSON in the user table

        return redirect()->route('opac.dashboard.preferences')
            ->with('success', __('Your preferences have been updated successfully.'));
    }
}
