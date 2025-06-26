<?php

namespace App\Http\Controllers;

use App\Models\PublicResponseAttachment;
use App\Models\PublicResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicResponseAttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attachments = PublicResponseAttachment::with(['response.user'])->paginate(10);
        return view('public.response-attachments.index', compact('attachments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $responses = PublicResponse::latest()->get();
        return view('public.response-attachments.create', compact('responses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'response_id' => 'required|exists:public_responses,id',
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('public/response-attachments');

            PublicResponseAttachment::create([
                'response_id' => $request->response_id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => basename($path),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'description' => $request->description,
            ]);
        }

        return redirect()->route('public.response-attachments.index')
            ->with('success', 'Pièce jointe ajoutée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicResponseAttachment $attachment)
    {
        return view('public.response-attachments.show', compact('attachment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicResponseAttachment $attachment)
    {
        $responses = PublicResponse::latest()->get();
        return view('public.response-attachments.edit', compact('attachment', 'responses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicResponseAttachment $attachment)
    {
        $request->validate([
            'response_id' => 'required|exists:public_responses,id',
            'file' => 'nullable|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:1000',
        ]);

        $data = [
            'response_id' => $request->response_id,
            'description' => $request->description,
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if ($attachment->file_path) {
                Storage::delete($attachment->file_path);
            }

            $file = $request->file('file');
            $path = $file->store('public/response-attachments');

            $data = array_merge($data, [
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => basename($path),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        $attachment->update($data);

        return redirect()->route('public.response-attachments.index')
            ->with('success', 'Pièce jointe modifiée avec succès.');
    }

    /**
     * Download the specified attachment.
     */
    public function download(PublicResponseAttachment $attachment)
    {
        if (!Storage::exists($attachment->file_path)) {
            return redirect()->back()->with('error', 'Fichier non trouvé.');
        }

        return Storage::download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicResponseAttachment $attachment)
    {
        if ($attachment->file_path) {
            Storage::delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->route('public.response-attachments.index')
            ->with('success', 'Pièce jointe supprimée avec succès.');
    }
}
