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
    public function show(PublicUser $user)
    {
        return view('public.users.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  \App\Models\PublicUser  $publicUser
     * @return \Illuminate\Http\Response
     */
    public function edit(PublicUser $user)
    {
        return view('public.users.edit', ['user' => $user]);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicUser  $publicUser
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicUser $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'phone1' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:public_users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_approved' => 'boolean',
        ]);

        // Only hash the password if it was provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // Don't update password if not provided
        }

        $user->update($validated);

        return redirect()->route('public.users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  \App\Models\PublicUser  $publicUser
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicUser $user)
    {
        $user->delete();

        return redirect()->route('public.users.index')
            ->with('success', 'User deleted successfully');
    }

    /**
     * Activate a user account.
     *
     * @param  \App\Models\PublicUser  $user
     * @return \Illuminate\Http\Response
     */
    public function activate(PublicUser $user)
    {
        $user->update(['is_approved' => true]);

        return redirect()->route('public.users.show', $user)
            ->with('success', 'User account activated successfully.');
    }

    /**
     * Deactivate a user account.
     *
     * @param  \App\Models\PublicUser  $user
     * @return \Illuminate\Http\Response
     */
    public function deactivate(PublicUser $user)
    {
        $user->update(['is_approved' => false]);

        return redirect()->route('public.users.show', $user)
            ->with('success', 'User account deactivated successfully.');
    }

    // ========================================
    // ========================================
    // API METHODS pour l'interface React
    // DEPRECATED: These methods have been moved to Api\PublicUserApiController
    // These methods are kept for backward compatibility and will be removed in future versions
    // ========================================

    /**
     * API: Login user
     * @deprecated Use Api\PublicUserApiController::login() instead
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
     * @deprecated Use Api\PublicUserApiController::register() instead
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

        // S'assurer que les champs requis en base ont une valeur par défaut
        $validated['phone1'] = $validated['phone1'] ?? 'Non renseigné';
        $validated['phone2'] = 'Non renseigné'; // Valeur par défaut
        $validated['address'] = $validated['address'] ?? 'Non renseignée';

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
