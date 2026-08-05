<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WorkplaceMember\StoreWorkplaceMemberRequest;
use App\Http\Requests\Api\V1\WorkplaceMember\UpdateWorkplaceMemberRequest;
use App\Http\Requests\Api\V1\WorkplaceMember\UpdateWorkplaceMemberPermissionsRequest;
use App\Http\Requests\Api\V1\WorkplaceMember\UpdateWorkplaceMemberNotificationsRequest;
use App\Http\Resources\Api\V1\WorkplaceMemberResource;
use App\Mail\WorkplaceInvitationMail;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceInvitation;
use App\Models\WorkplaceMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * D12 — membres d'un espace de travail (ressource imbriquée sous `/workplaces`).
 *
 * Relevé contre `WorkplaceMemberController` (relu le 2026-08-04). Les mutations
 * exigent `manageMembers` sur le workplace parent (WorkplacePolicy) ; le rôle
 * drive les flags de permission comme en Blade. `invited_by` / `joined_at` posés
 * côté serveur. L'ajout par email crée une invitation + envoie le mail (Blade).
 */
class WorkplaceMemberController extends Controller
{
    /**
     * GET /api/v1/workplaces/{workplace}/members
     */
    public function index(Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('view', $workplace);

        $members = $workplace->members()
            ->with('user')
            ->latest('joined_at')
            ->get();

        return response()->json(['data' => WorkplaceMemberResource::collection($members)]);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/members
     */
    public function store(StoreWorkplaceMemberRequest $request, Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageMembers', $workplace);

        $data = $request->validated();

        if (!$request->filled('user_id') && !$request->filled('email')) {
            abort(422, 'Renseignez un `user_id` ou une `email`.');
        }

        if ($request->filled('user_id')) {
            $user = User::find($data['user_id']);

            if ($workplace->members()->where('user_id', $user->id)->exists()) {
                abort(422, 'Cet utilisateur est déjà membre.');
            }

            $member = $workplace->members()->create([
                'user_id' => $user->id,
                'role' => $data['role'],
                'invited_by' => Auth::id(),
                'joined_at' => now(),
                'can_create_folders' => in_array($data['role'], ['admin', 'editor']),
                'can_create_documents' => in_array($data['role'], ['admin', 'editor']),
                'can_delete' => $data['role'] === 'admin',
                'can_share' => in_array($data['role'], ['admin', 'editor']),
                'can_invite' => $data['role'] === 'admin',
            ]);

            return response()->json(
                ['data' => new WorkplaceMemberResource($member)],
                201,
                ['Location' => "/api/v1/workplaces/{$workplace->id}/members/{$member->id}"]
            );
        }

        $invitation = $workplace->invitations()->create([
            'email' => $data['email'],
            'proposed_role' => $data['role'],
            'message' => $data['message'] ?? null,
            'invited_by' => Auth::id(),
            'token' => WorkplaceInvitation::generateToken(),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($data['email'])->send(new WorkplaceInvitationMail($invitation));

        return response()->json(
            ['data' => [
                'id' => $invitation->id,
                'workplace_id' => $invitation->workplace_id,
                'email' => $invitation->email,
                'proposed_role' => $invitation->proposed_role,
                'status' => $invitation->status,
                'expires_at' => $invitation->expires_at?->toIso8601ZuluString(),
            ]],
            201
        );
    }

    /**
     * PUT /api/v1/workplaces/{workplace}/members/{member}
     */
    public function update(UpdateWorkplaceMemberRequest $request, Workplace $workplace, WorkplaceMember $member): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageMembers', $workplace);

        if ($member->user_id === Auth::id() && $member->role === 'owner') {
            abort(403, 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        $role = $request->validated()['role'];

        $member->update([
            'role' => $role,
            'can_create_folders' => in_array($role, ['admin', 'editor']),
            'can_create_documents' => in_array($role, ['admin', 'editor']),
            'can_delete' => $role === 'admin',
            'can_share' => in_array($role, ['admin', 'editor']),
            'can_invite' => $role === 'admin',
        ]);

        return response()->json(['data' => new WorkplaceMemberResource($member->fresh())]);
    }

    /**
     * DELETE /api/v1/workplaces/{workplace}/members/{member}
     */
    public function destroy(Workplace $workplace, WorkplaceMember $member): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageMembers', $workplace);

        if ($member->role === 'owner') {
            abort(403, 'Le propriétaire ne peut pas être retiré.');
        }

        $member->delete();

        return response()->json(['data' => null]);
    }

    /**
     * PUT /api/v1/workplaces/{workplace}/members/{member}/permissions
     */
    public function updatePermissions(UpdateWorkplaceMemberPermissionsRequest $request, Workplace $workplace, WorkplaceMember $member): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('manageMembers', $workplace);

        $member->update($request->validated());

        return response()->json(['data' => new WorkplaceMemberResource($member->fresh())]);
    }

    /**
     * PUT /api/v1/workplaces/{workplace}/members/{member}/notifications
     */
    public function updateNotifications(UpdateWorkplaceMemberNotificationsRequest $request, Workplace $workplace, WorkplaceMember $member): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        if ($member->user_id !== Auth::id()) {
            $this->authorize('manageMembers', $workplace);
        }

        $member->update($request->validated());

        return response()->json(['data' => new WorkplaceMemberResource($member->fresh())]);
    }

    private function workplaceInOrganisation(Workplace $workplace): Workplace
    {
        return Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);
    }
}
