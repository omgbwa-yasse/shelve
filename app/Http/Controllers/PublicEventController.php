<?php

namespace App\Http\Controllers;

use App\Models\PublicEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

/**
 * Controller for Public Events
 * Handles events administration for the public portal
 */
class PublicEventController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:public.events.manage');
    }

    /**
     * Display a listing of the events.
     */
    public function index(Request $request)
    {
        $query = PublicEvent::with(['registrations'])->withCount('registrations');

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'past':
                    $query->past();
                    break;
                case 'ongoing':
                    $query->where('start_date', '<=', now())
                          ->where('end_date', '>=', now());
                    break;
                default:
                    break;
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            if ($request->type === 'online') {
                $query->where('is_online', true);
            } elseif ($request->type === 'physical') {
                $query->where('is_online', false);
            }
        }

        // Order by start_date
        $query->orderBy('start_date', 'desc');

        $events = $query->paginate(15)->appends($request->query());

        // Statistics
        $totalEvents = PublicEvent::count();
        $upcomingEvents = PublicEvent::upcoming()->count();
        $pastEvents = PublicEvent::past()->count();
        $totalRegistrations = DB::table('public_event_registrations')->count();

        return view('public.events.index', compact(
            'events',
            'totalEvents',
            'upcomingEvents',
            'pastEvents',
            'totalRegistrations'
        ));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        return view('public.events.create');
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'is_online' => 'boolean',
            'online_link' => 'nullable|url|required_if:is_online,1',
            'max_participants' => 'nullable|integer|min:1',
            'registration_required' => 'boolean',
            'registration_deadline' => 'nullable|date|before:start_date',
            'image' => 'nullable|image|max:2048', // 2MB max
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('public/events', 'public');
        }

        $event = PublicEvent::create($validated);

        if ($request->boolean('save_and_continue')) {
            return redirect()
                ->route('public.events.edit', $event)
                ->with('success', __('Event created successfully.'));
        }

        return redirect()
            ->route('public.events.show', $event)
            ->with('success', __('Event created successfully.'));
    }

    /**
     * Display the specified event.
     */
    public function show(PublicEvent $event)
    {
        $event->load(['registrations.user']);

        // Get registration statistics
        $registrationStats = [
            'total' => $event->registrations->count(),
            'confirmed' => $event->registrations->where('status', 'confirmed')->count(),
            'pending' => $event->registrations->where('status', 'pending')->count(),
            'cancelled' => $event->registrations->where('status', 'cancelled')->count(),
        ];

        return view('public.events.show', compact('event', 'registrationStats'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(PublicEvent $event)
    {
        return view('public.events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, PublicEvent $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'is_online' => 'boolean',
            'online_link' => 'nullable|url|required_if:is_online,1',
            'max_participants' => 'nullable|integer|min:1',
            'registration_required' => 'boolean',
            'registration_deadline' => 'nullable|date|before:start_date',
            'image' => 'nullable|image|max:2048',
            'remove_image' => 'boolean',
        ]);

        // Handle image removal
        if ($request->boolean('remove_image') && $event->image_path) {
            Storage::disk('public')->delete($event->image_path);
            $validated['image_path'] = null;
        }

        // Handle new image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($event->image_path) {
                Storage::disk('public')->delete($event->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('public/events', 'public');
        }

        $event->update($validated);

        if ($request->boolean('save_and_continue')) {
            return redirect()
                ->route('public.events.edit', $event)
                ->with('success', __('Event updated successfully.'));
        }

        return redirect()
            ->route('public.events.show', $event)
            ->with('success', __('Event updated successfully.'));
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(PublicEvent $event)
    {
        // Delete image if exists
        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }

        $event->delete();

        return redirect()
            ->route('public.events.index')
            ->with('success', __('Event deleted successfully.'));
    }

    /**
     * Bulk actions for events.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:public_events,id',
            'action' => 'required|in:delete',
        ]);

        $events = PublicEvent::whereIn('id', $request->event_ids)->get();

        if ($request->action === 'delete') {
            foreach ($events as $event) {
                if ($event->image_path) {
                    Storage::disk('public')->delete($event->image_path);
                }
                $event->delete();
            }
            $message = __(':count events deleted successfully.', ['count' => $events->count()]);
        }

        return redirect()
            ->route('public.events.index')
            ->with('success', $message);
    }

    /**
     * Export event registrations.
     */
    public function exportRegistrations(PublicEvent $event)
    {
        $registrations = $event->registrations()->with('user')->get();

        $filename = 'event_' . $event->id . '_registrations_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                __('Name'),
                __('Email'),
                __('Status'),
                __('Registration Date'),
                __('Notes')
            ]);

            // CSV data
            foreach ($registrations as $registration) {
                fputcsv($file, [
                    $registration->user->name ?? $registration->name,
                    $registration->user->email ?? $registration->email,
                    $registration->status,
                    $registration->created_at->format('Y-m-d H:i:s'),
                    $registration->notes ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Manage event registrations.
     */
    public function registrations(PublicEvent $event)
    {
        $registrations = $event->registrations()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('public.events.registrations', compact('event', 'registrations'));
    }

    /**
     * Update registration status.
     */
    public function updateRegistrationStatus(Request $request, PublicEvent $event, $registrationId)
    {
        $request->validate([
            'status' => 'required|in:confirmed,pending,cancelled'
        ]);

        $registration = $event->registrations()->findOrFail($registrationId);
        $registration->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }
}
