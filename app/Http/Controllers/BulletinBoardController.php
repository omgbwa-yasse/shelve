<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class BulletinBoardController extends Controller
{


    public function index()
    {
        $bulletinBoards = BulletinBoard::with(['creator', 'organisations'])
            ->whereHas('organisations', function($query) {
                $query->where('organisations.id', auth()->user()->current_organisation_id);
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

        $bulletinBoard->users()->attach(Auth::id(), [
            'role' => 'super_admin',
            'permissions' => 'write',
            'assigned_by' => Auth::id()
        ]);

        if ($request->has('organisations')) {
            foreach ($request->organisations as $organisationId) {
                $bulletinBoard->organisations()->attach($organisationId, [
                    'assigned_by' => Auth::id()
                ]);
            }
        }

        return redirect()->route('bulletin-boards.show', $bulletinBoard->id)
            ->with('success', 'Bulletin board created successfully.');
    }


    public function show(int $id)
    {
        $bulletinBoard = BulletinBoard::findOrFail($id)
            ->load(['creator', 'organisations', 'events', 'posts', 'users']);
        $userRole = $bulletinBoard->users()
            ->where('user_id', Auth::id())
            ->first()?->pivot?->role;
        return view('bulletin-boards.show', compact('bulletinBoard', 'userRole'));
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

        $this->authorize('delete', $bulletinBoard);

        $bulletinBoard->delete();

        return redirect()->route('bulletin-boards.index')
            ->with('success', 'Bulletin board deleted successfully.');
    }


    public function manageUsers(BulletinBoard $bulletinBoard)
    {
        $this->authorize('manageUsers', $bulletinBoard);
        $bulletinBoard->load('users');
        return view('bulletin-boards.manage-users', compact('bulletinBoard'));
    }


    public function addUser(Request $request, BulletinBoard $bulletinBoard)
    {
        $this->authorize('manageUsers', $bulletinBoard);
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:super_admin,admin,moderator',
            'permissions' => 'required|in:write,delete,edit'
        ]);
        $bulletinBoard->users()->syncWithoutDetaching([
            $request->user_id => [
                'role' => $request->role,
                'permissions' => $request->permissions,
                'assigned_by' => Auth::id()
            ]
        ]);
        return redirect()->route('bulletin-boards.manage-users', $bulletinBoard->id)
            ->with('success', 'User added successfully.');
    }




    public function removeUser(BulletinBoard $bulletinBoard, $userId)
    {

        $this->authorize('manageUsers', $bulletinBoard);

        if ($userId == Auth::id()) {
            $superAdmins = $bulletinBoard->users()
                ->wherePivot('role', 'super_admin')
                ->count();

            if ($superAdmins <= 1) {
                return redirect()->route('bulletin-boards.manage-users', $bulletinBoard->id)
                    ->with('error', 'Cannot remove the last super admin.');
            }
        }

        $bulletinBoard->users()->detach($userId);

        return redirect()->route('bulletin-boards.manage-users', $bulletinBoard->id)
            ->with('success', 'User removed successfully.');
    }
}
