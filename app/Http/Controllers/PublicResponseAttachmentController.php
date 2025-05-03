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
    public function index(PublicResponse $response)
    {
        $attachments = $response->attachments()->paginate(10);
        return view('public.response-attachments.index', compact('response', 'attachments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(PublicResponse $response)
    {
        return view('public.response-attachments.create', compact('response'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PublicResponse $response)
    {
        $validated = $request->validate([
            'attachments.*' => 'required|file|max:10240', // 10MB max per file
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/responses');
                $response->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('public.responses.show', $response)
            ->with('success', 'Attachments added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicResponse $response, PublicResponseAttachment $attachment)
    {
        return view('public.response-attachments.show', compact('response', 'attachment'));
    }

    /**
     * Download the specified attachment.
     */
    public function download(PublicResponse $response, PublicResponseAttachment $attachment)
    {
        return Storage::download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicResponse $response, PublicResponseAttachment $attachment)
    {
        Storage::delete($attachment->file_path);
        $attachment->delete();

        return redirect()->route('public.responses.show', $response)
            ->with('success', 'Attachment deleted successfully.');
    }

    /**
     * Preview the specified attachment.
     */
    public function preview(PublicResponse $response, PublicResponseAttachment $attachment)
    {
        if (!in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'])) {
            return redirect()->back()->with('error', 'Preview not available for this file type.');
        }

        return view('public.response-attachments.preview', compact('response', 'attachment'));
    }
}
