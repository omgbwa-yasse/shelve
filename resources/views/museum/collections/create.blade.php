@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-plus-circle"></i> {{ __('Nouvelle collection') }}</h1>
        <a href="{{ route('museum.collections.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('museum.collections.store') }}" method="POST">
                @csrf

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    {{ __('Note: Les collections sont automatiquement créées à partir des catégories d\'artefacts. Vous pouvez créer un nouvel artefact avec une nouvelle catégorie pour créer une collection.') }}
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nom de la collection') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('Exemple: Peintures, Sculptures, Céramiques, etc.') }}
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="code" class="form-label">{{ __('Code de la collection') }}</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                   id="code" name="code" value="{{ old('code') }}">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('Exemple: PEIN, SCUL, CERA') }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="period" class="form-label">{{ __('Période') }}</label>
                            <input type="text" class="form-control @error('period') is-invalid @enderror"
                                   id="period" name="period" value="{{ old('period') }}">
                            @error('period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('Exemple: XVIIIe siècle, Époque contemporaine') }}
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="origin" class="form-label">{{ __('Origine géographique') }}</label>
                            <input type="text" class="form-control @error('origin') is-invalid @enderror"
                                   id="origin" name="origin" value="{{ old('origin') }}">
                            @error('origin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('museum.collections.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ __('Créer la collection') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
