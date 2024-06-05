<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Mail;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\MailSubject;
use App\Models\MailBatch;
use App\Models\User;

class MailController extends Controller
{
    public function index()
    {
        $mails = Mail::all();
        return view('mails.index', compact('mails'));
    }

    public function create()
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $types = MailType::all();
        $subjects = MailSubject::all();
        $batches = MailBatch::all();
        $authors = user::all();
        return view('mails.create', compact('priorities','authors', 'typologies', 'types', 'subjects', 'batches'));
    }


    public function searchAuthors(Request $request)
    {
        $search = $request->input('search');
        $authors = User::where('name', 'LIKE', "%$search%")->get();
        return response()->json($authors);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|max:255',
            'object' => 'required|max:255',
            'date' => 'required|date',
            'description' => 'nullable',
            'subject_id' => 'required|exists:mail_subjects,id',
            'type_id' => 'required|exists:mail_types,id',
            'authors' => 'required',
            'document_id' => 'nullable',
            'mail_priority_id' => 'required|exists:mail_priorities,id',
            'mail_typology_id' => 'required|exists:mail_typologies,id',
        ]);

        Mail::create($validatedData);

        return redirect()->route('mails.index')->with('success', 'Mail created successfully.');
    }

    public function show(Mail $mail)
    {
        return view('mails.show', compact('mail'));
    }

    public function edit(Mail $mail)
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $types = MailType::all();
        $subjects = MailSubject::all();
        $batches = MailBatch::all();
        $authors = user::all();
        return view('mails.edit', compact('mail','authors', 'priorities', 'typologies', 'types', 'subjects', 'batches'));
    }

    public function update(Request $request, Mail $mail)
    {
        $validatedData = $request->validate([
            'code' => 'required|max:255',
            'object' => 'required|max:255',
            'date' => 'required|date',
            'description' => 'nullable',
            'subject_id' => 'required|exists:mail_subjects,id',
            'type_id' => 'required|exists:mail_types,id',
            'authors' => 'required',
            'document_id' => 'nullable',
            'mail_priority_id' => 'required|exists:mail_priorities,id',
            'mail_typology_id' => 'required|exists:mail_typologies,id',
        ]);

        $mail->update($validatedData);

        return redirect()->route('mails.index')->with('success', 'Mail updated successfully.');
    }

    public function destroy(Mail $mail)
    {
        $mail->delete();

        return redirect()->route('mails.index')->with('success', 'Mail deleted successfully.');
    }
}

