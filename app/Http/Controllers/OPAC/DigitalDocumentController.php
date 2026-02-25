<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DigitalDocumentController extends Controller
{
    public function show($id)
    {
        $document = RecordDigitalDocument::where('status', 'active')
            ->where('access_level', 'public')
            ->findOrFail($id);

        return view('opac.digital.documents.show', compact('document'));
    }

    public function download($id)
    {
        $document = RecordDigitalDocument::where('status', 'active')
            ->where('access_level', 'public')
            ->findOrFail($id);

        if (!Storage::exists($document->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::download($document->file_path, $document->name . '.' . $document->extension);
    }
}
