<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Display a listing of published events
     */
    public function index(Request $request)
    {
        // Note: public_events table doesn't have 'is_published' column
        $query = PublicEvent::query();

        // Filter by time period
        $filter = $request->get('filter', 'upcoming');

        switch ($filter) {
            case 'upcoming':
                $query->where('start_date', '>=', now()->startOfDay());
                break;
            case 'past':
                $query->where('end_date', '<', now()->startOfDay());
                break;
            case 'today':
                $query->whereDate('start_date', '<=', now())
                      ->whereDate('end_date', '>=', now());
                break;
            case 'this_week':
                $query->whereBetween('start_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;
            case 'this_month':
                $query->whereBetween('start_date', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ]);
                break;
            default:
                $query->where('start_date', '>=', now()->startOfDay());
                break;
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        $events = $query->orderBy('start_date', 'asc')
                       ->paginate(10)
                       ->appends($request->all());

        // No categories available in this table structure
        $categories = collect();

        return view('opac.events.index', compact('events', 'categories', 'filter'));
    }

    /**
     * Display a specific event
     */
    public function show($id)
    {
        $event = PublicEvent::findOrFail($id);

        // Get related events (excluding current)
        $relatedEvents = PublicEvent::where('id', '!=', $event->id)
                                  ->where('start_date', '>=', now())
                                  ->orderBy('start_date', 'asc')
                                  ->limit(3)
                                  ->get();

        return view('opac.events.show', compact('event', 'relatedEvents'));
    }
}
