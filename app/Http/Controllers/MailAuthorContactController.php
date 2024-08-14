<?php

namespace App\Http\Controllers;
use App\Models\AuthorContact;
use App\Models\Author;
use Illuminate\Http\Request;

class MailAuthorContactController extends Controller
{
    public function index(Author $author)
    {
        $authorContacts = AuthorContact::where('author_id','=',$author->id)->get();
        return view('mails.authors.contacts.index', compact('authorContacts', 'author'));
    }



    public function create(Author $author)
    {
        return view('mails.authors.contacts.create', compact('author'));
    }



    public function store(Request $request, Author $author)
    {
        AuthorContact::create($request->all());
        return redirect()->route('author-contact.index', compact('author'));
    }



    public function show(Author $author, AuthorContact $authorContact)
    {
        return view('mails.authors.contacts.show', compact('authorContact','author'));
    }



    public function edit(Author $author, AuthorContact $authorContact)
    {
        return view('mails.authors.contacts.edit', compact('authorContact'));
    }


    public function update(Request $request, AuthorContact $authorContact)
    {
        $authorContact->update($request->all());
        return redirect()->route('author-contact.index');
    }


    public function destroy(AuthorContact $authorContact)
    {
        $authorContact->delete();
        return redirect()->route('author-contact.index');
    }

}
