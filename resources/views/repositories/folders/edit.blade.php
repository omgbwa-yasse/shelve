@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Modifier le dossier: {{ $folder->name }}</h1>
        <div>
            <a href="{{ route('folders.show', $folder) }}" class="btn btn-secondary">
                <i class="bi bi-eye"></i> Voir le dossier
            </a>
            <a href="{{ route('folders.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('folders.update', $folder) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $folder->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="type_id" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type_id') is-invalid @enderror"
                                    id="type_id" name="type_id" required>
                                <option value="">-- Sélectionner un type --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}"
                                        {{ old('type_id', $folder->type_id) == $type->id ? 'selected' : '' }}>
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
                            <label for="parent_id" class="form-label">Dossier parent</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror"
                                    id="parent_id" name="parent_id">
                                <option value="">-- Racine (pas de parent) --</option>
                                @foreach($parentFolders as $potentialParent)
                                    @if($potentialParent->id !== $folder->id)
                                        <option value="{{ $potentialParent->id }}"
                                            {{ old('parent_id', $folder->parent_id) == $potentialParent->id ? 'selected' : '' }}>
                                            {{ $potentialParent->name }} ({{ $potentialParent->type->name }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Attention: le changement de parent peut créer une boucle. Vérifiez la hiérarchie.
                            </small>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="organisation_id" class="form-label">Organisation <span class="text-danger">*</span></label>
                            <select class="form-select @error('organisation_id') is-invalid @enderror"
                                    id="organisation_id" name="organisation_id" required>
                                <option value="">-- Sélectionner une organisation --</option>
                                @foreach($organisations as $organisation)
                                    <option value="{{ $organisation->id }}"
                                        {{ old('organisation_id', $folder->organisation_id) == $organisation->id ? 'selected' : '' }}>
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
                              id="description" name="description" rows="4">{{ old('description', $folder->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="access_level" class="form-label">Niveau d'accès <span class="text-danger">*</span></label>
                            <select class="form-select @error('access_level') is-invalid @enderror"
                                    id="access_level" name="access_level" required>
                                <option value="public" {{ old('access_level', $folder->access_level) == 'public' ? 'selected' : '' }}>Public</option>
                                <option value="internal" {{ old('access_level', $folder->access_level) == 'internal' ? 'selected' : '' }}>Interne</option>
                                <option value="confidential" {{ old('access_level', $folder->access_level) == 'confidential' ? 'selected' : '' }}>Confidentiel</option>
                                <option value="secret" {{ old('access_level', $folder->access_level) == 'secret' ? 'selected' : '' }}>Secret</option>
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
                                    id="status" name="status" required>
                                <option value="active" {{ old('status', $folder->status) == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="closed" {{ old('status', $folder->status) == 'closed' ? 'selected' : '' }}>Fermé</option>
                                <option value="archived" {{ old('status', $folder->status) == 'archived' ? 'selected' : '' }}>Archivé</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assigné à</label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror"
                                    id="assigned_to" name="assigned_to">
                                <option value="">-- Non assigné --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('assigned_to', $folder->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Date de début</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                   id="start_date" name="start_date"
                                   value="{{ old('start_date', $folder->start_date?->format('Y-m-d')) }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Date de fin</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                   id="end_date" name="end_date"
                                   value="{{ old('end_date', $folder->end_date?->format('Y-m-d')) }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="requires_approval"
                           name="requires_approval" value="1"
                           {{ old('requires_approval', $folder->requires_approval) ? 'checked' : '' }}>
                    <label class="form-check-label" for="requires_approval">
                        Nécessite une approbation
                    </label>
                </div>

                @if($folder->requires_approval && $folder->approved_by)
                    <div class="alert alert-info">
                        <strong>Approuvé par:</strong> {{ $folder->approver->name ?? 'Inconnu' }}<br>
                        <strong>Date:</strong> {{ $folder->approved_at?->format('d/m/Y H:i') }}<br>
                        @if($folder->approval_notes)
                            <strong>Notes:</strong> {{ $folder->approval_notes }}
                        @endif
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('folders.show', $folder) }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">Informations système</h5>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Code</dt>
                <dd class="col-sm-9"><code>{{ $folder->code }}</code></dd>

                <dt class="col-sm-3">Créé par</dt>
                <dd class="col-sm-9">{{ $folder->creator->name ?? 'Inconnu' }}</dd>

                <dt class="col-sm-3">Créé le</dt>
                <dd class="col-sm-9">{{ $folder->created_at?->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Modifié le</dt>
                <dd class="col-sm-9">{{ $folder->updated_at?->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Statistiques</dt>
                <dd class="col-sm-9">
                    {{ $folder->documents_count }} document(s),
                    {{ $folder->subfolders_count }} sous-dossier(s),
                    {{ number_format($folder->total_size / 1024 / 1024, 2) }} MB
                </dd>
            </dl>
        </div>
    </div>
</div>
@endsection
