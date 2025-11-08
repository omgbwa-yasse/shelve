@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-person-plus"></i> {{ __('Nouveau lecteur') }}</h1>
        <a href="{{ route('library.readers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour à la liste') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('library.readers.store') }}">
                @csrf

                <!-- Informations personnelles -->
                <h5 class="mb-3"><i class="bi bi-person"></i> {{ __('Informations personnelles') }}</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="phone" class="form-label">{{ __('Téléphone') }}</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label">{{ __('Adresse') }}</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror"
                               id="address" name="address" value="{{ old('address') }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Informations de lecteur -->
                <h5 class="mb-3 mt-4"><i class="bi bi-card-checklist"></i> {{ __('Informations de lecteur') }}</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="card_number" class="form-label">{{ __('Numéro de carte') }}</label>
                        <input type="text" class="form-control @error('card_number') is-invalid @enderror"
                               id="card_number" name="card_number" value="{{ old('card_number') }}"
                               placeholder="{{ __('Généré automatiquement si vide') }}">
                        @error('card_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="category" class="form-label">{{ __('Catégorie') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('category') is-invalid @enderror"
                                id="category" name="category" required>
                            <option value="">{{ __('Sélectionner...') }}</option>
                            <option value="student" {{ old('category') == 'student' ? 'selected' : '' }}>{{ __('Étudiant') }}</option>
                            <option value="teacher" {{ old('category') == 'teacher' ? 'selected' : '' }}>{{ __('Enseignant') }}</option>
                            <option value="staff" {{ old('category') == 'staff' ? 'selected' : '' }}>{{ __('Personnel') }}</option>
                            <option value="external" {{ old('category') == 'external' ? 'selected' : '' }}>{{ __('Externe') }}</option>
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="card_expiry" class="form-label">{{ __('Date d\'expiration de la carte') }} <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('card_expiry') is-invalid @enderror"
                               id="card_expiry" name="card_expiry"
                               value="{{ old('card_expiry', now()->addYear()->format('Y-m-d')) }}" required>
                        @error('card_expiry')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="is_active" class="form-label">{{ __('Statut') }}</label>
                        <select class="form-select @error('is_active') is-invalid @enderror"
                                id="is_active" name="is_active">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('library.readers.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ __('Créer le lecteur') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
