<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\Organisation;
use App\Models\MailAction;
use App\Enums\MailStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Actions du workflow courrier « zéro papier » :
 *  - Circuit entrant : cotation par le DG, validation de la réception.
 *  - Circuit sortant : soumission pour validation (N+1 / DG), signature ou rejet DG.
 */
class MailWorkflowController extends Controller
{
    /**
     * Formulaire de cotation d'un courrier entrant par le DG.
     */
    public function coteForm(Mail $mail)
    {
        $this->authorize('update', $mail);

        $organisations = Organisation::orderBy('name')->get();
        // Instructions de cotation du DG (sous-ensemble des actions).
        $instructions = MailAction::whereIn('name', ['Donner suite', "M'expliquer", 'En parler', 'Classer'])
            ->orderBy('name')
            ->get();

        return view('mails.workflow.cote', compact('mail', 'organisations', 'instructions'));
    }

    /**
     * Enregistre la cotation : affectation à une direction + instruction.
     */
    public function cote(Request $request, Mail $mail)
    {
        $this->authorize('update', $mail);

        $data = $request->validate([
            'assigned_organisation_id' => 'required|exists:organisations,id',
            'action_id' => 'nullable|exists:mail_actions,id',
            'instruction' => 'nullable|string|max:500',
        ]);

        $mail->cote(
            (int) $data['assigned_organisation_id'],
            $data['action_id'] ?? null,
            $data['instruction'] ?? null
        );

        return redirect()->route('mails.incoming.show', $mail->id)
            ->with('success', 'Courrier coté et affecté avec succès.');
    }

    /**
     * Validation de la réception par le responsable du service destinataire.
     */
    public function confirmReception(Mail $mail)
    {
        $this->authorize('update', $mail);

        $mail->confirmReception(Auth::id());

        return back()->with('success', 'Réception du courrier validée.');
    }

    /**
     * Soumission d'un courrier sortant pour validation (N+1 / DG),
     * avec note explicative facultative.
     */
    public function submit(Request $request, Mail $mail)
    {
        $this->authorize('update', $mail);

        $data = $request->validate([
            'explanatory_note' => 'nullable|string|max:2000',
        ]);

        $mail->submitForApproval($data['explanatory_note'] ?? null);

        return back()->with('success', 'Courrier soumis pour validation.');
    }

    /**
     * Signature / validation finale par le DG.
     */
    public function sign(Request $request, Mail $mail)
    {
        $this->authorize('update', $mail);

        // Seul un utilisateur ayant le rôle DG dans son organisation courante peut signer.
        if (!Auth::user()->hasRoleInOrganisation('DG', Auth::user()->current_organisation_id)
            && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Seul le Directeur Général peut signer ce courrier.');
        }

        $data = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $mail->signByDg(Auth::id(), $data['note'] ?? null);

        return back()->with('success', 'Courrier signé et transmis.');
    }

    /**
     * Rejet par le DG (ou le N+1) d'un courrier soumis.
     */
    public function reject(Request $request, Mail $mail)
    {
        $this->authorize('update', $mail);

        $data = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $mail->rejectByDg(Auth::id(), $data['note'] ?? null);

        return back()->with('success', 'Courrier rejeté.');
    }
}
