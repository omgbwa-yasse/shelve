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

    /**
     * Display the public dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $totalUsers = PublicUser::count();
        $activeUsers = PublicUser::where('is_approved', true)->count();
        $pendingUsers = PublicUser::where('is_approved', false)->count();
        $recentUsers = PublicUser::latest()->limit(5)->get();

        return view('public.dashboard', compact('totalUsers', 'activeUsers', 'pendingUsers', 'recentUsers'));
    }

    /**
     * Display statistics for public module.
     *
     * @return \Illuminate\Http\Response
     */
    public function statistics()
    {
        $stats = [
            'users' => [
                'total' => PublicUser::count(),
                'active' => PublicUser::where('is_approved', true)->count(),
                'pending' => PublicUser::where('is_approved', false)->count(),
                'new_this_month' => PublicUser::whereMonth('created_at', now()->month)->count(),
            ],
        ];

        // Add news statistics if available
        if (class_exists('\App\Models\PublicNews')) {
            $stats['news'] = [
                'total' => \App\Models\PublicNews::count(),
                'published' => \App\Models\PublicNews::where('status', 'published')->count(),
            ];
        }

        // Add events statistics if available
        if (class_exists('\App\Models\PublicEvent')) {
            $stats['events'] = [
                'total' => \App\Models\PublicEvent::count(),
                'upcoming' => \App\Models\PublicEvent::where('start_date', '>', now())->count(),
            ];
        }

        // Add document requests statistics if available
        if (class_exists('\App\Models\PublicDocumentRequest')) {
            $stats['document_requests'] = [
                'total' => \App\Models\PublicDocumentRequest::count(),
                'pending' => \App\Models\PublicDocumentRequest::where('status', 'pending')->count(),
                'completed' => \App\Models\PublicDocumentRequest::where('status', 'completed')->count(),
            ];
        }

        return view('public.statistics', compact('stats'));
    }

    // ========================================
}
