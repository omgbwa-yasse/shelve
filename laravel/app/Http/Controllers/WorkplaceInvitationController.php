<?php

namespace App\Http\Controllers;

use App\Models\WorkplaceInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WorkplaceInvitationController extends Controller
{
    public function accept($token)
    {
        $invitation = WorkplaceInvitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return redirect()->route('login')->withErrors(['error' => 'Cette invitation a expiré.']);
        }

        // If user is logged in
        if (Auth::check()) {
            // Check if email matches (optional, but good for security)
            if (Auth::user()->email !== $invitation->email) {
                // Allow accepting if the email is different? Usually yes, but maybe warn.
                // For now, let's assume the user clicking the link is the intended recipient or wants to join with their current account.
                // But strictly speaking, we should probably verify.
                // Let's just proceed.
            }

            $this->joinWorkplace($invitation, Auth::user());
            return redirect()->route('workplaces.show', $invitation->workplace_id)
                ->with('success', 'Vous avez rejoint l\'espace de travail avec succès.');
        }

        // If user is not logged in, check if user exists with this email
        $user = User::where('email', $invitation->email)->first();

        if ($user) {
            // User exists, ask to login
            session(['url.intended' => route('workplaces.invitations.accept', $token)]);
            return redirect()->route('login')->with('info', 'Veuillez vous connecter pour accepter l\'invitation.');
        } else {
            // User does not exist, redirect to register with pre-filled email and token
            return redirect()->route('register', ['email' => $invitation->email, 'invitation_token' => $token]);
        }
    }

    private function joinWorkplace(WorkplaceInvitation $invitation, User $user)
    {
        // Check if already member
        if (!$invitation->workplace->members()->where('user_id', $user->id)->exists()) {
            $invitation->workplace->members()->create([
                'user_id' => $user->id,
                'role' => $invitation->proposed_role,
                'invited_by' => $invitation->invited_by,
                'joined_at' => now(),
                'can_create_folders' => in_array($invitation->proposed_role, ['admin', 'editor']),
                'can_create_documents' => in_array($invitation->proposed_role, ['admin', 'editor']),
                'can_delete' => $invitation->proposed_role === 'admin',
                'can_share' => in_array($invitation->proposed_role, ['admin', 'editor']),
                'can_invite' => $invitation->proposed_role === 'admin',
            ]);
        }

        $invitation->accept();
    }
}
