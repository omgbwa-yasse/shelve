<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicDocumentRequest;
use App\Models\PublicEvent;
use App\Models\PublicFeedback;
use App\Models\PublicNews;
use App\Models\PublicRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:public');
    }

    public function index()
    {
        $user = Auth::guard('public')->user();

        $recentEvents = PublicEvent::where('start_date', '>=', now())
            ->where('is_published', true)
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        $recentNews = PublicNews::where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        $myRequests = PublicDocumentRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $myFeedback = PublicFeedback::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stats = [
            'total_records'   => PublicRecord::available()->count(),
            'total_events'    => PublicEvent::where('is_published', true)->count(),
            'total_news'      => PublicNews::where('is_published', true)->count(),
            'upcoming_events' => PublicEvent::where('start_date', '>=', now())->where('is_published', true)->count(),
        ];

        $userStats = [
            'requests_count' => PublicDocumentRequest::where('user_id', $user->id)->count(),
            'feedback_count' => PublicFeedback::where('user_id', $user->id)->count(),
        ];

        return view('opac.dashboard.index', compact(
            'user', 'recentEvents', 'recentNews',
            'myRequests', 'myFeedback', 'stats', 'userStats'
        ));
    }

    public function activity()
    {
        $user = Auth::guard('public')->user();

        $activities = [
            'requests' => PublicDocumentRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')->get(),
            'feedback' => PublicFeedback::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')->get(),
        ];

        return view('opac.dashboard.activity', compact('user', 'activities'));
    }

    public function quickActions()
    {
        return view('opac.dashboard.quick-actions');
    }

    public function preferences()
    {
        $user = auth('public')->user();
        return view('opac.dashboard.preferences', compact('user'));
    }

    public function updatePreferences(Request $request)
    {
        $user = auth('public')->user();

        $validated = $request->validate([
            'email_notifications'  => 'boolean',
            'newsletter_subscription' => 'boolean',
            'language'             => 'in:fr,en',
            'items_per_page'       => 'in:10,20,50,100',
            'save_search_history'  => 'boolean',
            'default_sort'         => 'in:relevance,title,date_desc,date_asc',
        ]);

        $preferences = array_merge($user->preferences ?? [], $validated);
        $user->update(['preferences' => $preferences]);

        return redirect()->route('opac.dashboard.preferences')
            ->with('success', __('Preferences updated successfully.'));
    }
}
