<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Author;
use Illuminate\Http\Request;

class MailController extends Controller
{
    public function index()
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $authors = Author::all();
        $mails = Mail::with(['priority', 'authors', 'typology', 'sender', 'recipient']) // Corrected relations
                     ->paginate(15);

        return view('mails.index', compact('mails', 'priorities', 'typologies', 'authors'));
    }

    public function archived()
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $authors = Author::all();
        $mails = Mail::with(['priority', 'authors', 'typology', 'sender', 'recipient'])
                     ->where('is_archived', true)
                     ->paginate(15);

        return view('mails.index', compact('mails', 'priorities', 'typologies', 'authors'));
    }

    public function create()
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $authors = Author::all();

        return view('mails.create', compact('priorities', 'typologies', 'authors'));
    }

    public function store(Request $request)
    {
        $mailCode = $this->generateMailCode();

        $validatedData = $request->validate([
            'name' => 'required|max:150',
            'date' => 'required|date',
            'description' => 'nullable',
            'document_type' => 'required|in:original,duplicate,copy', // Validate document_type
            'priority_id' => 'required|exists:mail_priorities,id',
            'typology_id' => 'required|exists:mail_typologies,id',
            'action_id' => 'required|exists:mail_actions,id',
            'sender_user_id' => 'required|exists:users,id',
            'sender_organisation_id' => 'required|exists:organisations,id',
            'recipient_user_id' => 'nullable|exists:users,id',
            'recipient_organisation_id' => 'nullable|exists:organisations,id',
        ]);

        $mail = Mail::create($validatedData + [
            'code' => $mailCode,
            'status' => 'draft', // Set initial status
        ]);

        // Assuming you want to attach authors to the mail:
        $authorIds = $request->input('author_ids', []); // Get author IDs from the request
        $mail->authors()->sync($authorIds);

        return redirect()->route('mails.index')->with('success', 'Mail créé avec succès !');
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
        $mail = Mail::with(['priority', 'typology', 'action', 'sender', 'senderOrganisation',
                            'recipient', 'recipientOrganisation', 'authors', 'attachments']) // Corrected relations
                    ->findOrFail($id);

        return view('mails.show', compact('mail'));
    }

    public function edit(int $id)
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $authors = Author::all();
        $mail = Mail::with(['priority', 'typology', 'action', 'sender', 'senderOrganisation',
                            'recipient', 'recipientOrganisation', 'authors', 'attachments'])
                    ->findOrFail($id);

        return view('mails.edit', compact('mail', 'authors', 'priorities', 'typologies'));
    }

    public function update(Request $request, Mail $mail)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:150',
            'date' => 'required|date',
            'description' => 'nullable',
            'document_type' => 'required|in:original,duplicate,copy',
            'status' => 'required|in:draft,in_progress,transmitted,reject',
            'priority_id' => 'required|exists:mail_priorities,id',
            'typology_id' => 'required|exists:mail_typologies,id',
            'action_id' => 'required|exists:mail_actions,id',
            'sender_user_id' => 'required|exists:users,id',
            'sender_organisation_id' => 'required|exists:organisations,id',
            'recipient_user_id' => 'nullable|exists:users,id',
            'recipient_organisation_id' => 'nullable|exists:organisations,id',
        ]);

        $mail->update($validatedData);

        $authorIds = $request->input('author_ids', []);
        $mail->authors()->sync($authorIds);

        return redirect()->route('mails.index')->with('success', 'Mail updated successfully.');
    }

    public function destroy(int $id)
    {
        $mail = Mail::findOrFail($id);
        $mail->delete();

        return redirect()->route('mails.index')->with('success', 'Mail deleted successfully.');
    }
}
