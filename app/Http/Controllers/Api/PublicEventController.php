<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicEvent;
use Illuminate\Http\Request;

class PublicEventController extends Controller
{
    public function index()
    {
        $events = PublicEvent::orderBy('start_date')->paginate(10);
        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'is_online' => 'boolean',
            'online_link' => 'nullable|url|required_if:is_online,true',
        ]);

        $event = PublicEvent::create($validated);
        return response()->json($event, 201);
    }

    public function show(PublicEvent $event)
    {
        return response()->json($event->load('registrations'));
    }

    public function update(Request $request, PublicEvent $event)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'is_online' => 'boolean',
            'online_link' => 'nullable|url|required_if:is_online,true',
        ]);

        $event->update($validated);
        return response()->json($event);
    }

    public function destroy(PublicEvent $event)
    {
        $event->delete();
        return response()->json(null, 204);
    }

    public function upcoming()
    {
        $events = PublicEvent::where('start_date', '>', now())
            ->orderBy('start_date')
            ->paginate(10);
        return response()->json($events);
    }

    public function past()
    {
        $events = PublicEvent::where('end_date', '<', now())
            ->orderBy('start_date', 'desc')
            ->paginate(10);
        return response()->json($events);
    }

    public function search(Request $request)
    {
        $query = PublicEvent::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        if ($request->has('is_online')) {
            $query->where('is_online', $request->is_online);
        }

        $events = $query->orderBy('start_date')->paginate(10);
        return response()->json($events);
    }
}
