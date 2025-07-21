<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Organisation;
use App\Models\Notification;
use App\Enums\NotificationModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulletinBoardController extends Controller
{
    public function index()
    {
        $bulletinBoards = BulletinBoard::with(['creator', 'organisations'])
            ->whereHas('organisations', function($query) {
                $query->where('organisations.id', Auth::currentOrganisationId());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bulletin-boards.index', compact('bulletinBoards'));
    }

    public function create()
    {
        $organisations = Organisation::all();
        return view('bulletin-boards.create', compact('organisations'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'organisations' => 'nullable|array',
            'organisations.*' => 'exists:organisations,id',
        ]);

        $bulletinBoard = new BulletinBoard();
        $bulletinBoard->name = $request->name;
        $bulletinBoard->description = $request->description;
        $bulletinBoard->created_by = Auth::id();
        $bulletinBoard->save();

        if ($request->has('organisations')) {
            foreach ($request->organisations as $organisationId) {
                $bulletinBoard->organisations()->attach($organisationId, [
                    'assigned_by' => Auth::id()
                ]);
            }
        } else {
            $bulletinBoard->organisations()->attach(Auth::currentOrganisationId(), [
                'assigned_by' => Auth::id()
            ]);
        }

        return redirect()->route('bulletin-boards.show', $bulletinBoard)
            ->with('success', 'Tableau d\'affichage créé avec succès.');
    }




    public function show(BulletinBoard $bulletinBoard)
    {
        $bulletinBoard->load(['creator', 'organisations', 'events', 'posts', 'users']);
        return view('bulletin-boards.show', compact('bulletinBoard'));
    }




    public function edit(BulletinBoard $bulletinBoard)
    {
        $organisations = Organisation::all();
        return view('bulletin-boards.edit', compact('bulletinBoard', 'organisations'));
    }



    public function update(Request $request, BulletinBoard $bulletinBoard)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'organisations' => 'nullable|array',
            'organisations.*' => 'exists:organisations,id',
        ]);

        $bulletinBoard->name = $request->name;
        $bulletinBoard->description = $request->description;
        $bulletinBoard->save();

        if ($request->has('organisations')) {
            $existingIds = $bulletinBoard->organisations->pluck('id')->toArray();
            $newIds = $request->organisations;
            $toAdd = array_diff($newIds, $existingIds);
            $toRemove = array_diff($existingIds, $newIds);

            $bulletinBoard->organisations()->detach($toRemove);

            foreach ($toAdd as $organisationId) {
                $bulletinBoard->organisations()->attach($organisationId, [
                    'assigned_by' => Auth::id()
                ]);
            }
        }

        return redirect()->route('bulletin-boards.show', $bulletinBoard)
            ->with('success', 'Tableau d\'affichage mis à jour avec succès.');
    }



    public function destroy(BulletinBoard $bulletinBoard)
    {
        $bulletinBoard->load(['posts', 'events', 'organisations']);

        if ($bulletinBoard->posts->count() > 0 || $bulletinBoard->events->count() > 0) {
            return redirect()->route('bulletin-boards.show', $bulletinBoard)
                ->with('error', 'Impossible de supprimer ce tableau d\'affichage car il contient des publications ou événements.');
        }

        $bulletinBoard->organisations()->detach();
        $bulletinBoard->delete();

        return redirect()->route('bulletin-boards.index')
                ->with('success', 'Tableau d\'affichage supprimé avec succès.');
    }


    public function dashboard()
    {
        $bulletinBoards = BulletinBoard::with(['events', 'posts'])
            ->whereHas('organisations', function($query) {
                $query->where('organisations.id', Auth::currentOrganisationId());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('bulletin-boards.dashboard', compact('bulletinBoards'));
    }

    public function myPosts()
    {
        $posts = Post::with('bulletinBoard')
            ->where('created_by', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bulletin-boards.my-posts', compact('posts'));
    }


    public function archives()
    {
        $bulletinBoards = BulletinBoard::onlyTrashed()
            ->whereHas('organisations', function($query) {
                $query->where('organisations.id', Auth::currentOrganisationId());
            })
            ->paginate(10);

        return view('bulletin-boards.archives', compact('bulletinBoards'));
    }


    public function toggleArchive(BulletinBoard $bulletinBoard)
    {
        if ($bulletinBoard->trashed()) {
            $bulletinBoard->restore();
            $message = 'Tableau d\'affichage restauré avec succès.';
        } else {
            $bulletinBoard->delete();
            $message = 'Tableau d\'affichage archivé avec succès.';
        }

        return back()->with('success', $message);
    }

    public function attachOrganisation(Request $request, BulletinBoard $bulletinBoard)
    {
        $request->validate([
            'organisation_id' => 'required|exists:organisations,id'
        ]);

        $bulletinBoard->organisations()->attach($request->organisation_id, [
            'assigned_by' => Auth::id()
        ]);

        return back()->with('success', 'Organisation ajoutée avec succès.');
    }

    public function detachOrganisation(BulletinBoard $bulletinBoard, Organisation $organisation)
    {
        $bulletinBoard->organisations()->detach($organisation->id);
        return back()->with('success', 'Organisation retirée avec succès.');
    }




}
