@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Créer un nouvel espace de travail</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('workplaces.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror"
                                    id="category_id" name="category_id" required>
                                <option value="">Sélectionner une catégorie</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    @if($category->icon)
                                    <i class="bi bi-{{ $category->icon }}"></i>
                                    @endif
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="max_members" class="form-label">Nombre max de membres</label>
                                <input type="number" class="form-control @error('max_members') is-invalid @enderror"
                                       id="max_members" name="max_members" value="{{ old('max_members', 50) }}" min="1">
                                @error('max_members')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Laisser vide pour illimité</small>
                            </div>

                            <div class="col-md-6">
                                <label for="max_storage_mb" class="form-label">Stockage max (MB)</label>
                                <input type="number" class="form-control @error('max_storage_mb') is-invalid @enderror"
                                       id="max_storage_mb" name="max_storage_mb" value="{{ old('max_storage_mb', 1000) }}" min="1">
                                @error('max_storage_mb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Laisser vide pour illimité</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                                       {{ old('is_public') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_public">
                                    Espace de travail public
                                </label>
                                <small class="d-block text-muted">Visible par tous les membres de l'organisation</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow_external_sharing"
                                       name="allow_external_sharing" value="1" {{ old('allow_external_sharing') ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_external_sharing">
                                    Autoriser le partage externe
                                </label>
                                <small class="d-block text-muted">Permet d'inviter des utilisateurs externes à l'organisation</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('workplaces.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Créer l'espace de travail</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
