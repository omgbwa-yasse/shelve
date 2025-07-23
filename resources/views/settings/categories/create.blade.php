@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('Créer une catégorie') }}</h1>
        <a href="{{ route('settings.categories.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>{{ __('Retour') }}
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informations de la catégorie') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.categories.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">{{ __('Catégorie parent') }}</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror"
                                    id="parent_id" name="parent_id">
                                <option value="">{{ __('Aucune (catégorie racine)') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('settings.categories.index') }}" class="btn btn-secondary">
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
