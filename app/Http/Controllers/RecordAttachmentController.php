<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Models\Attachment;

use Illuminate\Http\Request;

class RecordAttachmentController extends Controller
{
    public function index(Record $record)
    {
        $attachments = $record->attachments;
        return view('records.attachments.index', compact('record', 'attachments'));
    }

    public function create(Record $record)
    {
        return view('records.attachments.create', compact('record'));
    }

    public function store(Request $request, Record $record)
    {
        $request->validate([
            'file_path' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $attachment = new Attachment($request->all());
        $record->attachments()->save($attachment);

        return redirect()->route('records.attachments.index', $record);
    }

    public function edit(Record $record, Attachment $attachment)
    {
        return view('records.attachments.edit', compact('record', 'attachment'));
    }

    public function update(Request $request, Record $record, Attachment $attachment)
    {
        $request->validate([
            'file_path' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $attachment->update($request->all());

        return redirect()->route('records.attachments.index', $record);
    }

    public function destroy(Record $record, Attachment $attachment)
    {
        $attachment->delete();

        return redirect()->route('records.attachments.index', $record);
    }
}


