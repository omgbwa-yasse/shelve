<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Mail;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\Batch;
use App\Models\User;

class MailController extends Controller
{


    public function index()
    {
        $mails = Mail::with(['priority','typology','type','creator','updator'])->paginate(15);
        return view('mails.index', compact('mails'));
    }



    public function create()
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $types = MailType::all();
        $batches = Batch::all();
        return view('mails.create', compact('priorities','typologies','types','batches'));

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
            'code' => 'required|unique:mails|max:255',
            'name' => 'nullable|max:255',
            'author' => 'nullable|max:255',
            'description' => 'nullable',
            'date' => 'required|date',
            'mail_priority_id' => 'required|exists:mail_priorities,id',
            'mail_typology_id' => 'required|exists:mail_typologies,id',
            'mail_type_id' => 'required|exists:mail_types,id',
        ]);

            $mail = Mail::create($validatedData + [
                'create_by' => auth()->id(),
            ]);

            return redirect()->route('mails.index')->with('success', 'Mail créé avec succès !');
    }




    public function show(Mail $mail)
    {
        $mail->load('priority','typology','attachment','send', 'received',
                    'type','batch','creator','updator'); //'container'
        return view('mails.show', compact('mail'));
    }




    public function edit(Mail $mail)
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $types = MailType::all();
        $batches = Batch::all();
        $authors = user::all();
        return view('mails.edit', compact('mail','authors', 'priorities', 'typologies', 'types',  'batches'));
    }




    public function update(Request $request, Mail $mail)
    {
        $validatedData = $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'author' => 'nullable|max:255',
            'date' => 'required|date',
            'description' => 'nullable',
            'type_id' => 'required|exists:mail_types,id',
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

