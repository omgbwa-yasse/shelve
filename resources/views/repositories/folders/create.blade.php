@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Créer un nouveau dossier numérique</h1>
        <a href="{{ route('folders.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('folders.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
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
                                    <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>
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
                                @foreach($parentFolders as $folder)
                                    <option value="{{ $folder->id }}"
                                        {{ (old('parent_id', $parentId) == $folder->id) ? 'selected' : '' }}>
                                        {{ $folder->name }} ({{ $folder->type->name }})
                                    </option>
                                @endforeach
                            </select>
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
                                        {{ old('organisation_id', auth()->user()->organisation_id) == $organisation->id ? 'selected' : '' }}>
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
                              id="description" name="description" rows="4">{{ old('description') }}</textarea>
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
                                <option value="public" {{ old('access_level', 'public') == 'public' ? 'selected' : '' }}>Public</option>
                                <option value="internal" {{ old('access_level') == 'internal' ? 'selected' : '' }}>Interne</option>
                                <option value="confidential" {{ old('access_level') == 'confidential' ? 'selected' : '' }}>Confidentiel</option>
                                <option value="secret" {{ old('access_level') == 'secret' ? 'selected' : '' }}>Secret</option>
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
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Fermé</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archivé</option>
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
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
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
                                   id="start_date" name="start_date" value="{{ old('start_date') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Date de fin</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                   id="end_date" name="end_date" value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="requires_approval"
                           name="requires_approval" value="1" {{ old('requires_approval') ? 'checked' : '' }}>
                    <label class="form-check-label" for="requires_approval">
                        Nécessite une approbation
                    </label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('folders.index') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Créer le dossier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
