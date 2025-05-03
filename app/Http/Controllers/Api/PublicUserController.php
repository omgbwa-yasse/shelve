<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PublicUserController extends Controller
{
    public function index()
    {
        $users = PublicUser::paginate(10);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'phone1' => 'required|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:public_users',
            'password' => ['required', Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = PublicUser::create($validated);

        return response()->json($user, 201);
    }

    public function show(PublicUser $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, PublicUser $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'first_name' => 'sometimes|string|max:255',
            'phone1' => 'sometimes|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:public_users,email,' . $user->id,
            'password' => ['sometimes', Password::defaults()],
            'is_approved' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    public function destroy(PublicUser $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }

    public function approve(PublicUser $user)
    {
        $user->update(['is_approved' => true]);
        return response()->json($user);
    }

    public function reject(PublicUser $user)
    {
        $user->update(['is_approved' => false]);
        return response()->json($user);
    }
}
