@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Modifier le document: {{ $document->name }}</h1>
        <div>
            <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">
                <i class="bi bi-eye"></i> Voir le document
            </a>
            <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    @if($document->checked_out_by)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Document verrouillé:</strong>
            Ce document est actuellement emprunté par <strong>{{ $document->checkedOutUser->name }}</strong>
            depuis le {{ $document->checked_out_at?->format('d/m/Y H:i') }}.
            @if($document->checked_out_by !== auth()->id())
                Vous ne pouvez pas le modifier.
            @endif
        </div>
    @endif

    @if($document->signature_status === 'signed')
        <div class="alert alert-info">
            <i class="bi bi-shield-check"></i>
            <strong>Document signé:</strong>
            Ce document a été signé par <strong>{{ $document->signer->name ?? 'Inconnu' }}</strong>
            le {{ $document->signed_at?->format('d/m/Y H:i') }}.
            Les modifications peuvent invalider la signature.
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('documents.update', $document) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $document->name) }}" required
                                   {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="type_id" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type_id') is-invalid @enderror"
                                    id="type_id" name="type_id" required
                                    {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>
                                <option value="">-- Sélectionner un type --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}"
                                        {{ old('type_id', $document->type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="folder_id" class="form-label">Dossier <span class="text-danger">*</span></label>
                            <select class="form-select @error('folder_id') is-invalid @enderror"
                                    id="folder_id" name="folder_id" required
                                    {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>
                                <option value="">-- Sélectionner un dossier --</option>
                                @foreach($folders as $folder)
                                    <option value="{{ $folder->id }}"
                                        {{ old('folder_id', $document->folder_id) == $folder->id ? 'selected' : '' }}>
                                        {{ $folder->name }} ({{ $folder->type->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('folder_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="organisation_id" class="form-label">Organisation <span class="text-danger">*</span></label>
                            <select class="form-select @error('organisation_id') is-invalid @enderror"
                                    id="organisation_id" name="organisation_id" required
                                    {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>
                                <option value="">-- Sélectionner une organisation --</option>
                                @foreach($organisations as $organisation)
                                    <option value="{{ $organisation->id }}"
                                        {{ old('organisation_id', $document->organisation_id) == $organisation->id ? 'selected' : '' }}>
                                        {{ $organisation->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('organisation_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="4"
                              {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>{{ old('description', $document->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Note:</strong> Pour modifier le fichier, utilisez le bouton "Nouvelle version" sur la page de détails.
                    Cette page permet uniquement de modifier les métadonnées du document.
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="access_level" class="form-label">Niveau d'accès <span class="text-danger">*</span></label>
                            <select class="form-select @error('access_level') is-invalid @enderror"
                                    id="access_level" name="access_level" required
                                    {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>
                                <option value="public" {{ old('access_level', $document->access_level) == 'public' ? 'selected' : '' }}>Public</option>
                                <option value="internal" {{ old('access_level', $document->access_level) == 'internal' ? 'selected' : '' }}>Interne</option>
                                <option value="confidential" {{ old('access_level', $document->access_level) == 'confidential' ? 'selected' : '' }}>Confidentiel</option>
                                <option value="secret" {{ old('access_level', $document->access_level) == 'secret' ? 'selected' : '' }}>Secret</option>
                            </select>
                            @error('access_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required
                                    {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>
                                <option value="draft" {{ old('status', $document->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="active" {{ old('status', $document->status) == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="archived" {{ old('status', $document->status) == 'archived' ? 'selected' : '' }}>Archivé</option>
                                <option value="obsolete" {{ old('status', $document->status) == 'obsolete' ? 'selected' : '' }}>Obsolète</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="signature_status" class="form-label">Signature</label>
                            <select class="form-select" id="signature_status" name="signature_status" disabled>
                                <option value="unsigned" {{ $document->signature_status == 'unsigned' ? 'selected' : '' }}>Non signé</option>
                                <option value="pending" {{ $document->signature_status == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="signed" {{ $document->signature_status == 'signed' ? 'selected' : '' }}>Signé</option>
                                <option value="rejected" {{ $document->signature_status == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                            </select>
                            <small class="form-text text-muted">Le statut de signature ne peut pas être modifié ici</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assigné à</label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror"
                                    id="assigned_to" name="assigned_to"
                                    {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>
                                <option value="">-- Non assigné --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('assigned_to', $document->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="document_date" class="form-label">Date du document</label>
                            <input type="date" class="form-control @error('document_date') is-invalid @enderror"
                                   id="document_date" name="document_date"
                                   value="{{ old('document_date', $document->document_date?->format('Y-m-d')) }}"
                                   {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>
                            @error('document_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="requires_approval"
                                   name="requires_approval" value="1"
                                   {{ old('requires_approval', $document->requires_approval) ? 'checked' : '' }}
                                   {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>
                            <label class="form-check-label" for="requires_approval">
                                Nécessite une approbation
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="retention_until" class="form-label">Conservation jusqu'au</label>
                            <input type="date" class="form-control @error('retention_until') is-invalid @enderror"
                                   id="retention_until" name="retention_until"
                                   value="{{ old('retention_until', $document->retention_until?->format('Y-m-d')) }}"
                                   {{ $document->checked_out_by && $document->checked_out_by !== auth()->id() ? 'disabled' : '' }}>
                            @error('retention_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @if($document->requires_approval && $document->approved_by)
                    <div class="alert alert-success">
                        <strong>Approuvé par:</strong> {{ $document->approver->name ?? 'Inconnu' }}<br>
                        <strong>Date:</strong> {{ $document->approved_at?->format('d/m/Y H:i') }}<br>
                        @if($document->approval_notes)
                            <strong>Notes:</strong> {{ $document->approval_notes }}
                        @endif
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">Annuler</a>
                    @if(!$document->checked_out_by || $document->checked_out_by === auth()->id())
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Mettre à jour
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">Informations de version</h5>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Code</dt>
                <dd class="col-sm-9"><code>{{ $document->code }}</code></dd>

                <dt class="col-sm-3">Version actuelle</dt>
                <dd class="col-sm-9">
                    <span class="badge bg-primary">v{{ $document->version_number }}</span>
                    @if($document->is_current_version)
                        <span class="badge bg-success">Actuelle</span>
                    @endif
                </dd>

                @if($document->version_notes)
                    <dt class="col-sm-3">Notes de version</dt>
                    <dd class="col-sm-9">{{ $document->version_notes }}</dd>
                @endif

                <dt class="col-sm-3">Créé par</dt>
                <dd class="col-sm-9">{{ $document->creator->name ?? 'Inconnu' }}</dd>

                <dt class="col-sm-3">Créé le</dt>
                <dd class="col-sm-9">{{ $document->created_at?->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Modifié le</dt>
                <dd class="col-sm-9">{{ $document->updated_at?->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Statistiques</dt>
                <dd class="col-sm-9">
                    {{ $document->download_count }} téléchargement(s)
                    @if($document->last_viewed_at)
                        <br>Dernière consultation: {{ $document->last_viewed_at->format('d/m/Y H:i') }}
                        par {{ $document->lastViewer->name ?? 'Inconnu' }}
                    @endif
                </dd>

                @if($document->is_archived)
                    <dt class="col-sm-3">Archivage</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-warning">Archivé</span>
                        le {{ $document->archived_at?->format('d/m/Y H:i') }}
                    </dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
