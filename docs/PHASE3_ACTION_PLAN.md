# Plan d'Action Phase 3 - Multi-Type Records

**Date de création**: 8 Novembre 2025  
**Statut actuel**: ALPHA (56% complet)  
**Objectif**: Production-Ready (95%+)  
**Durée estimée**: 4-6 semaines

---

## 🎯 Objectifs Prioritaires

### 1. Rendre le système utilisable via UI (🔴 CRITIQUE)
- Créer toutes les vues manquantes pour Folders et Documents
- Exposer les fonctionnalités avancées (checkout, signature)
- Corriger l'index mixte pour la performance

### 2. Assurer la qualité et la stabilité (🟠 IMPORTANT)
- Ajouter tests automatisés (coverage 70%+)
- Compléter les permissions workflow
- Documenter l'API

### 3. Optimiser et finaliser (🟡 MOYEN)
- Améliorer les performances
- Enrichir l'UI/UX
- Ajouter monitoring et logs audit

---

## 📅 Phase 1: Corrections Critiques (Semaines 1-2)

### Semaine 1: Vues et Routes Essentielles

#### Tâche 1.1: Créer les vues Digital Folders
**Priorité**: 🔴 CRITIQUE  
**Durée**: 2 jours  
**Assigné à**: Frontend Developer

**Fichiers à créer**:
```
resources/views/repositories/folders/
├── index.blade.php      - Liste avec filtres + pagination
├── create.blade.php     - Formulaire création
├── edit.blade.php       - Formulaire édition
├── show.blade.php       - Détail avec breadcrumb + enfants
└── partials/
    ├── form.blade.php       - Formulaire réutilisable
    ├── tree.blade.php       - Arbre hiérarchique (vue-treeselect)
    ├── stats.blade.php      - Statistiques (documents/subfolders/size)
    └── breadcrumb.blade.php - Fil d'Ariane
```

**Checklist**:
- [ ] `index.blade.php`: Liste paginée avec filtres (type, status, organisation, parent)
- [ ] `index.blade.php`: Boutons actions (create, edit, delete, move)
- [ ] `create.blade.php`: Formulaire avec sélection parent (vue arbre)
- [ ] `create.blade.php`: Auto-génération code visible
- [ ] `edit.blade.php`: Formulaire pré-rempli
- [ ] `show.blade.php`: Affichage métadonnées complètes
- [ ] `show.blade.php`: Breadcrumb hiérarchique cliquable
- [ ] `show.blade.php`: Liste sous-dossiers et documents
- [ ] `show.blade.php`: Statistiques (counts + size)
- [ ] `partials/tree.blade.php`: Composant arbre réutilisable
- [ ] Tester toutes les vues avec données seedées

**Code de référence**:
```blade
{{-- resources/views/repositories/folders/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('Digital Folders') }}</h1>
        @can('create', App\Models\RecordDigitalFolder::class)
            <a href="{{ route('folders.create') }}" class="btn btn-primary">
                <i class="bi bi-folder-plus"></i> {{ __('New Folder') }}
            </a>
        @endcan
    </div>

    {{-- Filtres --}}
    <form method="GET" class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label>{{ __('Type') }}</label>
                    <select name="type_id" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>{{ __('Search') }}</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Code, name, description...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">{{ __('Filter') }}</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Liste --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Stats') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($folders as $folder)
                        <tr>
                            <td>
                                <code>{{ $folder->code }}</code>
                            </td>
                            <td>
                                <a href="{{ route('folders.show', $folder) }}">
                                    @if($folder->parent)
                                        <i class="bi bi-folder text-muted"></i>
                                    @else
                                        <i class="bi bi-folder-fill text-primary"></i>
                                    @endif
                                    {{ $folder->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $folder->type->name }}</span>
                            </td>
                            <td>
                                @if($folder->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($folder->status === 'archived')
                                    <span class="badge bg-secondary">Archived</span>
                                @else
                                    <span class="badge bg-dark">Closed</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-folder"></i> {{ $folder->subfolders_count }}
                                    <i class="bi bi-file-earmark ms-2"></i> {{ $folder->documents_count }}
                                    <i class="bi bi-hdd ms-2"></i> {{ \App\Helpers\FileHelper::formatBytes($folder->total_size) }}
                                </small>
                            </td>
                            <td>{{ $folder->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('view', $folder)
                                        <a href="{{ route('folders.show', $folder) }}" class="btn btn-outline-primary" title="{{ __('View') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $folder)
                                        <a href="{{ route('folders.edit', $folder) }}" class="btn btn-outline-secondary" title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $folder)
                                        <form action="{{ route('folders.destroy', $folder) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ __('No folders found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $folders->links() }}
    </div>
</div>
@endsection
```

---

#### Tâche 1.2: Créer les vues Digital Documents
**Priorité**: 🔴 CRITIQUE  
**Durée**: 3 jours  
**Assigné à**: Frontend Developer

**Fichiers à créer**:
```
resources/views/repositories/documents/
├── index.blade.php      - Liste avec filtres avancés
├── create.blade.php     - Formulaire + upload
├── edit.blade.php       - Formulaire édition
├── show.blade.php       - Détail + métadonnées + actions
├── versions.blade.php   - Historique versions
├── upload.blade.php     - Upload nouvelle version
└── partials/
    ├── form.blade.php           - Formulaire réutilisable
    ├── workflow.blade.php       - Boutons approve/reject
    ├── signature.blade.php      - Signature électronique
    ├── checkout.blade.php       - Check-out/check-in
    └── version-history.blade.php - Tableau versions
```

**Checklist**:
- [ ] `index.blade.php`: Liste paginée avec filtres (type, folder, status, signature_status)
- [ ] `index.blade.php`: Badges visuels (statut, signature, checkout)
- [ ] `create.blade.php`: Formulaire + upload fichier avec validation
- [ ] `create.blade.php`: Sélection folder parent (autocomplete)
- [ ] `edit.blade.php`: Formulaire pré-rempli (sans upload)
- [ ] `show.blade.php`: Affichage métadonnées complètes
- [ ] `show.blade.php`: Bouton download avec compteur
- [ ] `show.blade.php`: Intégration partials workflow/signature/checkout
- [ ] `versions.blade.php`: Tableau historique avec actions (download, restore)
- [ ] `upload.blade.php`: Upload nouvelle version avec notes
- [ ] `partials/workflow.blade.php`: Boutons approve/reject avec modal
- [ ] `partials/signature.blade.php`: Interface signature (si non checkout)
- [ ] `partials/checkout.blade.php`: Boutons checkout/checkin avec état
- [ ] Tester toutes les vues avec workflow complet

**Code de référence**:
```blade
{{-- resources/views/repositories/documents/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            {{-- En-tête --}}
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1>{{ $document->name }}</h1>
                    <p class="text-muted">
                        <code>{{ $document->code }}</code>
                        @if($document->folder)
                            · <a href="{{ route('folders.show', $document->folder) }}">{{ $document->folder->name }}</a>
                        @endif
                    </p>
                </div>
                <div>
                    @can('update', $document)
                        <a href="{{ route('documents.edit', $document) }}" class="btn btn-secondary">
                            <i class="bi bi-pencil"></i> {{ __('Edit') }}
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Statuts --}}
            <div class="mb-4">
                <span class="badge bg-{{ $document->status === 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($document->status) }}
                </span>
                @if($document->signature_status === 'signed')
                    <span class="badge bg-primary">
                        <i class="bi bi-patch-check"></i> Signed
                    </span>
                @endif
                @if($document->isCheckedOut())
                    <span class="badge bg-warning">
                        <i class="bi bi-lock"></i> Checked out by {{ $document->checkedOutUser->name }}
                    </span>
                @endif
                @if($document->requires_approval && !$document->approved_at)
                    <span class="badge bg-info">Pending Approval</span>
                @elseif($document->approved_at)
                    <span class="badge bg-success">Approved</span>
                @endif
            </div>

            {{-- Fichier principal --}}
            @if($document->attachment)
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="mb-1">
                                    <i class="bi bi-file-earmark"></i> {{ $document->attachment->name }}
                                </h5>
                                <p class="text-muted mb-0">
                                    {{ \App\Helpers\FileHelper::formatBytes($document->attachment->size) }}
                                    · {{ $document->attachment->mime_type }}
                                    · Version {{ $document->version_number }}
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('documents.download', $document) }}" class="btn btn-primary">
                                    <i class="bi bi-download"></i> Download
                                    @if($document->download_count > 0)
                                        <span class="badge bg-light text-dark">{{ $document->download_count }}</span>
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Description --}}
            @if($document->description)
                <div class="card mb-4">
                    <div class="card-header">{{ __('Description') }}</div>
                    <div class="card-body">
                        <p>{{ $document->description }}</p>
                    </div>
                </div>
            @endif

            {{-- Métadonnées --}}
            <div class="card mb-4">
                <div class="card-header">{{ __('Metadata') }}</div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __('Type') }}</dt>
                        <dd class="col-sm-9">{{ $document->type->name }}</dd>

                        <dt class="col-sm-3">{{ __('Creator') }}</dt>
                        <dd class="col-sm-9">{{ $document->creator->name }}</dd>

                        <dt class="col-sm-3">{{ __('Organisation') }}</dt>
                        <dd class="col-sm-9">{{ $document->organisation->name }}</dd>

                        @if($document->document_date)
                            <dt class="col-sm-3">{{ __('Document Date') }}</dt>
                            <dd class="col-sm-9">{{ $document->document_date->format('d/m/Y') }}</dd>
                        @endif

                        <dt class="col-sm-3">{{ __('Created') }}</dt>
                        <dd class="col-sm-9">{{ $document->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-3">{{ __('Last Modified') }}</dt>
                        <dd class="col-sm-9">{{ $document->updated_at->format('d/m/Y H:i') }}</dd>

                        @if($document->last_viewed_at)
                            <dt class="col-sm-3">{{ __('Last Viewed') }}</dt>
                            <dd class="col-sm-9">
                                {{ $document->last_viewed_at->format('d/m/Y H:i') }}
                                @if($document->lastViewer)
                                    by {{ $document->lastViewer->name }}
                                @endif
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Versions --}}
            @if($document->childVersions->count() > 0 || $document->parent_version_id)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>{{ __('Version History') }}</span>
                        <a href="{{ route('documents.versions', $document) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('View All') }}
                        </a>
                    </div>
                    <div class="card-body">
                        @include('repositories.documents.partials.version-history', ['versions' => $document->getAllVersions()->take(5)])
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            {{-- Actions Workflow --}}
            @include('repositories.documents.partials.workflow')

            {{-- Check-out/Check-in --}}
            @include('repositories.documents.partials.checkout')

            {{-- Signature --}}
            @include('repositories.documents.partials.signature')

            {{-- Keywords --}}
            @if($document->keywords->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">{{ __('Keywords') }}</div>
                    <div class="card-body">
                        @foreach($document->keywords as $keyword)
                            <span class="badge bg-light text-dark me-1 mb-1">{{ $keyword->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
```

---

### Semaine 2: Fonctionnalités Avancées

#### Tâche 2.1: Exposer Check-out/Check-in
**Priorité**: 🔴 CRITIQUE  
**Durée**: 1 jour  
**Assigné à**: Backend Developer

**Routes à ajouter**:
```php
// routes/web.php (ajouter après documents resource)
Route::post('documents/{document}/checkout', [DocumentController::class, 'checkout'])
    ->name('documents.checkout');
Route::post('documents/{document}/checkin', [DocumentController::class, 'checkin'])
    ->name('documents.checkin');
Route::post('documents/{document}/cancel-checkout', [DocumentController::class, 'cancelCheckout'])
    ->name('documents.cancel-checkout');
```

**Méthodes contrôleur**:
```php
// app/Http/Controllers/Web/DocumentController.php

public function checkout(RecordDigitalDocument $document)
{
    $this->authorize('update', $document);

    if (!$document->canCheckout(Auth::user())) {
        return back()->with('error', 'Cannot check out this document.');
    }

    $result = $document->checkout(Auth::user());

    if ($result) {
        return back()->with('success', 'Document checked out successfully.');
    }

    return back()->with('error', 'Failed to check out document.');
}

public function checkin(Request $request, RecordDigitalDocument $document)
{
    $this->authorize('update', $document);

    if ($document->checked_out_by !== Auth::id()) {
        return back()->with('error', 'You did not check out this document.');
    }

    $request->validate([
        'file' => 'required|file|max:' . ($document->type->max_file_size ?? 51200),
        'version_notes' => 'nullable|string|max:1000',
    ]);

    DB::beginTransaction();
    try {
        $newVersion = $document->checkin(
            Auth::user(),
            $request->file('file'),
            $request->version_notes
        );

        // Mise à jour stats folder
        if ($document->folder) {
            $document->folder->updateStatistics();
        }

        DB::commit();
        return redirect()
            ->route('documents.show', $newVersion)
            ->with('success', 'New version created successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to check in document: ' . $e->getMessage());
    }
}

public function cancelCheckout(RecordDigitalDocument $document)
{
    $this->authorize('update', $document);

    if ($document->checked_out_by !== Auth::id() && !Auth::user()->hasRole('superadmin')) {
        return back()->with('error', 'You did not check out this document.');
    }

    $result = $document->cancelCheckout(Auth::user());

    if ($result) {
        return back()->with('success', 'Check-out cancelled.');
    }

    return back()->with('error', 'Failed to cancel check-out.');
}
```

**Checklist**:
- [ ] Ajouter routes checkout/checkin/cancel-checkout
- [ ] Implémenter méthodes contrôleur avec authorizations
- [ ] Créer vue partielle `partials/checkout.blade.php`
- [ ] Tester workflow complet (checkout → edit → checkin)
- [ ] Tester cancel checkout
- [ ] Vérifier permissions (seul checkout user peut checkin/cancel)

---

#### Tâche 2.2: Exposer Signature Électronique
**Priorité**: 🔴 CRITIQUE  
**Durée**: 2 jours  
**Assigné à**: Backend Developer

**Routes à ajouter**:
```php
// routes/web.php
Route::post('documents/{document}/sign', [DocumentController::class, 'sign'])
    ->name('documents.sign');
Route::post('documents/{document}/verify-signature', [DocumentController::class, 'verifySignature'])
    ->name('documents.verify-signature');
Route::post('documents/{document}/revoke-signature', [DocumentController::class, 'revokeSignature'])
    ->name('documents.revoke-signature');
```

**Méthodes contrôleur**:
```php
public function sign(Request $request, RecordDigitalDocument $document)
{
    // TODO: Ajouter permission 'digital_documents_sign'
    $this->authorize('update', $document);

    if ($document->isCheckedOut()) {
        return back()->with('error', 'Cannot sign a checked-out document.');
    }

    if ($document->signature_status === 'signed') {
        return back()->with('error', 'Document already signed.');
    }

    $request->validate([
        'signature_password' => 'required|string',
        'signature_reason' => 'nullable|string|max:500',
    ]);

    // Générer signature data
    $signatureData = [
        'signer_name' => Auth::user()->name,
        'signer_email' => Auth::user()->email,
        'signature_reason' => $request->signature_reason,
        'signature_timestamp' => now()->toIso8601String(),
        'document_hash' => hash_file('sha256', Storage::path($document->attachment->path)),
        'signature_hash' => hash('sha256', $request->signature_password . now()->timestamp),
    ];

    $result = $document->sign(Auth::user(), $signatureData);

    if ($result) {
        return back()->with('success', 'Document signed successfully.');
    }

    return back()->with('error', 'Failed to sign document.');
}

public function verifySignature(RecordDigitalDocument $document)
{
    $result = $document->verifySignature();

    if ($result) {
        return back()->with('success', 'Signature is valid.');
    }

    return back()->with('error', 'Signature verification failed or document has been modified.');
}

public function revokeSignature(Request $request, RecordDigitalDocument $document)
{
    $this->authorize('update', $document);

    $request->validate([
        'revoke_reason' => 'required|string|max:500',
    ]);

    $result = $document->revokeSignature(Auth::user(), $request->revoke_reason);

    if ($result) {
        return back()->with('success', 'Signature revoked.');
    }

    return back()->with('error', 'Failed to revoke signature.');
}
```

**Checklist**:
- [ ] Ajouter routes sign/verify/revoke
- [ ] Implémenter méthodes contrôleur
- [ ] Créer vue partielle `partials/signature.blade.php`
- [ ] Ajouter modal signature avec champ password
- [ ] Tester signature → verify → revoke
- [ ] Vérifier hash document après signature
- [ ] Afficher détails signature (qui, quand, raison)

---

#### Tâche 2.3: Restore Version
**Priorité**: 🟠 IMPORTANT  
**Durée**: 1 jour  
**Assigné à**: Backend Developer

**Route à ajouter**:
```php
Route::post('documents/{document}/versions/{version}/restore', [DocumentController::class, 'restoreVersion'])
    ->name('documents.versions.restore');
```

**Méthode contrôleur**:
```php
public function restoreVersion(RecordDigitalDocument $document, int $versionId)
{
    $this->authorize('update', $document);

    $version = RecordDigitalDocument::where('id', $versionId)
        ->where('code', $document->code)
        ->firstOrFail();

    if ($document->isCheckedOut()) {
        return back()->with('error', 'Cannot restore version while document is checked out.');
    }

    DB::beginTransaction();
    try {
        $newVersion = $document->restoreVersion($version->version_number);

        DB::commit();
        return redirect()
            ->route('documents.show', $newVersion)
            ->with('success', "Version {$version->version_number} restored as new version {$newVersion->version_number}.");
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to restore version: ' . $e->getMessage());
    }
}
```

**Checklist**:
- [ ] Ajouter route restore
- [ ] Implémenter méthode contrôleur
- [ ] Ajouter bouton "Restore" dans versions.blade.php
- [ ] Ajouter confirmation modal
- [ ] Tester restore → vérifier nouvelle version créée
- [ ] Vérifier is_current_version mis à jour

---

#### Tâche 2.4: Séparer Index Mixte (Performance)
**Priorité**: 🔴 CRITIQUE  
**Durée**: 2 jours  
**Assigné à**: Backend Developer

**Problème actuel**:
```php
// RecordController::index() - 3 requêtes + fusion mémoire
$physicalQuery = RecordPhysical::with([...])->get();
$foldersQuery = RecordDigitalFolder::with([...])->get();
$documentsQuery = RecordDigitalDocument::with([...])->get();
// Fusion manuelle...
```

**Solution**:
```php
// RecordController::index() - PHYSICAL UNIQUEMENT
public function index(Request $request)
{
    $query = RecordPhysical::with([
        'level', 'status', 'support', 'activity',
        'containers', 'authors', 'thesaurusConcepts', 'keywords'
    ]);

    // Filtres existants
    if ($request->filled('keyword_filter')) { ... }

    $records = $query->orderBy('created_at', 'desc')->paginate(20);

    return view('records.index', compact('records'));
}
```

**Modifications routes**:
```php
// routes/web.php - Renommer route
Route::get('records', [RecordController::class, 'index'])
    ->name('records.index'); // Physical uniquement

// Menu: Changer "Mes archives" → "Physical Records"
```

**Checklist**:
- [ ] Modifier `RecordController::index()` pour Physical uniquement
- [ ] Supprimer code fusion 3 types
- [ ] Mettre à jour vue `records/index.blade.php` (supprimer logique type)
- [ ] Mettre à jour menu submenu (renommer "Mes archives")
- [ ] Vérifier performance (1 requête au lieu de 3)
- [ ] Tester pagination
- [ ] Optionnel: Créer Dashboard global si nécessaire

---

## 📅 Phase 2: Améliorations Importantes (Semaines 3-4)

### Semaine 3: Permissions et Tests

#### Tâche 3.1: Ajouter Permissions Workflow
**Priorité**: 🟠 IMPORTANT  
**Durée**: 1 jour  
**Assigné à**: Backend Developer

**Permissions à créer**:
```php
// database/seeders/DigitalRecordPermissionsSeeder.php
$workflowPermissions = [
    // Documents
    ['name' => 'digital_documents_approve', 'description' => 'Approve digital documents'],
    ['name' => 'digital_documents_reject', 'description' => 'Reject digital documents'],
    ['name' => 'digital_documents_sign', 'description' => 'Sign digital documents'],
    ['name' => 'digital_documents_checkout', 'description' => 'Check-out digital documents'],
    ['name' => 'digital_documents_manage_versions', 'description' => 'Manage document versions'],

    // Folders
    ['name' => 'digital_folders_approve', 'description' => 'Approve digital folders'],
    ['name' => 'digital_folders_reject', 'description' => 'Reject digital folders'],
    ['name' => 'digital_folders_move', 'description' => 'Move folders in hierarchy'],
];
```

**Policies à mettre à jour**:
```php
// app/Policies/RecordDigitalDocumentPolicy.php
public function approve(User $user, RecordDigitalDocument $document): bool
{
    return $user->hasRole('superadmin') || $user->can('digital_documents_approve');
}

public function sign(User $user, RecordDigitalDocument $document): bool
{
    return $user->hasRole('superadmin') || $user->can('digital_documents_sign');
}

public function checkout(User $user, RecordDigitalDocument $document): bool
{
    return $user->hasRole('superadmin') || $user->can('digital_documents_checkout');
}
```

**Checklist**:
- [ ] Ajouter 8 nouvelles permissions au seeder
- [ ] Exécuter seeder (idempotent)
- [ ] Mettre à jour policies (ajouter méthodes approve, sign, checkout)
- [ ] Enregistrer nouvelles méthodes policies
- [ ] Mettre à jour contrôleurs (utiliser nouvelles autorizations)
- [ ] Mettre à jour vues (utiliser @can avec nouvelles permissions)
- [ ] Tester avec utilisateur non-superadmin

---

#### Tâche 3.2: Tests Feature Controllers
**Priorité**: 🟠 IMPORTANT  
**Durée**: 3 jours  
**Assigné à**: Backend Developer

**Tests à créer**:
```
tests/Feature/
├── FolderControllerTest.php (15 tests)
│   ├── test_can_list_folders
│   ├── test_can_filter_folders
│   ├── test_can_create_folder
│   ├── test_creates_folder_with_auto_code
│   ├── test_can_view_folder
│   ├── test_can_edit_folder
│   ├── test_can_delete_folder
│   ├── test_cannot_delete_folder_with_children
│   ├── test_can_move_folder
│   ├── test_cannot_create_circular_hierarchy
│   ├── test_statistics_update_on_folder_creation
│   ├── test_statistics_update_on_folder_deletion
│   ├── test_statistics_update_on_folder_move
│   ├── test_unauthorized_user_cannot_create_folder
│   └── test_unauthorized_user_cannot_delete_folder
└── DocumentControllerTest.php (20 tests)
    ├── test_can_list_documents
    ├── test_can_filter_documents
    ├── test_can_create_document_with_upload
    ├── test_can_create_document_without_upload
    ├── test_validates_file_type
    ├── test_validates_file_size
    ├── test_can_view_document
    ├── test_can_edit_document
    ├── test_can_delete_document
    ├── test_can_upload_new_version
    ├── test_can_approve_document
    ├── test_can_reject_document
    ├── test_can_checkout_document
    ├── test_can_checkin_document
    ├── test_cannot_checkout_already_checked_out
    ├── test_can_cancel_checkout
    ├── test_can_sign_document
    ├── test_can_verify_signature
    ├── test_can_revoke_signature
    └── test_can_restore_version
```

**Exemple test**:
```php
// tests/Feature/DocumentControllerTest.php
class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $superadmin;
    protected $type;
    protected $organisation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('superadmin');
        
        $this->type = RecordDigitalDocumentType::factory()->create();
        $this->organisation = Organisation::factory()->create();
    }

    public function test_can_create_document_with_upload()
    {
        Storage::fake('local');
        
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->superadmin)
            ->post(route('documents.store'), [
                'name' => 'Test Document',
                'type_id' => $this->type->id,
                'organisation_id' => $this->organisation->id,
                'status' => 'draft',
                'file' => $file,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('record_digital_documents', [
            'name' => 'Test Document',
            'type_id' => $this->type->id,
            'version_number' => 1,
            'is_current_version' => true,
        ]);

        Storage::disk('local')->assertExists('documents/' . $file->hashName());
    }

    public function test_can_checkout_document()
    {
        $document = RecordDigitalDocument::factory()->create([
            'type_id' => $this->type->id,
        ]);

        $response = $this->actingAs($this->superadmin)
            ->post(route('documents.checkout', $document));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('record_digital_documents', [
            'id' => $document->id,
            'checked_out_by' => $this->superadmin->id,
        ]);
    }
}
```

**Checklist**:
- [ ] Créer factories manquantes (RecordDigitalFolderType, RecordDigitalDocumentType)
- [ ] Écrire 15 tests FolderController
- [ ] Écrire 20 tests DocumentController
- [ ] Configurer Storage fake pour tests upload
- [ ] Atteindre coverage 70%+ sur contrôleurs
- [ ] Tous les tests passent (vert)
- [ ] Intégrer dans CI/CD (GitHub Actions)

---

### Semaine 4: Tests Unitaires et Documentation

#### Tâche 4.1: Tests Unitaires Modèles
**Priorité**: 🟠 IMPORTANT  
**Durée**: 2 jours  
**Assigné à**: Backend Developer

**Tests à créer**:
```
tests/Unit/
├── RecordDigitalFolderTest.php (10 tests)
│   ├── test_generates_correct_code
│   ├── test_calculates_statistics_correctly
│   ├── test_get_ancestors_returns_correct_hierarchy
│   ├── test_get_total_size_includes_subfolders
│   ├── test_can_be_deleted_validation
│   ├── test_cannot_delete_with_children
│   ├── test_update_statistics_on_child_add
│   ├── test_parent_relationship
│   ├── test_children_relationship
│   └── test_documents_relationship
└── RecordDigitalDocumentTest.php (15 tests)
    ├── test_creates_version_correctly
    ├── test_get_latest_version
    ├── test_get_all_versions
    ├── test_checkout_workflow
    ├── test_checkin_creates_new_version
    ├── test_cancel_checkout
    ├── test_signature_workflow
    ├── test_verify_signature
    ├── test_revoke_signature
    ├── test_file_validation_type
    ├── test_file_validation_size
    ├── test_approve_workflow
    ├── test_reject_workflow
    ├── test_restore_version
    └── test_is_current_version_flag
```

**Exemple test**:
```php
// tests/Unit/RecordDigitalDocumentTest.php
class RecordDigitalDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_workflow()
    {
        $user = User::factory()->create();
        $document = RecordDigitalDocument::factory()->create();

        $this->assertFalse($document->isCheckedOut());
        $this->assertTrue($document->canCheckout($user));

        $result = $document->checkout($user);

        $this->assertTrue($result);
        $this->assertTrue($document->isCheckedOut());
        $this->assertEquals($user->id, $document->checked_out_by);
        $this->assertNotNull($document->checked_out_at);
    }

    public function test_creates_version_correctly()
    {
        Storage::fake('local');
        
        $user = User::factory()->create();
        $document = RecordDigitalDocument::factory()->create([
            'version_number' => 1,
            'is_current_version' => true,
        ]);

        $file = UploadedFile::fake()->create('document-v2.pdf', 100);

        $newVersion = $document->createNewVersion($user, $file, 'Updated content');

        $this->assertEquals(2, $newVersion->version_number);
        $this->assertTrue($newVersion->is_current_version);
        $this->assertFalse($document->fresh()->is_current_version);
        $this->assertEquals($document->id, $newVersion->parent_version_id);
    }
}
```

**Checklist**:
- [ ] Écrire 10 tests RecordDigitalFolder
- [ ] Écrire 15 tests RecordDigitalDocument
- [ ] Tester toutes les méthodes business
- [ ] Atteindre coverage 80%+ sur modèles
- [ ] Tous les tests passent

---

#### Tâche 4.2: Documentation API (Swagger)
**Priorité**: 🟡 MOYEN  
**Durée**: 2 jours  
**Assigné à**: Backend Developer

**Configuration l5-swagger** (déjà installé):
```php
// config/l5-swagger.php déjà présent
php artisan l5-swagger:generate
```

**Annotations à ajouter**:
```php
// app/Http/Controllers/Api/DigitalFolderController.php
/**
 * @OA\Get(
 *     path="/api/v1/digital-folders",
 *     tags={"Digital Folders"},
 *     summary="List digital folders",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="type_id",
 *         in="query",
 *         description="Filter by folder type ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Filter by status (active, archived, closed)",
 *         @OA\Schema(type="string", enum={"active", "archived", "closed"})
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RecordDigitalFolder")),
 *             @OA\Property(property="meta", type="object")
 *         )
 *     )
 * )
 */
public function index(Request $request) { ... }
```

**Checklist**:
- [ ] Annoter tous les endpoints API folders (10 endpoints)
- [ ] Annoter tous les endpoints API documents (12 endpoints)
- [ ] Créer schémas models (@OA\Schema)
- [ ] Ajouter exemples requêtes/réponses
- [ ] Générer documentation (`php artisan l5-swagger:generate`)
- [ ] Tester documentation `/api/documentation`
- [ ] Ajouter README API avec exemples curl

---

## 📅 Phase 3: Optimisations (Semaines 5-6)

### Semaine 5: Performance et UI/UX

#### Tâche 5.1: Optimisation Requêtes
**Priorité**: 🟡 MOYEN  
**Durée**: 2 jours  
**Assigné à**: Backend Developer

**Optimisations**:
1. **Analyse N+1**:
```bash
composer require barryvdh/laravel-debugbar --dev
```

2. **Eager loading systématique**:
```php
// FolderController::index()
$folders = RecordDigitalFolder::with([
    'type',
    'parent:id,name,code',
    'creator:id,name',
    'organisation:id,name',
])
->withCount(['children', 'documents'])
->paginate(20);
```

3. **Cache Redis**:
```php
// Cache arbre folders (coûteux à calculer)
public function tree()
{
    $tree = Cache::remember('folders.tree', 3600, function() {
        return RecordDigitalFolder::with(['children' => function($q) {
            $q->orderBy('name');
        }])->whereNull('parent_id')->orderBy('name')->get();
    });

    return response()->json($tree);
}
```

4. **Index DB supplémentaires**:
```php
// Migration
Schema::table('record_digital_folders', function (Blueprint $table) {
    $table->index(['status', 'created_at']); // Pour filtrage + tri
    $table->index(['organisation_id', 'status']); // Filtre combo fréquent
});

Schema::table('record_digital_documents', function (Blueprint $table) {
    $table->index(['folder_id', 'is_current_version']); // Documents par folder
    $table->index(['status', 'signature_status']); // Filtres fréquents
    $table->index(['organisation_id', 'status', 'is_current_version']); // Combo
});
```

**Checklist**:
- [ ] Installer debugbar
- [ ] Identifier requêtes N+1
- [ ] Ajouter eager loading partout
- [ ] Implémenter cache Redis (arbre folders)
- [ ] Créer migration index supplémentaires
- [ ] Mesurer temps réponse avant/après
- [ ] Objectif: <200ms pour index, <100ms pour show

---

#### Tâche 5.2: Amélioration UI/UX
**Priorité**: 🟡 MOYEN  
**Durée**: 3 jours  
**Assigné à**: Frontend Developer

**Améliorations**:

1. **Arbre Folders Interactif**:
```bash
npm install vue-treeselect
```
```vue
<!-- resources/js/components/FolderTreeSelect.vue -->
<template>
  <treeselect
    v-model="value"
    :options="folders"
    :normalizer="normalizer"
    placeholder="Select parent folder..."
  />
</template>
```

2. **Drag & Drop Folders**:
```javascript
// Utiliser SortableJS
import Sortable from 'sortablejs';

Sortable.create(document.getElementById('folder-list'), {
    onEnd: function (evt) {
        // Appel AJAX route move
        axios.post(`/repositories/folders/${evt.item.dataset.id}/move`, {
            parent_id: evt.to.dataset.parentId
        });
    }
});
```

3. **Prévisualisation Documents**:
```php
// Controller
public function preview(RecordDigitalDocument $document)
{
    $mimeType = $document->attachment->mime_type;
    
    if (str_starts_with($mimeType, 'image/')) {
        return response()->file(Storage::path($document->attachment->path));
    }
    
    if ($mimeType === 'application/pdf') {
        return view('repositories.documents.preview-pdf', compact('document'));
    }
    
    return redirect()->route('documents.download', $document);
}
```

4. **Indicateurs Visuels**:
```blade
{{-- Badges statut --}}
@if($document->requires_approval && !$document->approved_at)
    <span class="badge bg-warning">
        <i class="bi bi-clock-history"></i> Pending Approval
    </span>
@endif

@if($document->isCheckedOut())
    <span class="badge bg-danger">
        <i class="bi bi-lock-fill"></i> Locked
    </span>
@endif

{{-- Progress bar rétention --}}
@if($document->retention_until)
    @php
        $total = $document->created_at->diffInDays($document->retention_until);
        $elapsed = now()->diffInDays($document->retention_until);
        $percent = max(0, ($elapsed / $total) * 100);
    @endphp
    <div class="progress" title="Retention: {{ $elapsed }} days remaining">
        <div class="progress-bar" style="width: {{ $percent }}%"></div>
    </div>
@endif
```

**Checklist**:
- [ ] Intégrer vue-treeselect pour sélection parent
- [ ] Implémenter drag & drop folders (move)
- [ ] Créer prévisualisation PDF/images
- [ ] Ajouter badges visuels (statuts, checkout, approval)
- [ ] Ajouter icônes selon type fichier
- [ ] Améliorer responsive mobile
- [ ] Tester UX complète

---

### Semaine 6: Monitoring et Finalisation

#### Tâche 6.1: Logs Audit
**Priorité**: 🟡 MOYEN  
**Durée**: 2 jours  
**Assigné à**: Backend Developer

**Package audit**:
```bash
composer require owen-it/laravel-auditing
php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider"
php artisan migrate
```

**Activer audit**:
```php
// app/Models/RecordDigitalFolder.php
use OwenIt\Auditing\Contracts\Auditable;

class RecordDigitalFolder extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    // ...
}

// Idem pour RecordDigitalDocument
```

**Vue audit**:
```php
// Controller
public function audit(RecordDigitalDocument $document)
{
    $audits = $document->audits()->with('user')->latest()->paginate(20);
    return view('repositories.documents.audit', compact('document', 'audits'));
}
```

**Checklist**:
- [ ] Installer laravel-auditing
- [ ] Activer sur RecordDigitalFolder et RecordDigitalDocument
- [ ] Créer vue audit (qui a fait quoi, quand)
- [ ] Ajouter route audit
- [ ] Tester logs création/modification/suppression
- [ ] Conserver historique 1 an minimum

---

#### Tâche 6.2: Métriques et Alertes
**Priorité**: 🟡 MOYEN  
**Durée**: 2 jours  
**Assigné à**: Backend Developer

**Dashboard métriques**:
```php
// app/Http/Controllers/DashboardController.php
public function digitalRecordsStats()
{
    return [
        'folders' => [
            'total' => RecordDigitalFolder::count(),
            'active' => RecordDigitalFolder::where('status', 'active')->count(),
            'archived' => RecordDigitalFolder::where('status', 'archived')->count(),
            'by_type' => RecordDigitalFolder::select('type_id', DB::raw('count(*) as count'))
                ->groupBy('type_id')
                ->with('type:id,name')
                ->get(),
        ],
        'documents' => [
            'total' => RecordDigitalDocument::where('is_current_version', true)->count(),
            'by_status' => RecordDigitalDocument::where('is_current_version', true)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'pending_approval' => RecordDigitalDocument::where('requires_approval', true)
                ->whereNull('approved_at')
                ->count(),
            'checked_out' => RecordDigitalDocument::whereNotNull('checked_out_by')->count(),
            'signed' => RecordDigitalDocument::where('signature_status', 'signed')->count(),
            'most_downloaded' => RecordDigitalDocument::where('is_current_version', true)
                ->orderBy('download_count', 'desc')
                ->take(10)
                ->get(['id', 'name', 'download_count']),
        ],
    ];
}
```

**Alertes**:
```php
// app/Console/Commands/CheckPendingApprovals.php
public function handle()
{
    $pending = RecordDigitalDocument::where('requires_approval', true)
        ->whereNull('approved_at')
        ->where('created_at', '<', now()->subDays(7))
        ->get();

    if ($pending->count() > 0) {
        // Envoyer email notification admins
        Mail::to(config('app.admin_email'))
            ->send(new PendingApprovalsAlert($pending));
    }
}
```

**Checklist**:
- [ ] Créer dashboard statistiques
- [ ] Afficher métriques (totaux, par statut, par type)
- [ ] Graphiques (Chart.js) évolution dans le temps
- [ ] Documents les plus consultés
- [ ] Command alertes approbations bloquées (7+ jours)
- [ ] Command alertes checkouts oubliés (14+ jours)
- [ ] Scheduler commands (daily)

---

#### Tâche 6.3: Documentation Utilisateur
**Priorité**: 🟡 MOYEN  
**Durée**: 1 jour  
**Assigné à**: Technical Writer

**Guides à créer**:
```
docs/
├── USER_GUIDE_DIGITAL_FOLDERS.md
│   ├── Création folder
│   ├── Organisation hiérarchique
│   ├── Déplacement folders
│   ├── Statistiques
│   └── Suppression
├── USER_GUIDE_DIGITAL_DOCUMENTS.md
│   ├── Création document avec upload
│   ├── Workflow approbation
│   ├── Check-out/Check-in
│   ├── Signature électronique
│   ├── Gestion versions
│   └── Restauration version
└── ADMIN_GUIDE_DIGITAL_RECORDS.md
    ├── Configuration types
    ├── Gestion permissions
    ├── Monitoring
    └── Maintenance
```

**Checklist**:
- [ ] Rédiger guide utilisateur folders (avec screenshots)
- [ ] Rédiger guide utilisateur documents (workflow complet)
- [ ] Rédiger guide admin (configuration + monitoring)
- [ ] Créer vidéos tutoriels (5-10 min chacune)
- [ ] Ajouter FAQ
- [ ] Publier sur wiki interne

---

## ✅ Critères d'Acceptation Production-Ready

### Fonctionnalités (95%+)
- [x] ✅ Modèles complets (Physical, Folder, Document)
- [x] ✅ Migrations complètes
- [x] ✅ Controllers complets
- [ ] 🔴 Vues complètes (folders + documents)
- [ ] 🔴 Routes workflow exposées (checkout, signature)
- [ ] 🟠 Permissions workflow
- [x] ✅ API REST complète

### Qualité (70%+)
- [ ] 🟠 Tests feature (35 tests minimum)
- [ ] 🟠 Tests unitaires (25 tests minimum)
- [ ] 🟠 Coverage 70%+ sur nouveaux modèles
- [ ] 🟡 Documentation API complète
- [ ] 🟡 Logs audit activés

### Performance
- [ ] 🟡 Index <200ms
- [ ] 🟡 Show <100ms
- [ ] 🟡 Pas de requêtes N+1
- [ ] 🟡 Cache actif (arbre folders)

### Sécurité
- [x] ✅ Policies complètes
- [ ] 🟠 Permissions workflow
- [x] ✅ Validation fichiers
- [x] ✅ Soft deletes

### Documentation
- [x] ✅ Analyse intégration (INTEGRATION_ANALYSIS_PHASE3.md)
- [ ] 🔴 Plan d'action (ce fichier)
- [ ] 🟡 Guide utilisateur
- [ ] 🟡 Guide admin
- [ ] 🟠 Documentation API

---

## 📊 Suivi Progression

### Statut Actuel (Semaine 0)
```
Backend:        ████████░░ 85%
Frontend:       ██░░░░░░░░ 20%
Tests:          ░░░░░░░░░░  0%
Documentation:  ████░░░░░░ 45%
Performance:    ████░░░░░░ 50%

GLOBAL:         ████░░░░░░ 56% - ALPHA
```

### Objectif Production (Semaine 6)
```
Backend:        ██████████ 100%
Frontend:       █████████░  95%
Tests:          ████████░░  80%
Documentation:  █████████░  90%
Performance:    █████████░  90%

GLOBAL:         █████████░  95% - PRODUCTION-READY
```

---

## 🎯 Priorités par Rôle

### Backend Developer (4 semaines)
1. 🔴 Semaine 1: Routes checkout/signature/restore
2. 🔴 Semaine 1: Séparer index mixte
3. 🟠 Semaine 3: Permissions workflow
4. 🟠 Semaine 3-4: Tests (feature + unit)
5. 🟡 Semaine 4: Documentation API
6. 🟡 Semaine 5: Optimisations performance
7. 🟡 Semaine 6: Logs audit + métriques

### Frontend Developer (3 semaines)
1. 🔴 Semaine 1: Vues folders (4 fichiers + partials)
2. 🔴 Semaine 1-2: Vues documents (6 fichiers + partials)
3. 🟡 Semaine 5: UI/UX (arbre interactif, drag & drop, preview)

### Technical Writer (1 semaine)
1. 🟡 Semaine 6: Guides utilisateur (folders + documents)
2. 🟡 Semaine 6: Guide admin
3. 🟡 Semaine 6: FAQ + vidéos tutoriels

---

## 🚀 Déploiement

### Pré-Production (Fin Semaine 5)
- [ ] Tous les tests passent
- [ ] Coverage 70%+
- [ ] Performance validée
- [ ] Documentation API complète
- [ ] Seeders à jour
- [ ] Migration plan testé

### Production (Semaine 6)
- [ ] Backup base données
- [ ] Exécuter migrations
- [ ] Exécuter seeders (permissions)
- [ ] Vérifier permissions superadmin
- [ ] Tester fonctionnalités critiques
- [ ] Formation utilisateurs
- [ ] Support J+1 semaine

---

**Version**: 1.0  
**Dernière mise à jour**: 8 Novembre 2025  
**Responsable**: Chef de Projet Digital Records
