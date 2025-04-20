<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\user;
use App\Models\MailAction;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Dolly;
use App\Models\DollyType;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MailReceivedController extends Controller
{
    public function index()
    {
        $organisationId = Auth::user()->current_organisation_id;
        $mails = Mail::with(['action', 'sender', 'senderOrganisation'])
                     ->where('recipient_organisation_id', $organisationId)
                     ->where('status', '!=', 'draft')
                     ->get();
        $dollies = Dolly::all();
        $categories = Dolly::pluck('category');
        $users = User::all();

        return view('mails.received.index', compact('mails','dollies', 'categories','users'));
    }




    public function inprogress()
    {
        $userId = Auth::id();
        $mails = Mail::with(['action', 'sender', 'senderOrganisation'])
                     ->where('recipient_user_id', $userId)
                     ->where('status', 'in_progress')
                     ->get();
        $dollies = Dolly::all();
        $types = DollyType::all();
        $users = User::all();
        return view('mails.received.index', compact('mails','dollies', 'types','users'));
    }




    public function approve(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:mails,id',
        ]);

        $mail = Mail::findOrFail($validatedData['id']);

        $mail->update([
            'recipient_user_id' => auth()->id(),
            'status' => 'received',
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
        return view('mails.received.create', compact('mailActions', 'senderOrganisations','users', 'priorities','typologies' ));
    }



    public function store(Request $request)
    {
        $mailCode = $this->generateMailCode();

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
        ]);

        Mail::create($validatedData + [
            'code' => $mailCode,
            'recipient_organisation_id' => auth()->user()->current_organisation_id,
            'recipient_user_id' => auth()->id(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('mail-received.index')
                         ->with('success', 'Mail created successfully.');
    }



    public function generateMailCode()
    {
        $year = date('Y');
        $lastMailCode = Mail::whereYear('created_at', $year)
                            ->latest('created_at')
                            ->value('code');

        if ($lastMailCode) {
            $lastCodeParts = explode('-', $lastMailCode);
            $lastOrderNumber = isset($lastCodeParts[1]) ? (int) substr($lastCodeParts[1], 1) : 0;
            $mailCount = $lastOrderNumber + 1;
        } else {
            $mailCount = 1;
        }

        $formattedMailCount = str_pad($mailCount, 6, '0', STR_PAD_LEFT);
        return 'M' . $year . '-' . $formattedMailCount;
    }



    public function show(int $id)
    {
        $mail = Mail::with([
                            'action',
                            'sender',
                            'senderOrganisation',
                            'recipient',
                            'recipientOrganisation',
                            'authors',
                            'attachments'
                        ])
                    ->findOrFail($id);

        return view('mails.received.show', compact('mail'));
    }



    public function edit(int $id)
    {
        $mail = Mail::with([
                            'action',
                            'sender',
                            'senderOrganisation',
                            'priority',
                            'typology',
                            'authors',
                            'attachments'
                        ])
                    ->findOrFail($id);
        $mailActions = MailAction::all();
        $senderOrganisations = Organisation::whereNot('id', auth()->user()->current_organisation_id)->get();

        return view('mails.received.edit', compact('mail', 'mailActions', 'senderOrganisations'));
    }



    public function update(Request $request, int $id)
    {
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
        ]);

        $mail->update($validatedData);

        return redirect()->route('mail-received.index')
                         ->with('success', 'Mail updated successfully');
    }



    public function destroy($id)
    {
        $mail = Mail::findOrFail($id);
        $mail->delete();

        return redirect()->route('mail-received.index')->with('success', 'Mail deleted successfully');
    }
}
