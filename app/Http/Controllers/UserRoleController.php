<?php

namespace App\Http\Controllers;
use App\models\User;
use App\models\Role;
use App\models\UserRole;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{


    public function index()
    {
        $userRoles = UserRole::with('user', 'role')->get();
        return view('user_roles.index', compact('userRoles'));
    }




    public function create()
    {
        $users = User::all();
        $roles = Role::all();
        return view('user_roles.create', compact('users', 'roles'));
    }



    public function edit(UserRole $userRole)
    {
        $users = User::all();
        $roles = Role::all();
        return view('user_roles.edit', compact('userRole', 'users', 'roles'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        UserRole::create($request->all());

        return redirect()->route('users.show', $request->user_id)
            ->with('success', 'Role assigned to user successfully.');
    }



    public function destroy(User $user, Role $role)
    {
        UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->delete();

        return redirect()->route('users.show', $user->id)
            ->with('success', 'Role removed from user successfully.');
    }


}


