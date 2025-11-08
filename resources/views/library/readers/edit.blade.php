@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-pencil"></i> {{ __('Modifier le lecteur') }}</h1>
        <a href="{{ route('library.readers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour à la liste') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="#">
                @csrf
                @method('PUT')

                <!-- Informations personnelles -->
                <h5 class="mb-3"><i class="bi bi-person"></i> {{ __('Informations personnelles') }}</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="phone" class="form-label">{{ __('Téléphone') }}</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label">{{ __('Adresse') }}</label>
                        <input type="text" class="form-control" id="address" name="address">
                    </div>
                </div>

                <!-- Informations de lecteur -->
                <h5 class="mb-3 mt-4"><i class="bi bi-card-checklist"></i> {{ __('Informations de lecteur') }}</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="card_number" class="form-label">{{ __('Numéro de carte') }}</label>
                        <input type="text" class="form-control" id="card_number" name="card_number" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="category" class="form-label">{{ __('Catégorie') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="student">{{ __('Étudiant') }}</option>
                            <option value="teacher">{{ __('Enseignant') }}</option>
                            <option value="staff">{{ __('Personnel') }}</option>
                            <option value="external">{{ __('Externe') }}</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="card_expiry" class="form-label">{{ __('Date d\'expiration') }} <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="card_expiry" name="card_expiry" required>
                    </div>
                    <div class="col-md-6">
                        <label for="is_active" class="form-label">{{ __('Statut') }}</label>
                        <select class="form-select" id="is_active" name="is_active">
                            <option value="1">{{ __('Actif') }}</option>
                            <option value="0">{{ __('Inactif') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('library.readers.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ __('Enregistrer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
