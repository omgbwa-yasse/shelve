<?php

namespace App\Http\Controllers;

use App\Models\UserOrganisationRole;
use App\Models\User;
use App\Models\Role;
use App\Models\Organisation;
use Illuminate\Http\Request;

class UserOrganisationRoleController extends Controller
{


    public function index()
    {
        $userOrganisationRoles = UserOrganisationRole::with(['user', 'organisation', 'role', 'creator'])->get();
        return view('users.organisations.index', compact('userOrganisationRoles'));
    }



    public function create()
    {
        $users = User::all();
        $organisations = Organisation::all();
        $roles = Role::all();

        return view('users.organisations.create', compact('users', 'organisations', 'roles'));
    }




    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $validatedData['creator_id'] = auth()->id();

        $userOrganisationRole = UserOrganisationRole::firstOrCreate(
            ['user_id' => $validatedData['user_id'], 'organisation_id' => $validatedData['organisation_id']],
            $validatedData
        );

        if ($userOrganisationRole->wasRecentlyCreated) {
            return redirect()->route('user-organisation-role.index')->with('success', 'User organisation role created successfully.');
        } else {
            return redirect()->route('user-organisation-role.index')->with('warning', 'User organisation role already exists.');
        }
    }




    public function show(INT $u_id, INT $o_id)
    {
        $userOrganisationRole = UserOrganisationRole::where('user_id', $u_id)
            ->where('organisation_id', $o_id)
            ->firstOrFail();
        return view('users.organisations.show', compact('userOrganisationRole'));
    }




    public function edit(INT $u_id, INT $o_id)
    {
        $users = User::all();
        $organisations = Organisation::all();
        $roles = Role::all();

        $userOrganisationRole = UserOrganisationRole::where('user_id', $u_id)
            ->where('organisation_id', $o_id)
            ->firstOrFail();

        return view('users.organisations.edit', compact('userOrganisationRole', 'users', 'organisations', 'roles'));
    }




    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $userOrganisationRole = UserOrganisationRole::where('user_id', $validatedData['user_id'])
            ->where('organisation_id', $validatedData['organisation_id'])
            ->firstOrFail();

        $userOrganisationRole->update($validatedData);

        return redirect()->route('user-organisation-role.index')->with('success', 'User organisation role updated successfully.');
    }



    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
        ]);

        $userOrganisationRole = UserOrganisationRole::where('user_id', $validatedData['user_id'])
            ->where('organisation_id', $validatedData['organisation_id'])
            ->firstOrFail();

        $userOrganisationRole->delete();

        return redirect()->route('user-organisation-role.index')->with('success', 'User organisation role deleted successfully.');
    }
}
