<?php

namespace App\Http\Controllers;

use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class PublicUserController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = PublicUser::paginate(10);
        return view('public.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('public.users.create');
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'phone1' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:public_users',
            'password' => 'required|string|min:8|confirmed',
            'is_approved' => 'boolean',
        ]);

        // Hash the password
        $validated['password'] = Hash::make($validated['password']);

        // Create the user
        $user = PublicUser::create($validated);

        return redirect()->route('public.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     *
     * @param  \App\Models\PublicUser  $publicUser
     * @return \Illuminate\Http\Response
     */
    public function show(PublicUser $publicUser)
    {
        return view('public.users.show', compact('publicUser'));
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  \App\Models\PublicUser  $publicUser
     * @return \Illuminate\Http\Response
     */
    public function edit(PublicUser $publicUser)
    {
        return view('public.users.edit', compact('publicUser'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicUser  $publicUser
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicUser $publicUser)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'phone1' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:public_users,email,' . $publicUser->id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_approved' => 'boolean',
        ]);

        // Only hash the password if it was provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // Don't update password if not provided
        }

        $publicUser->update($validated);

        return redirect()->route('public.users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  \App\Models\PublicUser  $publicUser
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicUser $publicUser)
    {
        $publicUser->delete();

        return redirect()->route('public.users.index')
            ->with('success', 'User deleted successfully');
    }

    // ========================================
    // API METHODS pour l'interface React
    // ========================================

    /**
     * API: Login user
     */
    public function apiLogin(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = PublicUser::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Account not approved yet'
            ], 403);
        }

        $token = $user->createToken('shelve-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    /**
     * API: Register new user
     */
    public function apiRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:public_users',
            'password' => 'required|string|min:8|confirmed',
            'phone1' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_approved'] = false; // Require approval

        $user = PublicUser::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Your account is pending approval.',
            'data' => $user
        ], 201);
    }

    /**
     * API: Logout user
     */
    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * API: Verify token
     */
    public function apiVerifyToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string'
        ]);

        // For now, we'll return success since Sanctum handles token verification
        // You can add additional token validation logic here if needed
        return response()->json([
            'success' => true,
            'message' => 'Token is valid'
        ]);
    }

    /**
     * API: Forgot password (mock implementation)
     */
    public function apiForgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        $user = PublicUser::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // TODO: Implement actual password reset logic
        return response()->json([
            'success' => true,
            'message' => 'Password reset instructions sent to your email'
        ]);
    }

    /**
     * API: Reset password (mock implementation)
     */
    public function apiResetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        // TODO: Implement actual password reset logic
        return response()->json([
            'success' => true,
            'message' => 'Password reset successful'
        ]);
    }

    /**
     * API: Update user profile
     */
    public function apiUpdateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'first_name' => 'sometimes|required|string|max:255',
            'phone1' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:public_users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }
}
