<?php

namespace App\Http\Controllers;

use App\MailSubject;
use Illuminate\Http\Request;

class MailSubjectController extends Controller
{


    public function index()
    {
        $mailSubjects = MailSubject::all();
        return view('mails.subject.index', compact('mailSubjects'));
    }



    public function create()
    {
        return view('mails.subject.create');
    }




    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
        ]);

        MailSubject::create($request->all());

        return redirect()->route('mail_subjects.index')
            ->with('success', 'Mail subject created successfully.');
    }




    public function show(MailSubject $mailSubject)
    {
        return view('mails.subject.show', compact('mailSubject'));
    }



    public function edit(MailSubject $mailSubject)
    {
        return view('mails.subject.edit', compact('mailSubject'));
    }



    public function update(Request $request, MailSubject $mailSubject)
    {
        $request->validate([
            'name' => 'required|max:100',
        ]);

        $mailSubject->update($request->all());

        return redirect()->route('mail_subjects.index')
            ->with('success', 'Mail subject updated successfully.');
    }


    public function destroy(MailSubject $mailSubject)
    {
        $mailSubject->delete();

        return redirect()->route('mail_subjects.index')
            ->with('success', 'Mail subject deleted successfully.');
    }
}
