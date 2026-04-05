<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{



    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }




    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('roles'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'birthday' => 'required|date',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->birthday = $request->birthday;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        if ($request->filled('role_id')) {
            $user->roles()->attach($request->role_id);
        }

        return redirect()->route('settings.users.index')
            ->with('success', 'User created successfully.');
    }





    public function show(User $user)
    {
        // Charger les organisations auxquelles l'utilisateur est affilié
        $user->load('organisations');
        return view('users.show', compact('user'));
    }



    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }





    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'birthday' => 'required|date',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->birthday = $request->birthday;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('settings.users.index')
            ->with('success', 'User updated successfully.');
    }




    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', 'User deleted successfully.');
    }

}


