<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = BulletinBoard::where('type', 'event')
            ->with(['user', 'organisations'])
            ->orderBy('start_date')
            ->paginate(10);

        return view('bulletin-boards.events.index', compact('events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'organisations' => 'nullable|array',
            'organisations.*' => 'exists:organisations,id'
        ]);

        $event = BulletinBoard::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => 'event',
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location' => $validated['location'],
            'user_id' => auth()->id(),
        ]);

        if (!empty($validated['organisations'])) {
            $event->organisations()->attach($validated['organisations']);
        }

        return redirect()->route('bulletin-boards.events.show', $event)
            ->with('success', 'Événement créé avec succès.');
    }

    public function register(Event $event)
    {
        $event->participants()->attach(auth()->id());
        return back()->with('success', 'Inscription effectuée avec succès.');
    }

    public function unregister(Event $event)
    {
        $event->participants()->detach(auth()->id());
        return back()->with('success', 'Désinscription effectuée avec succès.');
    }
}
