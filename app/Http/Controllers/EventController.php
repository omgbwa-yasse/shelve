<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\BulletinBoard;
use App\Models\Event;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(BulletinBoard $bulletinBoard)
    {
        $events = Event::with(['bulletinBoard', 'creator', 'attachments'])
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

        return view('bulletin-boards.events.index', compact('bulletinBoard','events', 'organisations'));
    }

    public function create(BulletinBoard $BulletinBoard)
    {
        $organisations = Organisation::all();
        return view('bulletin-boards.events.create', compact('organisations', 'BulletinBoard'));
    }

    public function edit(BulletinBoard $BulletinBoard, Event $event)
    {
        $bulletinBoard = BulletinBoard::findOrFail($BulletinBoard['id']);

        $event = Event::findOrFail($event['id'])->load([
            'bulletinBoard.organisations',
            'attachments',
            'creator',
        ]);

        if ($event->bulletin_board_id !== $BulletinBoard->id) {
            return abort(404);
        }

        $organisations = Organisation::all();
        return view('bulletin-boards.events.edit', compact('organisations', 'bulletinBoard', 'event'));
    }

    public function store(BulletinBoard $BulletinBoard, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
        ]);

        $event = Event::create([
            'bulletin_board_id' => $BulletinBoard->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location' => $validated['location'],
            'status' => $request->status ?? 'published',
            'created_by' => auth()->id()
        ]);

        // Redirection vers la page d'ajout de pièces jointes si "add_attachments" est coché
        if ($request->has('add_attachments')) {
            return redirect()->route('events.attachments.create', $event->id)
                ->with('success', 'Événement créé avec succès. Vous pouvez maintenant ajouter des pièces jointes.');
        }

        return redirect()->route('bulletin-boards.events.show', [$BulletinBoard, $event])
            ->with('success', 'Événement créé avec succès.');
    }

    public function show(BulletinBoard $BulletinBoard, Event $event)
    {
        $event= Event::findOrFail($event['id'])->load([
            'bulletinBoard.organisations',
            'attachments',
            'creator',
        ]);
        return view('bulletin-boards.events.show', compact('BulletinBoard', 'event'));
    }

    public function updateStatus(BulletinBoard $BulletinBoard, Event $event, Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:draft,published,cancelled'
        ]);

        $oldStatus = $event->status;

        if ($event->bulletin_board_id !== $BulletinBoard->id) {
            return abort(404);
        }

        $event->status = $request->status;
        $event->save();

        return redirect()->route('bulletin-boards.events.show', [$BulletinBoard, $event])
            ->with('success', 'Statut de l\'événement mis à jour avec succès.');
    }

    public function update(BulletinBoard $BulletinBoard, Event $event, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
        ]);

        $event->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location' => $validated['location'],
            'status' => $request->status ?? $event->status,
            'updated_by' => auth()->id()
        ]);

        // Redirection vers la page d'ajout de pièces jointes si "add_attachments" est coché
        if ($request->has('add_attachments')) {
            return redirect()->route('events.attachments.create', $event->id)
                ->with('success', 'Événement mis à jour avec succès. Vous pouvez maintenant ajouter des pièces jointes.');
        }

        return redirect()->route('bulletin-boards.events.show', [$BulletinBoard, $event])
            ->with('success', 'Événement mis à jour avec succès.');
    }

    public function destroy(BulletinBoard $bulletinBoard, Event $event)
    {

        $event->delete();

        return redirect()->route('bulletin-boards.show', $bulletinBoard)
            ->with('success', 'Événement supprimé avec succès.');
    }
}
