<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\OpacResponseAttachment;
use App\Models\OpacResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpacResponseAttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(OpacResponse $response)
    {
        $attachments = $response->attachments()->paginate(10);
        return view('opac.response-attachments.index', compact('response', 'attachments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(OpacResponse $response)
    {
        return view('opac.response-attachments.create', compact('response'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'response_id' => 'required|exists:public_responses,id',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('opac/attachments');

        $attachment = PublicResponseAttachment::create([
            'response_id' => $validated['response_id'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Attachment uploaded successfully',
            'data' => $attachment
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicResponseAttachment $attachment)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Attachment details retrieved successfully',
            'data' => $attachment
        ], 200);
    }

    /**
     * Download the specified attachment.
     */
    public function download(OpacResponse $response, OpacResponseAttachment $attachment)
    {
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
        Storage::delete($attachment->file_path);
        $attachment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Attachment deleted successfully'
        ], 200);
    }

    /**
     * Preview the specified attachment.
     */
    public function preview(OpacResponse $response, OpacResponseAttachment $attachment)
    {
        if (!in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'])) {
            return redirect()->back()->with('error', 'Preview not available for this file type.');
        }

        return view('opac.response-attachments.preview', compact('response', 'attachment'));
    }
}
