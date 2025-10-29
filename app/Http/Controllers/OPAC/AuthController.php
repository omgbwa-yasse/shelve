<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the login form for public users
     */
    public function showLoginForm()
    {
        if (auth('public')->check()) {
            return redirect()->route('opac.index');
        }

        return view('opac.auth.login');
    }

    /**
     * Handle login attempt for public users
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('public')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Check if user is approved
            $user = auth('public')->user();
            if (!$user->is_approved) {
                Auth::guard('public')->logout();
                return back()->withErrors([
                    'email' => 'Your account is pending approval. Please contact the administrator.',
                ]);
            }

            return redirect()->intended(route('opac.index'))->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Show the registration form for public users
     */
    public function showRegistrationForm()
    {
        if (auth('public')->check()) {
            return redirect()->route('opac.index');
        }

        return view('opac.auth.register');
    }

    /**
     * Handle registration for public users
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:public_users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'institution' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user = PublicUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'institution' => $request->institution,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_approved' => false, // Requires admin approval
            'registration_date' => now(),
        ]);

        return redirect()->route('opac.login')->with('success',
            'Your account has been created successfully! Please wait for administrator approval before logging in.'
        );
    }

    /**
     * Logout the public user
     */
    public function logout(Request $request)
    {
        Auth::guard('public')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('opac.index')->with('success', 'You have been logged out successfully.');
    }
}
