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

class MailController extends Controller
{


    public function search(Request $request)
    {
        $query = Mail::query();

        if ($request->filled('code')) {
            $query->where('code', 'LIKE', '%' . $request->code . '%');
        }

        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->filled('author')) {
            $query->whereHas('authors', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->author . '%');
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('mail_priority_id')) {
            $query->where('mail_priority_id', $request->mail_priority_id);
        }

        if ($request->filled('mail_type_id')) {
            $query->where('mail_type_id', $request->mail_type_id);
        }

        if ($request->filled('mail_typology_id')) {
            $query->where('mail_typology_id', $request->mail_typology_id);
        }

        if ($request->filled('author_ids')) {
            $authorIds = explode(',', $request->author_ids);
            $query->whereHas('authors', function ($q) use ($authorIds) {
                $q->whereIn('id', $authorIds);
            });
        }

        $mails = $query->with(['priority', 'authors', 'typology', 'type', 'creator', 'updator', 'lastTransaction'])
            ->paginate(15);

        $priorities = MailPriority::all();
        $types = MailType::all();
        $typologies = MailTypology::all();
        $authors = Author::all();

        return view('mails.index', compact('mails', 'priorities', 'types', 'typologies', 'authors'));
    }

    public function index()
    {
        $priorities = MailPriority::all();
        $types = MailType::all();
        $typologies = MailTypology::all();
        $authors = Author::all();
        $mails = Mail::with(['priority','authors','typology','type','creator','updator','lastTransaction'])->paginate(15);
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
            ]);

            $mail->authors()->attach($validatedData['author_id']);

            return redirect()->route('mails.index')->with('success', 'Mail créé avec succès !');
    }




    public function show(INT $id)
    {
        $mail=mail::with('priority','typology','attachment','send','transactions', 'received','type','batch','authors','updator','documentType')->findOrFail($id);
        return view('mails.show', compact('mail'));
    }




    public function edit(INT $id)
    {
        $priorities = MailPriority::all();
        $typologies = MailTypology::all();
        $types = MailType::all();
        $batches = Batch::all();
        $authors = user::all();
        $mail=mail::with('priority','typology','attachment','send','transactions', 'received','type','batch','authors','updator','documentType')->findOrFail($id);
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

