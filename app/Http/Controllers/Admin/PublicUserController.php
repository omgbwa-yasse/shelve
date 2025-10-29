<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PublicUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:admin.opac.users');
    }

    /**
     * Display a listing of the public users.
     */
    public function index(Request $request)
    {
        $query = PublicUser::query();

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par statut d'approbation
        if ($request->has('approval_status') && $request->approval_status !== '') {
            $query->where('is_approved', (bool) $request->approval_status);
        }

        $users = $query->withCount([
            'documentRequests',
            'feedbacks',
            'eventRegistrations',
            'searchLogs'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return view('public.admin.opac.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new public user.
     */
    public function create()
    {
        return view('public.admin.opac.users.create');
    }

    /**
     * Store a newly created public user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'email' => 'required|email|unique:public_users,email',
            'phone1' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'required|string|min:8|confirmed',
            'is_approved' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        PublicUser::create($validated);

        return redirect()->route('admin.opac.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Display the specified public user.
     */
    public function show(PublicUser $user)
    {
        $user->load([
            'documentRequests' => function($query) {
                $query->latest()->limit(5);
            },
            'feedbacks' => function($query) {
                $query->latest()->limit(5);
            },
            'eventRegistrations' => function($query) {
                $query->with('event')->latest()->limit(5);
            },
            'searchLogs' => function($query) {
                $query->latest()->limit(10);
            }
        ]);

        return view('public.admin.opac.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified public user.
     */
    public function edit(PublicUser $user)
    {
        return view('public.admin.opac.users.edit', compact('user'));
    }

    /**
     * Update the specified public user in storage.
     */
    public function update(Request $request, PublicUser $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('public_users')->ignore($user->id)],
            'phone1' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
            'is_approved' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.opac.users.index')
            ->with('success', 'Utilisateur modifié avec succès.');
    }

    /**
     * Remove the specified public user from storage.
     */
    public function destroy(PublicUser $user)
    {
        $user->delete();

        return redirect()->route('admin.opac.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Approve a public user.
     */
    public function approve(PublicUser $user)
    {
        $user->update(['is_approved' => true]);

        return back()->with('success', 'Utilisateur approuvé avec succès.');
    }

    /**
     * Disapprove a public user.
     */
    public function disapprove(PublicUser $user)
    {
        $user->update(['is_approved' => false]);

        return back()->with('success', 'Approbation de l\'utilisateur révoquée.');
    }
}
