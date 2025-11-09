<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\WorkplaceMember;
use App\Models\WorkplaceInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WorkplaceMemberController extends Controller
{
    public function index(Workplace $workplace)
    {
        $members = $workplace->members()
            ->with('user')
            ->latest('joined_at')
            ->get();

        $invitations = $workplace->invitations()
            ->pending()
            ->latest()
            ->get();

        return view('workplaces.members.index', compact('workplace', 'members', 'invitations'));
    }

    public function store(Request $request, Workplace $workplace)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email',
            'role' => 'required|in:admin,editor,contributor,viewer',
            'message' => 'nullable|string',
        ]);

        // Check if adding existing user or inviting by email
        if ($request->filled('user_id')) {
            $user = User::find($validated['user_id']);

            // Check if already member
            if ($workplace->members()->where('user_id', $user->id)->exists()) {
                return back()->withErrors(['error' => 'Cet utilisateur est déjà membre']);
            }

            $workplace->members()->create([
                'user_id' => $user->id,
                'role' => $validated['role'],
                'invited_by' => Auth::id(),
                'joined_at' => now(),
                'can_create_folders' => in_array($validated['role'], ['admin', 'editor']),
                'can_create_documents' => in_array($validated['role'], ['admin', 'editor']),
                'can_delete' => $validated['role'] === 'admin',
                'can_share' => in_array($validated['role'], ['admin', 'editor']),
                'can_invite' => $validated['role'] === 'admin',
            ]);

            return back()->with('success', 'Membre ajouté avec succès');
        } else {
            // Create invitation
            $invitation = $workplace->invitations()->create([
                'email' => $validated['email'],
                'proposed_role' => $validated['role'],
                'message' => $validated['message'] ?? null,
                'invited_by' => Auth::id(),
                'token' => WorkplaceInvitation::generateToken(),
                'status' => 'pending',
                'expires_at' => now()->addDays(7),
            ]);

            // TODO: Send invitation email
            // Mail::to($validated['email'])->send(new WorkplaceInvitationMail($invitation));

            return back()->with('success', 'Invitation envoyée avec succès');
        }
    }

    public function update(Request $request, Workplace $workplace, WorkplaceMember $member)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,editor,contributor,viewer',
        ]);

        // Prevent changing own role if owner
        if ($member->user_id === Auth::id() && $member->role === 'owner') {
            return back()->withErrors(['error' => 'Vous ne pouvez pas modifier votre propre rôle']);
        }

        $member->update([
            'role' => $validated['role'],
            'can_create_folders' => in_array($validated['role'], ['admin', 'editor']),
            'can_create_documents' => in_array($validated['role'], ['admin', 'editor']),
            'can_delete' => $validated['role'] === 'admin',
            'can_share' => in_array($validated['role'], ['admin', 'editor']),
            'can_invite' => $validated['role'] === 'admin',
        ]);

        return back()->with('success', 'Rôle du membre mis à jour avec succès');
    }

    public function destroy(Workplace $workplace, WorkplaceMember $member)
    {
        // Prevent removing owner
        if ($member->role === 'owner') {
            return back()->withErrors(['error' => 'Le propriétaire ne peut pas être retiré']);
        }

        $member->delete();
        return back()->with('success', 'Membre retiré avec succès');
    }

    public function updatePermissions(Request $request, Workplace $workplace, WorkplaceMember $member)
    {
        $validated = $request->validate([
            'can_create_folders' => 'boolean',
            'can_create_documents' => 'boolean',
            'can_delete' => 'boolean',
            'can_share' => 'boolean',
            'can_invite' => 'boolean',
        ]);

        $member->update($validated);

        return back()->with('success', 'Permissions mises à jour avec succès');
    }

    public function updateNotifications(Request $request, Workplace $workplace, WorkplaceMember $member)
    {
        $validated = $request->validate([
            'notify_on_new_content' => 'boolean',
            'notify_on_mentions' => 'boolean',
            'notify_on_updates' => 'boolean',
        ]);

        $member->update($validated);

        return back()->with('success', 'Préférences de notification mises à jour');
    }
}
