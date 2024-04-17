<?php

namespace App\Http\Controllers;

use App\MailAttachment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MailAttachmentController extends Controller
{

    public function index()
    {
        $mailAttachments = MailAttachment::get();
        return view('mail_attachments.index', compact('mailAttachments'));
    }


    public function create()
    {
        return view('mail_attachments.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->store('mail_attachments');

        $mailAttachment = MailAttachment::create([
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);

        return redirect()->route('mail_attachments.index')
                        ->with('success','Mail attachment created successfully');
    }


    public function show(MailAttachment $mailAttachment)
    {
        return view('mail_attachments.show', compact('mailAttachment'));
    }


    public function edit(MailAttachment $mailAttachment)
    {
        return view('mail_attachments.edit', compact('mailAttachment'));
    }


    public function update(Request $request, MailAttachment $mailAttachment)
    {
        $request->validate([
            'file' => 'file|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('mail_attachments');

            $mailAttachment->update([
                'path' => $path,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);
        }

        return redirect()->route('mail_attachments.index')
                        ->with('success','Mail attachment updated successfully');
    }


    public function destroy(MailAttachment $mailAttachment)
    {
        \Storage::delete($mailAttachment->path);
        $mailAttachment->delete();

        return redirect()->route('mail_attachments.index')
                        ->with('success','Mail attachment deleted successfully');
    }
}
