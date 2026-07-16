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
     * Vrai si l'utilisateur courant a le rôle DG (dans son organisation) ou est superadmin.
     */
    private function isDg(): bool
    {
        $user = Auth::user();

        return $user->isSuperAdmin()
            || $user->hasRoleInOrganisation('DG', $user->current_organisation_id);
    }

    /**
     * Abandonne la requête si l'utilisateur courant n'est pas DG.
     */
    private function requireDg(): void
    {
        abort_unless($this->isDg(), 403, 'Cette action est réservée au Directeur Général.');
    }

    /**
     * Formulaire de cotation d'un courrier entrant par le DG.
     */
    public function coteForm(Mail $mail)
    {
        $this->requireDg();

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
        $this->requireDg();

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
     * Affectation interne (cotation) d'un courrier à un agent du service,
     * par le responsable de l'organisation à laquelle il a été transmis.
     */
    public function assignToUser(Request $request, Mail $mail)
    {
        $user = Auth::user();
        $orgId = $mail->assigned_organisation_id ?? $mail->recipient_organisation_id;

        abort_unless(
            $user->isSuperAdmin() || (int) $user->current_organisation_id === (int) $orgId,
            403,
            'Seul le service destinataire peut affecter ce courrier.'
        );

        $data = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'action_id' => 'nullable|exists:mail_actions,id',
            'instruction' => 'nullable|string|max:500',
        ]);

        $mail->assignToUser((int) $data['assigned_to'], $data['action_id'] ?? null, $data['instruction'] ?? null);

        return back()->with('success', 'Courrier affecté à l\'agent avec succès.');
    }

    /**
     * Validation de la réception par le responsable du service destinataire.
     */
    public function confirmReception(Mail $mail)
    {
        // Le responsable (ou un membre) de l'organisation destinataire/assignée valide la réception.
        $user = Auth::user();
        $orgId = $mail->assigned_organisation_id ?? $mail->recipient_organisation_id;
        abort_unless(
            $user->isSuperAdmin() || (int) $user->current_organisation_id === (int) $orgId,
            403,
            'Seul le service destinataire peut valider la réception.'
        );

        $mail->confirmReception(Auth::id());

        return back()->with('success', 'Réception du courrier validée.');
    }

    /**
     * Soumission d'un courrier sortant pour validation (N+1 / DG),
     * avec note explicative facultative.
     */
    public function submit(Request $request, Mail $mail)
    {
        // L'initiateur (expéditeur) du courrier, ou un membre de son organisation, peut soumettre.
        $user = Auth::user();
        abort_unless(
            $user->isSuperAdmin()
                || (int) $mail->sender_user_id === (int) $user->id
                || (int) $mail->sender_organisation_id === (int) $user->current_organisation_id,
            403,
            'Vous ne pouvez pas soumettre ce courrier.'
        );

        $data = $request->validate([
            'explanatory_note' => 'nullable|string|max:2000',
        ]);

        $mail->submitForApproval($data['explanatory_note'] ?? null);

        return back()->with('success', 'Courrier soumis pour validation.');
    }

    /**
     * L'utilisateur courant est-il le supérieur hiérarchique (N+1) de
     * l'initiateur de ce courrier ? (ou le DG / superadmin.)
     */
    private function isSuperiorFor(Mail $mail): bool
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $this->isDg()) {
            return true;
        }

        $sender = $mail->sender_user_id ? \App\Models\User::find($mail->sender_user_id) : null;
        if (!$sender) {
            return false;
        }

        $superior = $sender->hierarchicalSuperior($mail->sender_organisation_id);

        return $superior && (int) $superior->id === (int) $user->id;
    }

    /**
     * Validation intermédiaire par le supérieur hiérarchique (N+1) :
     * le courrier passe ensuite à la signature du DG.
     */
    public function validateByN1(Request $request, Mail $mail)
    {
        abort_unless($this->isSuperiorFor($mail), 403, 'Seul le supérieur hiérarchique peut valider ce courrier.');

        $data = $request->validate(['note' => 'nullable|string|max:1000']);
        $mail->validateBySuperior(Auth::id(), $data['note'] ?? null);

        return back()->with('success', 'Courrier validé et transmis à la signature du DG.');
    }

    /**
     * Signature / validation finale par le DG.
     */
    public function sign(Request $request, Mail $mail)
    {
        // Seul le DG (ou superadmin) peut signer / valider définitivement.
        $this->requireDg();

        $data = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $mail->signByDg(Auth::id(), $data['note'] ?? null);

        return back()->with('success', 'Courrier signé et transmis.');
    }

    /**
     * Rejet définitif par le DG d'un courrier soumis.
     */
    public function reject(Request $request, Mail $mail)
    {
        $this->requireDg();

        $data = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $mail->rejectByDg(Auth::id(), $data['note'] ?? null);

        return back()->with('success', 'Courrier rejeté.');
    }

    /**
     * Renvoi pour révision (par le N+1 ou le DG) : le courrier retourne à son
     * initiateur, qui pourra corriger et resoumettre.
     */
    public function returnForRevision(Request $request, Mail $mail)
    {
        abort_unless($this->isSuperiorFor($mail), 403, 'Vous ne pouvez pas renvoyer ce courrier pour révision.');

        $data = $request->validate([
            'note' => 'required|string|max:1000',
        ], [
            'note.required' => 'Merci de préciser les modifications à apporter.',
        ]);

        $mail->returnForRevision(Auth::id(), $data['note']);

        return back()->with('success', 'Courrier renvoyé à l\'initiateur pour révision.');
    }

    /**
     * Resoumission par l'initiateur après révision, avec ajout éventuel de
     * pièces jointes correctives.
     */
    public function resubmit(Request $request, Mail $mail)
    {
        $user = Auth::user();
        abort_unless(
            $user->isSuperAdmin()
                || (int) $mail->sender_user_id === (int) $user->id
                || (int) $mail->sender_organisation_id === (int) $user->current_organisation_id,
            403,
            'Vous ne pouvez pas resoumettre ce courrier.'
        );

        $data = $request->validate([
            'note' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
        ]);

        // Pièces jointes correctives éventuelles.
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->attachRevisionFile($file, $mail);
            }
        }

        $mail->resubmit($data['note'] ?? null);

        return back()->with('success', 'Courrier corrigé et resoumis.');
    }

    /**
     * Stocke un fichier de révision et l'associe au courrier.
     */
    private function attachRevisionFile($file, Mail $mail): void
    {
        $path = $file->store('mail_attachments');

        $attachment = \App\Models\MailAttachment::create([
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'crypt' => md5_file($file->getRealPath()),
            'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
            'size' => $file->getSize(),
            'creator_id' => Auth::id(),
            'type' => 'mail',
            'mime_type' => $file->getMimeType(),
        ]);

        $mail->attachments()->attach($attachment->id, [
            'added_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
