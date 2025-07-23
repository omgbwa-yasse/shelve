@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('Créer un paramètre') }}</h1>
        <a href="{{ route('settings.definitions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>{{ __('Retour') }}
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informations du paramètre') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.definitions.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Nom unique du paramètre (ex: app_name, max_file_size)') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">{{ __('Catégorie') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror"
                                    id="category_id" name="category_id" required>
                                <option value="">{{ __('Sélectionner une catégorie') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror"
                                    id="type" name="type" required>
                                <option value="">{{ __('Sélectionner un type') }}</option>
                                <option value="string" {{ old('type') == 'string' ? 'selected' : '' }}>{{ __('Chaîne de caractères') }}</option>
                                <option value="integer" {{ old('type') == 'integer' ? 'selected' : '' }}>{{ __('Nombre entier') }}</option>
                                <option value="float" {{ old('type') == 'float' ? 'selected' : '' }}>{{ __('Nombre décimal') }}</option>
                                <option value="boolean" {{ old('type') == 'boolean' ? 'selected' : '' }}>{{ __('Booléen (Vrai/Faux)') }}</option>
                                <option value="json" {{ old('type') == 'json' ? 'selected' : '' }}>{{ __('JSON') }}</option>
                                <option value="array" {{ old('type') == 'array' ? 'selected' : '' }}>{{ __('Tableau') }}</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="default_value" class="form-label">{{ __('Valeur par défaut') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('default_value') is-invalid @enderror"
                                      id="default_value" name="default_value" rows="3" required>{{ old('default_value') }}</textarea>
                            @error('default_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Valeur au format JSON (ex: "texte", 123, true, {"key": "value"})') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="constraints" class="form-label">{{ __('Contraintes') }}</label>
                            <textarea class="form-control @error('constraints') is-invalid @enderror"
                                      id="constraints" name="constraints" rows="3">{{ old('constraints') }}</textarea>
                            @error('constraints')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Contraintes au format JSON (ex: {"min": 1, "max": 100, "options": ["a", "b"]})') }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_system" name="is_system" value="1"
                                       {{ old('is_system') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_system">
                                    {{ __('Paramètre système') }}
                                </label>
                            </div>
                            <div class="form-text">{{ __('Les paramètres système ne peuvent être modifiés que par les administrateurs') }}</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('settings.definitions.index') }}" class="btn btn-secondary">
                                {{ __('Annuler') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>{{ __('Créer') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
