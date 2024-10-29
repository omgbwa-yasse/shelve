<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Mail;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\Batch;
use App\Models\documentType;
use App\Models\Author;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MailController extends Controller
{
    public function index()
    {
        $priorities = MailPriority::all();
        $types = MailType::all();
        $typologies = MailTypology::all();
        $authors = Author::all();
        $mails = Mail::with(['priority','container','authors','typology','type','creator','updator','lastTransaction'])
            ->paginate(15);
        return view('mails.index', compact('mails', 'priorities', 'types', 'typologies', 'authors'));

    }


    public function typologies()
    {
        $priorities = MailPriority::all();
        $types = MailType::all();
        $typologies = MailTypology::all();
        $authors = Author::all();
        $mails = Mail::with(['priority','container','authors','typology','type','creator','updator','lastTransaction'])
            ->where('is_archived', true)
            ->paginate(15);
        return view('mails.index', compact('mails', 'priorities', 'types', 'typologies', 'authors'));
    }



    public function archived()
    {
        $priorities = MailPriority::all();
        $types = MailType::all();
        $typologies = MailTypology::all();
        $authors = Author::all();
        $mails = Mail::with(['priority','container','authors','typology','type','creator','updator','lastTransaction'])
            ->paginate(15);
        return view('mails.index', compact('mails', 'priorities', 'types', 'typologies', 'authors'));
    }





    public function create()
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $types = MailType::all();
        $batches = Batch::all();
        $authors = Author::all();
        $documentTypes = documentType ::all();
        return view('mails.create', compact('priorities','typologies','types','batches','authors','documentTypes'));

    }



    public function searchAuthors(Request $request)
    {
        $search = $request->input('search');
        $authors = User::where('name', 'LIKE', "%$search%")->get();
        return response()->json($authors);
    }




    public function store(Request $request)
    {
        $mailCode = $this->getMailCode();

        $request->merge(['code' => $mailCode]);

        $validatedData = $request->validate([
            'code' => 'required|unique:mails|max:255',
            'name' => 'nullable|max:255',
            'author' => 'nullable|max:255',
            'description' => 'nullable',
            'address' => 'nullable',
            'date' => 'required|date',
            'author_id' => 'required|exists:authors,id',
            'mail_priority_id' => 'required|exists:mail_priorities,id',
            'mail_typology_id' => 'required|exists:mail_typologies,id',
            'mail_type_id' => 'required|exists:mail_types,id',
            'document_type_id' => 'required|exists:document_types,id',
        ]);

        $mail = Mail::create($validatedData + [
            'create_by' => auth()->id(),
            'creator_organisation_id'=>Auth::user()->current_organisation_id
        ]);

        $mail->authors()->attach($validatedData['author_id']);

        return redirect()->route('mails.index')->with('success', 'Mail créé avec succès !');
    }


    public function getMailCode(){
        $year = date('Y');
        $month = date('m');

        $mailCount = Mail::whereYear('created_at', $year)->count();
        $mailCount++;

         if($mailCount > 999999){
            $formattedMailCount = str_pad($mailCount, 8, '0', STR_PAD_LEFT);
         }else{
            $formattedMailCount = str_pad($mailCount, 6, '0', STR_PAD_LEFT);
         }

        return $year . '-' . $month . '-' . $formattedMailCount;
    }



    public function show(INT $id)
    {
        $mail=mail::with('priority','typology','send','transactions', 'received','type','batch','authors','updator','documentType')->findOrFail($id);
        return view('mails.show', compact('mail'));
    }




    public function edit(INT $id)
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $types = MailType::all();
        $batches = Batch::all();
        $authors = user::all();
        $mail=mail::with('priority','typology','send','transactions', 'received','type','batch','authors','updator','documentType')->findOrFail($id);
        return view('mails.edit', compact('mail','authors', 'priorities', 'typologies', 'types',  'batches'));
    }




    public function update(Request $request, Mail $mail)
    {
        $validatedData = $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'address' => 'nullable',
            'date' => 'required|date',
            'description' => 'nullable',
            'author_id' => 'required|exists:authors,id',
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

