<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\BulletinBoard;
use App\Models\Event;
use App\Models\Organisation;
use Illuminate\Support\Facades\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['bulletinBoard', 'user'])
            ->when(request('period') === 'upcoming', function ($query) {
                return $query->where('start_date', '>=', now());
            })
            ->when(request('organisation'), function ($query, $organisationId) {
                return $query->whereHas('bulletinBoard.organisations', function ($q) use ($organisationId) {
                    $q->where('organisations.id', $organisationId);
                });
            })
            ->orderBy('start_date')
            ->paginate(10);

        $organisations = Organisation::all();

        return view('bulletin-boards.events.index', compact('events', 'organisations'));
    }

    public function create()
    {
        $organisations = Organisation::all();
        return view('bulletin-boards.events.create', compact('organisations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255'
        ]);

        $bulletinBoard = BulletinBoard::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'user_id' => auth()->id()
        ]);

        $event = Event::create([
            'bulletin_board_id' => $bulletinBoard->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location' => $validated['location'],
            'status' => $request->status ?? 'draft',
            'user_id' => auth()->id()
        ]);

        if ($request->has('organisations')) {
            $bulletinBoard->organisations()->attach($request->organisations, [
                'user_id' => auth()->id()
            ]);
        }

        return redirect()->route('bulletin-boards.events.show', $event)
            ->with('success', 'Événement créé avec succès.');
    }
}
