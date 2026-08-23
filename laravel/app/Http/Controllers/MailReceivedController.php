<?php

namespace App\Http\Controllers;

use App\Services\Mail\MailActivityService;

use App\Services\Mail\MailCodeService;

use App\Models\Attachment;
use App\Models\Mail;
use App\Models\User;
use App\Models\MailAction;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Dolly;
use App\Models\DollyType;
use App\Models\Organisation;
use App\Models\ExternalContact;
use App\Models\ExternalOrganization;
use App\Enums\MailStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class MailReceivedController extends Controller
{
    public function index()
    {
        return app(MailController::class)->index('received');
    }

    public function inprogress()
    {
        $userId = Auth::id();
        $mails = Mail::with(['action', 'sender', 'senderOrganisation'])
                     ->where('recipient_user_id', $userId)
                     ->where('status', MailStatusEnum::IN_PROGRESS)
                     ->get();
        $dollies = Dolly::all();
        $categories = Dolly::categories();
        $users = User::all();
        $type = 'received';
        return view('mails.index', compact('mails', 'dollies', 'categories', 'users', 'type'));
    }

    public function approve(Mail $mail)
    {
        $mail->update([
            'recipient_user_id' => Auth::id(),
            'status' => MailStatusEnum::TRANSMITTED,
        ]);

        return redirect()->route('mail-received.index')
                         ->with('success', 'Mail approved successfully.');
    }

    public function reject(Mail $mail)
    {
        $mail->update([
            'recipient_user_id' => Auth::id(),
            'status' => MailStatusEnum::REJECTED,
        ]);

        return redirect()->route('mail-received.index')
                         ->with('success', 'Mail updated successfully');
    }

    public function create()
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;
        $mailActions = MailAction::orderBy('name')->get();
        $senderOrganisations = Organisation::where('id', '!=', $currentOrganisationId)->orderBy('name')->get();
        $users = User::all();
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();

        // Récupérer les contacts et organisations externes
        $externalContacts = ExternalContact::orderBy('last_name')->orderBy('first_name')->get();
        $externalOrganizations = ExternalOrganization::orderBy('name')->get();

        // Activité du plan de classement : indispensable au routage des intérims
        // par volet — un courrier sans activité n'est traitable par aucun volet.
        $activities = app(MailActivityService::class)->optionsFor(Auth::user());

        return view('mails.received.create', compact(
            'mailActions',
            'senderOrganisations',
            'users',
            'priorities',
            'typologies',
            'externalContacts',
            'externalOrganizations',
            'activities'
        ));
    }

    public function store(Request $request)
    {
        Gate::authorize('mail_create');

        // Validation de base
        $baseValidation = [
            'code' => 'nullable|string|max:50',
            'name' => 'required|max:150',
            'date' => 'required|date',
            'description' => 'nullable',
            'document_type' => 'required|in:original,duplicate,copy',
            'action_id' => 'required|exists:mail_actions,id',
            'priority_id' => 'required|exists:mail_priorities,id',
            'typology_id' => 'required|exists:mail_typologies,id',
            'activity_id' => 'nullable|exists:activities,id',
            'sender_type' => 'required|in:internal,external_contact,external_organization',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,gif',
            'attachments_pending' => 'nullable|array',
            'attachments_pending.*' => 'integer|exists:attachments,id',
        ];

        // Validation conditionnelle selon le type d'expéditeur
        if ($request->input('sender_type') === 'internal') {
            $senderValidation = [
                'sender_user_id' => 'required|exists:users,id',
                'sender_organisation_id' => 'required|exists:organisations,id',
            ];
        } elseif ($request->input('sender_type') === 'external_contact') {
            $senderValidation = [
                'external_sender_id' => 'required|exists:external_contacts,id',
            ];
        } elseif ($request->input('sender_type') === 'external_organization') {
            $senderValidation = [
                'external_sender_organization_id' => 'required|exists:external_organizations,id',
            ];
        } else {
            $senderValidation = [];
        }

        $validatedData = $request->validate(array_merge($baseValidation, $senderValidation));

        // Génération ou validation du code de courrier
        if (!isset($validatedData['code']) || empty($validatedData['code'])) {
            // Pas de code fourni : générer automatiquement
            $validatedData['code'] = $this->generateMailCode($validatedData['typology_id']);
        } else {
            // Code fourni : vérifier qu'il n'existe pas déjà
            $existingMail = Mail::where('code', $validatedData['code'])->first();
            if ($existingMail) {
                return back()->withErrors(['code' => 'Un mail avec ce code existe déjà.'])->withInput();
            }
        }

        // Préparer les données de base du courrier
        $mailData = [
            'code' => $validatedData['code'],
            'name' => $validatedData['name'],
            'date' => $validatedData['date'],
            'description' => $validatedData['description'],
            'document_type' => $validatedData['document_type'],
            'action_id' => $validatedData['action_id'],
            'priority_id' => $validatedData['priority_id'],
            'typology_id' => $validatedData['typology_id'],
            'activity_id' => $validatedData['activity_id'] ?? null,
            'recipient_organisation_id' => Auth::user()->current_organisation_id,
            'recipient_user_id' => Auth::id(),
            'status' => MailStatusEnum::IN_PROGRESS,
            'mail_type' => 'incoming', // Courrier entrant
            'recipient_type' => 'user', // Le destinataire est toujours un utilisateur interne
        ];

        // Ajouter les données de l'expéditeur selon le type
        if ($validatedData['sender_type'] === 'internal') {
            $mailData['sender_user_id'] = $validatedData['sender_user_id'];
            $mailData['sender_organisation_id'] = $validatedData['sender_organisation_id'];
            $mailData['sender_type'] = 'user';
        } elseif ($validatedData['sender_type'] === 'external_contact') {
            $mailData['external_sender_id'] = $validatedData['external_sender_id'];
            $mailData['sender_type'] = 'external_contact';

            // Vérifier si le contact externe appartient à une organisation et l'ajouter si c'est le cas
            $externalContact = ExternalContact::find($validatedData['external_sender_id']);
            if ($externalContact && $externalContact->external_organization_id) {
                $mailData['external_sender_organization_id'] = $externalContact->external_organization_id;
            }
        } elseif ($validatedData['sender_type'] === 'external_organization') {
            $mailData['external_sender_organization_id'] = $validatedData['external_sender_organization_id'];
            $mailData['sender_type'] = 'external_organization';
        }

        $mail = Mail::create($mailData);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachment = Attachment::createFromUpload($file, Attachment::TYPE_ATTACHMENT, Auth::id());
                $mail->attachments()->attach($attachment->id, ['added_by' => Auth::id()]);
            }
        }

        if (!empty($validatedData['attachments_pending'])) {
            $pending = Attachment::whereIn('id', $validatedData['attachments_pending'])
                ->where('creator_id', Auth::id())
                ->pluck('id');
            $mail->attachments()->attach($pending, ['added_by' => Auth::id()]);
        }

        return redirect()->route('mail-received.index')
                         ->with('success', 'Mail créé avec succès.');
    }

    public function incoming(Request $request)
    {
        // Validation de base
        $baseValidation = [
            'name' => 'required|max:150',
            'date' => 'required|date',
            'description' => 'nullable',
            'document_type' => 'required|in:original,duplicate,copy',
            'action_id' => 'required|exists:mail_actions,id',
            'priority_id' => 'required|exists:mail_priorities,id',
            'typology_id' => 'required|exists:mail_typologies,id',
            'activity_id' => 'nullable|exists:activities,id',
            'sender_type' => 'required|in:external_contact,external_organization',
        ];

        // Validation conditionnelle selon le type d'expéditeur
        if ($request->input('sender_type') === 'external_contact') {
            $senderValidation = [
                'external_sender_id' => 'required|exists:external_contacts,id',
            ];
        } elseif ($request->input('sender_type') === 'external_organization') {
            $senderValidation = [
                'external_sender_organization_id' => 'required|exists:external_organizations,id',
            ];
        } else {
            $senderValidation = [];
        }

        $validatedData = $request->validate(array_merge($baseValidation, $senderValidation));

        // Génération du code de courrier
        if (!isset($validatedData['code']) || empty($validatedData['code'])) {
            $validatedData['code'] = $this->generateMailCode($validatedData['typology_id']);
        } else {
            $existingMail = Mail::where('code', $validatedData['code'])->first();
            if ($existingMail) {
                return back()->withErrors(['code' => 'Un mail avec ce code existe déjà.'])->withInput();
            }
        }

        // Préparer les données de base du courrier
        $mailData = [
            'code' => $validatedData['code'],
            'name' => $validatedData['name'],
            'date' => $validatedData['date'],
            'description' => $validatedData['description'],
            'document_type' => $validatedData['document_type'],
            'action_id' => $validatedData['action_id'],
            'priority_id' => $validatedData['priority_id'],
            'typology_id' => $validatedData['typology_id'],
            'activity_id' => $validatedData['activity_id'] ?? null,
            'recipient_organisation_id' => Auth::user()->current_organisation_id,
            'recipient_user_id' => Auth::id(),
            'status' => MailStatusEnum::IN_PROGRESS,
            'mail_type' => 'incoming', // Courrier entrant
            'recipient_type' => 'user', // Le destinataire est toujours un utilisateur interne
        ];

        // Ajouter les données de l'expéditeur selon le type
        if ($validatedData['sender_type'] === 'external_contact') {
            $mailData['external_sender_id'] = $validatedData['external_sender_id'];
            $mailData['sender_type'] = 'external_contact';

            // Vérifier si le contact externe appartient à une organisation et l'ajouter si c'est le cas
            $externalContact = ExternalContact::find($validatedData['external_sender_id']);
            if ($externalContact && $externalContact->external_organization_id) {
                $mailData['external_sender_organization_id'] = $externalContact->external_organization_id;
            }
        } elseif ($validatedData['sender_type'] === 'external_organization') {
            $mailData['external_sender_organization_id'] = $validatedData['external_sender_organization_id'];
            $mailData['sender_type'] = 'external_organization';
        }

        Mail::create($mailData);

        return redirect()->route('mail-received.index')
                         ->with('success', 'Courrier entrant créé avec succès.');
    }

    public function createIncoming()
    {
        $typologies = MailTypology::all();
        $priorities = MailPriority::all();
        $mailActions = MailAction::orderBy('name')->get();

        // Récupérer les contacts et organisations externes
        $externalContacts = ExternalContact::orderBy('last_name')->orderBy('first_name')->get();
        $externalOrganizations = ExternalOrganization::orderBy('name')->get();

        return view('mails.received.createIncoming', compact(
            'typologies',
            'priorities',
            'mailActions',
            'externalContacts',
            'externalOrganizations'
        ));
    }

    public function generateMailCode(int $typologie_id)
    {
        // Numérotation du registre : attribution transactionnelle et unique, via
        // MailCodeService. Le format AAAA/CODE/0001 reste inchangé.
        return app(MailCodeService::class)->nextForTypology($typologie_id);
    }

    public function show(INT $mail_id)
    {
        Gate::authorize('mail_view');

        return app(MailController::class)->show('received', $mail_id);
    }

    public function edit(Mail $received)
    {
        Gate::authorize('mail_update');

        $received->load([
            'action',
            'sender',
            'senderOrganisation',
            'recipient',
            'recipientOrganisation',
            'attachments'
        ]);

        $mailActions = MailAction::all();
        $senderOrganisations = Organisation::whereNot('id', Auth::user()->current_organisation_id)->get();
        $users = User::all();
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();

        // The edit view references $mail; expose the loaded record under both names.
        $mail = $received;

        $activities = app(MailActivityService::class)->optionsFor(Auth::user());

        return view('mails.received.edit', compact('received', 'mail', 'mailActions', 'senderOrganisations', 'users', 'priorities', 'typologies', 'activities'));
    }

    public function update(Request $request, int $id)
    {
        Gate::authorize('mail_update');

        $mail = Mail::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|max:150',
            'date' => 'required|date',
            'description' => 'nullable',
            'document_type' => 'required|in:original,duplicate,copy',
            'action_id' => 'required|exists:mail_actions,id',
            'sender_user_id' => 'required|exists:users,id',
            'sender_organisation_id' => 'required|exists:organisations,id',
            'priority_id' => 'required|exists:mail_priorities,id',
            'typology_id' => 'required|exists:mail_typologies,id',
            'activity_id' => 'nullable|exists:activities,id',
        ]);

        $mail->update($validatedData);

        return redirect()->route('mail-received.index')
                         ->with('success', 'Mail updated successfully');
    }

    /**
     * Affiche uniquement les courriers reçus qu'on doit retourner
     * - Courriers reçus par l'utilisateur qui ont une action avec to_return = true
     */
    public function toReturn()
    {
        $userId = Auth::id();

        // Courriers reçus par l'utilisateur qui doivent être retournés
        $mails = Mail::with(['action', 'sender', 'senderOrganisation', 'typology', 'priority'])
                    ->where('recipient_user_id', $userId)
                    ->whereHas('action', function($query) {
                        $query->where('to_return', true);
                    })
                    ->where('mail_type', Mail::TYPE_INTERNAL)
                    ->where('is_archived', false)
                    ->orderBy('created_at', 'desc')
                    ->get();

        $dollies = Dolly::all();
        $categories = Dolly::categories();
        $users = User::all();

        return view('mails.received.toReturn', compact(
            'mails',
            'dollies',
            'categories',
            'users'
        ));
    }

    public function returned()
    {
        $userId = Auth::id();

        // Courriers émis par l'utilisateur qui ont été retournés
        $mails = Mail::with(['action', 'recipient', 'recipientOrganisation', 'typology', 'priority'])
                    ->where('sender_user_id', $userId)
                    ->whereHas('action', function($query) {
                        $query->where('to_return', true);
                    })
                    ->where('mail_type', Mail::TYPE_INTERNAL)
                    ->where('is_archived', false)
                    ->where('status', MailStatusEnum::TRANSMITTED)
                    ->orderBy('created_at', 'desc')
                    ->get();

        $dollies = Dolly::all();
        $categories = Dolly::categories();
        $users = User::all();

        return view('mails.received.returned', compact(
            'mails',
            'dollies',
            'categories',
            'users'
        ));
    }

    public function destroy($id)
    {
        Gate::authorize('mail_delete');

        $mail = Mail::findOrFail($id);
        $mail->delete();

        return redirect()->route('mail-received.index')->with('success', 'Mail deleted successfully');
    }
}
