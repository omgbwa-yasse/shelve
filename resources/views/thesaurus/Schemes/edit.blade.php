@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Modifier le schéma de thésaurus</h3>
                    <div class="card-tools">
                        <a href="{{ route('thesaurus.schemes.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('thesaurus.schemes.update', $scheme->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group row">
                            <label for="identifier" class="col-sm-2 col-form-label">Identifiant</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control @error('identifier') is-invalid @enderror"
                                       id="identifier" name="identifier" value="{{ old('identifier', $scheme->identifier) }}" required>
                                @error('identifier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Identifiant unique du schéma (ex: MATIERE, LIEUX)</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="title" class="col-sm-2 col-form-label">Titre</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title', $scheme->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="description" class="col-sm-2 col-form-label">Description</label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3">{{ old('description', $scheme->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="language" class="col-sm-2 col-form-label">Langue par défaut</label>
                            <div class="col-sm-10">
                                <select class="form-control @error('language') is-invalid @enderror"
                                        id="language" name="language" required>
                                    <option value="fr-fr" {{ old('language', $scheme->language) == 'fr-fr' ? 'selected' : '' }}>Français (fr-fr)</option>
                                    <option value="en-us" {{ old('language', $scheme->language) == 'en-us' ? 'selected' : '' }}>Anglais (en-us)</option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="uri" class="col-sm-2 col-form-label">URI</label>
                            <div class="col-sm-10">
                                <input type="url" class="form-control @error('uri') is-invalid @enderror"
                                       id="uri" name="uri" value="{{ old('uri', $scheme->uri) }}" required>
                                @error('uri')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if ($scheme->namespace)
                            <div class="form-group row">
                                <label for="namespace_uri" class="col-sm-2 col-form-label">URI d'espace de noms</label>
                                <div class="col-sm-10">
                                    <input type="url" class="form-control @error('namespace_uri') is-invalid @enderror"
                                           id="namespace_uri" name="namespace_uri" value="{{ old('namespace_uri', $scheme->namespace->namespace_uri) }}">
                                    @error('namespace_uri')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @else
                            <div class="form-group row">
                                <label for="namespace_uri" class="col-sm-2 col-form-label">URI d'espace de noms</label>
                                <div class="col-sm-10">
                                    <input type="url" class="form-control @error('namespace_uri') is-invalid @enderror"
                                           id="namespace_uri" name="namespace_uri" value="{{ old('namespace_uri') }}">
                                    @error('namespace_uri')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">URI de base pour les concepts de ce schéma</small>
                                </div>
                            </div>
                        @endif

                        <div class="form-group row">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                                <a href="{{ route('thesaurus.schemes.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
