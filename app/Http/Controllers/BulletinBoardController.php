<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
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
        return view('bulletin-boards.create');
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

        $bulletinBoards=BulletinBoard::paginate(50);

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
        $this->authorize('update', $bulletinBoard);

        return view('bulletin-boards.edit', compact('bulletinBoard'));
    }



    public function update(Request $request, BulletinBoard $bulletinBoard)
    {

        $this->authorize('update', $bulletinBoard);

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

        $bulletinBoard->load(['posts', 'events', 'organisations', 'attachments']);


        if ($bulletinBoard->posts->count() > 0 || $bulletinBoard->events->count() > 0) {
            return redirect()->route('bulletin-boards.show', $bulletinBoard)
                ->with('error', 'Impossible de supprimer ce tableau d\'affichage car il contient des publications ou événements.');
        }


        DB::beginTransaction();

        try {
            foreach ($bulletinBoard->attachments as $attachment) {
                if (Storage::exists($attachment->path)) {
                    Storage::delete($attachment->path);
                }
                if ($attachment->thumbnail_path && Storage::exists('public/' . $attachment->thumbnail_path)) {
                    Storage::delete('public/' . $attachment->thumbnail_path);
                }
                $bulletinBoard->attachments()->detach($attachment->id);
                if ($attachment->bulletinBoards()->count() === 0 &&
                    $attachment->posts()->count() === 0 &&
                    $attachment->events()->count() === 0) {
                    $attachment->delete();
                }
            }

            $bulletinBoard->organisations()->detach();
            $bulletinBoard->delete();
            DB::commit();

            return redirect()->route('bulletin-boards.index')
                ->with('success', 'Tableau d\'affichage supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('bulletin-boards.show', $bulletinBoard)
                ->with('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
        }
    }



}
