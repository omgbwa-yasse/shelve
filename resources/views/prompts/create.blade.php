@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ isset($prompt) ? 'Modifier le prompt' : 'Nouveau prompt' }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ isset($prompt) ? route('prompts.update', $prompt) : route('prompts.store') }}"
                          method="POST">
                        @csrf
                        @if(isset($prompt))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $prompt->name ?? '') }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="instruction" class="form-label">Instructions</label>
                            <textarea class="form-control @error('instruction') is-invalid @enderror"
                                      id="instruction"
                                      name="instruction"
                                      rows="5"
                                      required>{{ old('instruction', $prompt->instruction ?? '') }}</textarea>
                            @error('instruction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="is_public"
                                           name="is_public"
                                           value="1"
                                           {{ old('is_public', $prompt->is_public ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_public">Public</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="is_draft"
                                           name="is_draft"
                                           value="1"
                                           {{ old('is_draft', $prompt->is_draft ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_draft">Brouillon</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('prompts.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> {{ isset($prompt) ? 'Mettre à jour' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
