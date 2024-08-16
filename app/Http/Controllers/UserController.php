<?php

namespace App\Http\Controllers;

use App\models\User;
use App\models\UserType;
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
        return view('users.create');
    }



    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'birthday' => 'required|date',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->birthday = $request->birthday;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }





    public function show(User $user)
    {
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

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }




    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

}


