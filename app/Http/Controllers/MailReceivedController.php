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
                ->where('status', '!=', ['draft','reject'])
                ->OrWhereHas('containers', function($q) {
                    $q->where('creator_organisation_id', Auth::user()->current_organisation_id);
                })
                ->get();
        $dollies = Dolly::all();
        $categories = Dolly::categories();
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
        $categories = Dolly::categories();
        $users = User::all();
        return view('mails.received.index', compact('mails','dollies', 'categories','users'));
    }




    public function approve(Mail $mail)
    {

        $mail->update([
            'recipient_user_id' => Auth::id(),
            'status' => 'transmitted',
        ]);

        return redirect()->route('mail-received.index')
                         ->with('success', 'Mail approved successfully.');
    }


    public function reject(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:mails,id',
        ]);

        $mail = Mail::findOrFail($validatedData['id']);

        $mail->update([
            'recipient_user_id' => auth::user()->id,
            'status' => 'reject',

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


        if (!isset($validatedData['code']) || empty($validatedData['code'])) {
            $validatedData['code'] = $this->generateMailCode($validatedData['mail_typology_id']);
        } else {
            $existingMail = Mail::where('code', $validatedData['code'])->first();
            if (!$existingMail) {
                return back()->withErrors(['code' => 'Aucun mail trouvé avec ce code.'])->withInput();
            }
        }


        Mail::create($validatedData + [
            'recipient_organisation_id' => auth()->user()->current_organisation_id,
            'recipient_user_id' => auth()->id(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('mail-received.index')
                         ->with('success', 'Mail created successfully.');
    }



    public function generateMailCode(int $typologie_id)
    {
        $typology = MailTypology::findOrFail($typologie_id);
        $year = date('Y');

        $count = Mail::whereYear('created_at', $year)
                ->where('mail_typology_id', $typologie_id)
                ->count();

        $nextNumber = $count + 1;
        $codeExists = true;

        while ($codeExists) {
            $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $candidateCode = $year . "/" . $typology->code . "/" . $formattedNumber;
            $codeExists = Mail::where('code', $candidateCode)->exists();
            if ($codeExists) {
                $nextNumber++;
            }
        }

        return $candidateCode;
    }



    public function show(INT $mail_id)
    {
        $mail=Mail::findOrFail($mail_id)->load([
                            'action',
                            'sender',
                            'senderOrganisation',
                            'recipient',
                            'recipientOrganisation',
                            'attachments'
                        ]);
        return view('mails.received.show', compact('mail'));
    }



    public function edit(Mail $received)
    {
        $received->load([
            'action',
            'sender',
            'senderOrganisation',
            'recipient',
            'recipientOrganisation',
            'attachments'
        ]);

        $mailActions = MailAction::all();
        $senderOrganisations = Organisation::whereNot('id', auth()->user()->current_organisation_id)->get();

        return view('mails.received.edit', compact('received', 'mailActions', 'senderOrganisations'));
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
