<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\WorkplaceFolder;
use App\Models\WorkplaceDocument;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkplaceContentController extends Controller
{
    public function folders(Workplace $workplace)
    {
        $folders = $workplace->folders()
            ->with(['folder', 'sharedBy'])
            ->latest('shared_at')
            ->get();

        return view('workplaces.content.folders', compact('workplace', 'folders'));
    }

    public function documents(Workplace $workplace)
    {
        $documents = $workplace->documents()
            ->with(['document', 'sharedBy'])
            ->latest('shared_at')
            ->get();

        return view('workplaces.content.documents', compact('workplace', 'documents'));
    }

    public function shareFolder(Request $request, Workplace $workplace)
    {
        $validated = $request->validate([
            'folder_id' => 'required|exists:record_digital_folders,id',
            'access_level' => 'required|in:view,edit,full',
            'share_note' => 'nullable|string',
            'is_pinned' => 'boolean',
        ]);

        // Check if already shared
        if ($workplace->folders()->where('folder_id', $validated['folder_id'])->exists()) {
            return back()->withErrors(['error' => 'Ce dossier est déjà partagé dans ce workspace']);
        }

        $workplace->folders()->create([
            ...$validated,
            'shared_by' => Auth::id(),
            'shared_at' => now(),
        ]);

        // Log activity
        $workplace->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'shared_folder',
            'subject_type' => RecordDigitalFolder::class,
            'subject_id' => $validated['folder_id'],
            'description' => 'Dossier partagé',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Dossier partagé avec succès');
    }

    public function shareDocument(Request $request, Workplace $workplace)
    {
        $validated = $request->validate([
            'document_id' => 'required|exists:record_digital_documents,id',
            'access_level' => 'required|in:view,edit,full',
            'share_note' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        // Check if already shared
        if ($workplace->documents()->where('document_id', $validated['document_id'])->exists()) {
            return back()->withErrors(['error' => 'Ce document est déjà partagé dans ce workspace']);
        }

        $workplace->documents()->create([
            ...$validated,
            'shared_by' => Auth::id(),
            'shared_at' => now(),
        ]);

        // Log activity
        $workplace->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'shared_document',
            'subject_type' => RecordDigitalDocument::class,
            'subject_id' => $validated['document_id'],
            'description' => 'Document partagé',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Document partagé avec succès');
    }

    public function unshareFolder(Workplace $workplace, WorkplaceFolder $folder)
    {
        $folder->delete();

        $workplace->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'deleted_folder',
            'description' => 'Partage de dossier supprimé',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Dossier retiré du workspace');
    }

    public function unshareDocument(Workplace $workplace, WorkplaceDocument $document)
    {
        $document->delete();

        $workplace->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'deleted_document',
            'description' => 'Partage de document supprimé',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Document retiré du workspace');
    }

    public function pinFolder(Workplace $workplace, WorkplaceFolder $folder)
    {
        $folder->update(['is_pinned' => !$folder->is_pinned]);

        $message = $folder->is_pinned ? 'Dossier épinglé' : 'Dossier désépinglé';
        return back()->with('success', $message);
    }

    public function featureDocument(Workplace $workplace, WorkplaceDocument $document)
    {
        $document->update(['is_featured' => !$document->is_featured]);

        $message = $document->is_featured ? 'Document mis en avant' : 'Document retiré des mises en avant';
        return back()->with('success', $message);
    }

    public function viewDocument(Workplace $workplace, WorkplaceDocument $document)
    {
        $document->incrementViews();

        // Redirect to actual document view
        return redirect()->route('documents.show', $document->document_id);
    }
}
