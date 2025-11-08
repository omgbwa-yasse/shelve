<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalFolder;
use App\Services\RecordDigitalDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    protected RecordDigitalDocumentService $documentService;

    public function __construct(RecordDigitalDocumentService $documentService)
    {
        $this->middleware('auth');
        $this->documentService = $documentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RecordDigitalDocument::with(['folder', 'type', 'creator'])
            ->withCount('versions');

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $documents = $query->latest()->paginate(20);

        return view('documents.index', compact('documents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $folderId = $request->get('folder_id');
        $folder = $folderId ? RecordDigitalFolder::find($folderId) : null;

        return view('documents.create', compact('folder'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'folder_id' => 'required|exists:record_digital_folders,id',
            'type_id' => 'required|exists:record_digital_document_types,id',
            'file' => 'required|file|max:10240',
        ]);

        try {
            $type = \App\Models\RecordDigitalDocumentType::findOrFail($validated['type_id']);
            $folder = RecordDigitalFolder::findOrFail($validated['folder_id']);
            $creator = Auth::user();
            $organisation = $folder->organisation;
            
            $document = $this->documentService->createDocument(
                $type,
                $folder,
                $validated,
                $creator,
                $organisation
            );            return redirect()
                ->route('documents.show', $document->id)
                ->with('success', 'Document created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating document: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $document = RecordDigitalDocument::with([
            'folder',
            'type',
            'creator',
            'versions',
            'approvals'
        ])->findOrFail($id);

        return view('documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $document = RecordDigitalDocument::findOrFail($id);

        return view('documents.edit', compact('document'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $document = RecordDigitalDocument::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $document->update($validated);
            
            return redirect()
                ->route('documents.show', $document->id)
                ->with('success', 'Document updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error updating document: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $document = RecordDigitalDocument::findOrFail($id);
            $folderId = $document->folder_id;
            
            $document->delete();
            
            return redirect()
                ->route('folders.show', $folderId)
                ->with('success', 'Document deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting document: ' . $e->getMessage());
        }
    }
    
    /**
     * Upload a new version of the document
     */
    public function upload(Request $request, string $id)
    {
        $document = RecordDigitalDocument::findOrFail($id);
        
        $validated = $request->validate([
            'file' => 'required|file|max:10240',
            'notes' => 'nullable|string',
        ]);

        try {
            // Handle file upload
            $file = $validated['file'];
            $path = $file->store('documents', 'public');
            
            // Create attachment record
            $attachment = new \App\Models\Attachment([
                'path' => $path,
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
            $attachment->save();
            
            $this->documentService->createVersion(
                $document,
                $attachment,
                Auth::user(),
                $validated['notes'] ?? null
            );
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    
    /**
     * Approve a document
     */
    public function approve(Request $request, string $id)
    {
        $document = RecordDigitalDocument::findOrFail($id);
        
        try {
            $this->documentService->approveDocument($document, Auth::user());
            
            return redirect()
                ->route('documents.show', $document->id)
                ->with('success', 'Document approved successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error approving document: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject a document
     */
    public function reject(Request $request, string $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);
        
        $document = RecordDigitalDocument::findOrFail($id);
        
        try {
            // Temporary implementation without rejection service method
            $document->update([
                'approval_status' => 'rejected',
                'rejection_reason' => $validated['reason']
            ]);
            
            return redirect()
                ->route('documents.show', $document->id)
                ->with('success', 'Document rejected!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error rejecting document: ' . $e->getMessage());
        }
    }
    
    /**
     * List document versions
     */
    public function versions(string $id)
    {
        $document = RecordDigitalDocument::with('versions')->findOrFail($id);
        
        return view('documents.versions', compact('document'));
    }
    
    /**
     * Download a specific version
     */
    public function downloadVersion(string $id, string $versionId)
    {
        $document = RecordDigitalDocument::findOrFail($id);
        $version = $document->versions()->findOrFail($versionId);
        
        return response()->download(storage_path('app/' . $version->file_path));
    }
}
