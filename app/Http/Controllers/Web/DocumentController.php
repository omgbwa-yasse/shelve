<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolder;
use App\Models\Organisation;
use App\Models\User;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RecordDigitalDocument::with([
            'type',
            'folder.type',
            'creator',
            'organisation',
            'assignedUser',
            'attachment'
        ])->currentVersions();

        // Filtres
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('signature_status')) {
            $query->where('signature_status', $request->signature_status);
        }

        if ($request->filled('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filtre des documents archivés
        if ($request->get('show_archived', false)) {
            $query->where('is_archived', true);
        } else {
            $query->where('is_archived', false);
        }

        $documents = $query->latest()->paginate(20);
        $types = RecordDigitalDocumentType::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();
        $folders = RecordDigitalFolder::with('type')->where('status', 'active')->orderBy('name')->get();

        return view('repositories.documents.index', compact('documents', 'types', 'organisations', 'folders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $types = RecordDigitalDocumentType::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $folders = RecordDigitalFolder::with('type')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $folderId = $request->get('folder_id');

        return view('repositories.documents.create', compact('types', 'organisations', 'users', 'folders', 'folderId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_id' => 'required|exists:record_digital_document_types,id',
            'folder_id' => 'nullable|exists:record_digital_folders,id',
            'organisation_id' => 'required|exists:organisations,id',
            'assigned_to' => 'nullable|exists:users,id',
            'access_level' => 'required|in:public,internal,confidential,secret',
            'status' => 'required|in:draft,active,archived,obsolete',
            'requires_approval' => 'boolean',
            'document_date' => 'nullable|date',
            'retention_until' => 'nullable|date',
            'metadata' => 'nullable|array',
            'file' => 'nullable|file|max:51200', // 50MB max
        ]);

        DB::beginTransaction();
        try {
            $type = RecordDigitalDocumentType::findOrFail($validated['type_id']);

            // Générer le code automatiquement
            $code = $type->generateCode();

            // Valider le fichier si fourni
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $validationErrors = (new RecordDigitalDocument(['type_id' => $validated['type_id']]))
                    ->validateFile($file);

                if (!empty($validationErrors)) {
                    return back()
                        ->withInput()
                        ->withErrors($validationErrors);
                }

                // Créer l'attachment et le stocker
                $attachment = Attachment::createFromUpload(
                    $file,
                    Attachment::TYPE_DIGITAL_DOCUMENT,
                    Auth::id(),
                    [
                        'description' => $validated['description'] ?? null,
                        'is_primary' => true,
                    ]
                );
                $validated['attachment_id'] = $attachment->id;
            }

            $document = RecordDigitalDocument::create(array_merge($validated, [
                'code' => $code,
                'creator_id' => Auth::id(),
                'version_number' => 1,
                'is_current_version' => true,
                'signature_status' => 'unsigned',
                'requires_approval' => $request->boolean('requires_approval', false),
            ]));

            // Mettre à jour les statistiques du dossier parent
            if ($document->folder) {
                $document->folder->updateStatistics();
            }

            DB::commit();

            return redirect()
                ->route('documents.show', $document)
                ->with('success', 'Document créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du document : ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RecordDigitalDocument $document)
    {
        $document->load([
            'type',
            'folder.type',
            'attachment',
            'creator',
            'organisation',
            'assignedUser',
            'checkedOutUser',
            'signer',
            'approver',
            'lastViewer'
        ]);

        // Récupérer toutes les versions du document
        $versions = $document->getAllVersions();

        // Tracker la consultation
        $document->trackView(Auth::user());

        return view('repositories.documents.show', compact('document', 'versions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RecordDigitalDocument $document)
    {
        // On ne peut éditer que la version courante
        if (!$document->is_current_version) {
            return redirect()
                ->route('documents.show', $document)
                ->with('error', 'Seule la version courante peut être éditée.');
        }

        // On ne peut pas éditer un document en cours de checkout
        if ($document->isCheckedOut() && !$document->isCheckedOutBy(Auth::user())) {
            return redirect()
                ->route('documents.show', $document)
                ->with('error', 'Ce document est actuellement réservé par un autre utilisateur.');
        }

        $types = RecordDigitalDocumentType::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $folders = RecordDigitalFolder::with('type')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('repositories.documents.edit', compact('document', 'types', 'organisations', 'users', 'folders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecordDigitalDocument $document)
    {
        if (!$document->is_current_version) {
            return back()->with('error', 'Seule la version courante peut être mise à jour.');
        }

        if ($document->isCheckedOut() && !$document->isCheckedOutBy(Auth::user())) {
            return back()->with('error', 'Document réservé par un autre utilisateur.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_id' => 'required|exists:record_digital_document_types,id',
            'folder_id' => 'nullable|exists:record_digital_folders,id',
            'organisation_id' => 'required|exists:organisations,id',
            'assigned_to' => 'nullable|exists:users,id',
            'access_level' => 'required|in:public,internal,confidential,secret',
            'status' => 'required|in:draft,active,archived,obsolete',
            'requires_approval' => 'boolean',
            'document_date' => 'nullable|date',
            'retention_until' => 'nullable|date',
            'metadata' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $oldFolderId = $document->folder_id;

            $document->update(array_merge($validated, [
                'requires_approval' => $request->boolean('requires_approval', false),
            ]));

            // Mettre à jour les statistiques si le dossier a changé
            if ($oldFolderId !== $document->folder_id) {
                if ($oldFolderId) {
                    $oldFolder = RecordDigitalFolder::find($oldFolderId);
                    $oldFolder?->updateStatistics();
                }
                if ($document->folder) {
                    $document->folder->updateStatistics();
                }
            }

            DB::commit();

            return redirect()
                ->route('documents.show', $document)
                ->with('success', 'Document mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecordDigitalDocument $document)
    {
        // On ne peut supprimer que des documents brouillons ou la version courante
        if (!$document->is_current_version) {
            return back()->with('error', 'Seule la version courante peut être supprimée.');
        }

        if ($document->isCheckedOut()) {
            return back()->with('error', 'Impossible de supprimer un document réservé.');
        }

        if ($document->signature_status === 'signed') {
            return back()->with('error', 'Impossible de supprimer un document signé.');
        }

        DB::beginTransaction();
        try {
            $folderId = $document->folder_id;

            // Supprimer toutes les versions du document
            $rootId = $document->parent_version_id ?? $document->id;
            RecordDigitalDocument::where('id', $rootId)
                ->orWhere('parent_version_id', $rootId)
                ->delete();

            // Mettre à jour les statistiques du dossier
            if ($folderId) {
                $folder = RecordDigitalFolder::find($folderId);
                $folder?->updateStatistics();
            }

            DB::commit();

            return redirect()
                ->route('documents.index')
                ->with('success', 'Document et toutes ses versions supprimés avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Upload a new file version for the document
     */
    public function upload(Request $request, RecordDigitalDocument $document)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB
            'version_notes' => 'nullable|string|max:1000',
        ]);

        if (!$document->is_current_version) {
            return back()->with('error', 'Seule la version courante peut recevoir une nouvelle version.');
        }

        if ($document->isCheckedOut() && !$document->isCheckedOutBy(Auth::user())) {
            return back()->with('error', 'Document réservé par un autre utilisateur.');
        }

        DB::beginTransaction();
        try {
            $file = $request->file('file');

            // Valider le fichier
            $validationErrors = $document->validateFile($file);
            if (!empty($validationErrors)) {
                return back()->withErrors($validationErrors);
            }

            // Créer la nouvelle version
            $newVersion = $document->createNewVersion(
                Auth::user(),
                $file,
                $request->input('version_notes')
            );

            // Mettre à jour les statistiques du dossier
            if ($document->folder) {
                $document->folder->updateStatistics();
            }

            DB::commit();

            return redirect()
                ->route('documents.show', $newVersion)
                ->with('success', "Nouvelle version créée avec succès (v{$newVersion->version_number}).");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création de la version : ' . $e->getMessage());
        }
    }

    /**
     * Approve the document
     */
    public function approve(Request $request, RecordDigitalDocument $document)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        if (!$document->requires_approval) {
            return back()->with('error', 'Ce document ne nécessite pas d\'approbation.');
        }

        if ($document->approved_at) {
            return back()->with('error', 'Ce document a déjà été approuvé.');
        }

        try {
            $document->approve(Auth::user(), $request->input('approval_notes'));

            return redirect()
                ->route('documents.show', $document)
                ->with('success', 'Document approuvé avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'approbation : ' . $e->getMessage());
        }
    }

    /**
     * Reject the document
     */
    public function reject(Request $request, RecordDigitalDocument $document)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if (!$document->requires_approval) {
            return back()->with('error', 'Ce document ne nécessite pas d\'approbation.');
        }

        try {
            $document->rejectSignature(Auth::user(), $request->input('rejection_reason'));

            return redirect()
                ->route('documents.show', $document)
                ->with('success', 'Document rejeté.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du rejet : ' . $e->getMessage());
        }
    }

    /**
     * Get all versions of the document
     */
    public function versions(RecordDigitalDocument $document)
    {
        $currentDocument = $document->is_current_version
            ? $document
            : $document->getCurrentVersion();

        $allVersions = $document->getAllVersions();
        $allVersions->load(['creator', 'signer', 'approver', 'type', 'folder', 'attachment']);

        return view('repositories.documents.versions', compact('currentDocument', 'allVersions'));
    }

    /**
     * Download a specific version of the document
     */
    public function downloadVersion(RecordDigitalDocument $document, $version)
    {
        $versionDocument = RecordDigitalDocument::where(function ($q) use ($document) {
            $rootId = $document->parent_version_id ?? $document->id;
            $q->where('id', $rootId)->orWhere('parent_version_id', $rootId);
        })
        ->where('version_number', $version)
        ->firstOrFail();

        if (!$versionDocument->attachment) {
            return back()->with('error', 'Aucun fichier attaché à cette version.');
        }

        // Tracker le téléchargement sur la version courante
        if ($document->is_current_version) {
            $document->trackView(Auth::user());
        }

        // Télécharger le fichier
        return $versionDocument->attachment->download();
    }
}
