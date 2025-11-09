@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Modifier l'espace de travail</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('workplaces.update', $workplace) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $workplace->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $workplace->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror"
                                    id="category_id" name="category_id" required>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $workplace->category_id) == $category->id ? 'selected' : '' }}>
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
                                       id="max_members" name="max_members" value="{{ old('max_members', $workplace->max_members) }}" min="1">
                                @error('max_members')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="max_storage_mb" class="form-label">Stockage max (MB)</label>
                                <input type="number" class="form-control @error('max_storage_mb') is-invalid @enderror"
                                       id="max_storage_mb" name="max_storage_mb" value="{{ old('max_storage_mb', $workplace->max_storage_mb) }}" min="1">
                                @error('max_storage_mb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                                       {{ old('is_public', $workplace->is_public) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_public">
                                    Espace de travail public
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow_external_sharing"
                                       name="allow_external_sharing" value="1" {{ old('allow_external_sharing', $workplace->allow_external_sharing) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_external_sharing">
                                    Autoriser le partage externe
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('workplaces.show', $workplace) }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
