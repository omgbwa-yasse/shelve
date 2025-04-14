<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
Use App\Models\User;
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
        }else{
            $bulletinBoard->organisations()->attach(Auth::currentOrganisationId(), [
                'assigned_by' => Auth::id()
            ]);
        }

        $bulletinBoards = BulletinBoard::whereHas('organisations', function($query) {
            $query->where('organisations.id', Auth::currentOrganisationId());
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('bulletin-boards.index', compact('bulletinBoards'));
    }



    public function show(int $id)
    {
        $bulletinBoard = BulletinBoard::findOrFail($id)
            ->load(['creator', 'organisations', 'events', 'posts', 'users']);
        return view('bulletin-boards.show', compact('bulletinBoard'));
    }



    public function edit(BulletinBoard $bulletinBoard)
    {
        return view('bulletin-boards.edit', compact('bulletinBoard'));
    }



    public function update(Request $request, BulletinBoard $bulletinBoard)
    {


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

        return redirect()->route('bulletin-boards.show', $bulletinBoard->id)
            ->with('success', 'Bulletin board updated successfully.');
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



}
