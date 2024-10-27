<?php

namespace App\Http\Controllers;

use App\Models\documentType;
use App\Models\MailAction;
use App\Models\Mail;
use App\Models\User;
use App\Models\MailType;
use App\Models\MailStatus;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\UserOrganisation;
use Illuminate\Http\Request;
use App\Models\MailAttachment;
use App\Models\MailTransaction;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MailSendController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $transactions = MailTransaction::where('user_send_id', $user->id)->get();
        $transactions->load(['mail','action','organisationReceived','organisationSend']);
        return view('mails.send.index', compact('transactions'));
    }


// Pour les courriers envoyés (MailSend)
    public function create()
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Récupérer les mails qui ont des transactions où l'organisation courante est expéditrice
        $mails = Mail::with(['transactions', 'type'])
            ->whereHas('transactions', function ($query) use ($currentOrganisationId) {
                $query->where('organisation_send_id', $currentOrganisationId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Récupérer les utilisateurs sauf l'utilisateur courant
        $users = User::where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();

        // Récupérer toutes les organisations sauf l'organisation courante
        $organisations = Organisation::whereNot('id', $currentOrganisationId)
            ->orderBy('name')
            ->get();

        $documentTypes = DocumentType::orderBy('name')->get();
        $mailActions = MailAction::orderBy('name')->get();

        // Pour l'envoi, toutes les organisations sauf la courante peuvent être destinataires
        $sendOrganisations = Organisation::whereNot('id', $currentOrganisationId)
            ->orderBy('name')
            ->get();

        return view('mails.send.create', compact(
            'mails',
            'users',
            'organisations',
            'sendOrganisations',
            'documentTypes',
            'mailActions'
        ));
    }

    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'code' => 'required',
            'mail_id' => 'required|exists:mails,id',
            'user_received_id' => 'required|exists:users,id',
            'organisation_received_id' => 'required|exists:organisations,id',
            'document_type_id' => 'required|exists:document_types,id',
            'action_id' => 'required|exists:mail_actions,id',
            'description' => 'nullable',
        ]);

        $validatedData['organisation_send_id'] = auth()->user()->current_organisation_id;
        $validatedData['user_send_id'] =  auth()->id();

        $validatedData['date_creation'] = now();
        $validatedData['mail_type_id'] = 3; // 1 Recevoir, 2 Emettre 3 en cours (in progress)



        MailTransaction::create($validatedData);

        return redirect()->route('mail-send.index')
            ->with('success', 'MailTransaction created successfully.');
    }





    public function show(int $id)
    {
        $mailTransaction = MailTransaction::with([
            'mail',
            'mail.attachments',
            'documentType',
            'userReceived',
            'userSend',
            'organisationReceived',
            'organisationSend',
            'action',
            'mailType'
        ])->findOrFail($id);

        // Récupérer l'historique des transactions pour ce mail
        $mailHistory = MailTransaction::where('mail_id', $mailTransaction->mail_id)
            ->with(['userSend', 'userReceived', 'organisationSend', 'organisationReceived', 'action'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mails.send.show', compact('mailTransaction', 'mailHistory'));
    }



    public function edit(INT $id)
    {
        $mails = mail::all();
        $users = User::where('id', '!=', auth()->id())->get();

        $organisations = Organisation::whereNot('id', auth()->user()->currentOrganisation->id)->get();

        $sendOrganisations = Organisation::whereIn('id', UserOrganisation::where('user_id', auth()->id())
            ->where('active',true)
            ->pluck('organisation_id'))
            ->get();
        $documentTypes = documentType :: all();
        $mailActions = MailAction :: all();
        $mailTransaction = MailTransaction ::findOrFail($id);
        return view('mails.send.edit', compact('mails','users','mailTransaction','sendOrganisations','organisations','documentTypes','mailActions'));
    }



    public function update(Request $request, INT $id)
    {
        $mailTransaction = MailTransaction ::findOrFail($id);
        $validatedData = $request->validate([
            'code' => 'required',
            'mail_id' => 'required|exists:mails,id',
            'user_received_id' => 'required|exists:users,id',
            'organisation_received_id' => 'required|exists:organisations,id',
            'document_type_id' => 'required|exists:document_types,id',
            'action_id' => 'required|exists:mail_actions,id',
            'description' => 'nullable',
        ]);

        $validatedData['organisation_send_id'] = auth()->user()->organisation->id ;
        $validatedData['user_send_id'] =  auth()->id();

        $validatedData['date_creation'] = now();
        $validatedData['mail_type_id'] = 3; // 1 Recevoir, 2 Emettre 3 en cours (in progress)

        $mailTransaction->update($validatedData);

        return redirect()->route('mail-send.index')
            ->with('success', 'Mail Transaction updated successfully');
    }

    public function destroy($id)
    {
        $mailTransaction = MailTransaction::findOrFail($id);
        $mailTransaction->delete();
        return redirect()->route('mail-send.index')->with('success', 'MailTransaction deleted successfully');
    }
}

