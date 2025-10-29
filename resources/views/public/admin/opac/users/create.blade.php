@extends('layouts.app')

@section('title', 'Ajouter un utilisateur OPAC')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Ajouter un utilisateur OPAC</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.configurations.index') }}">OPAC</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.users.index') }}">Utilisateurs</a></li>
                        <li class="breadcrumb-item active">Ajouter</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-user-plus text-primary me-2"></i>
                        Informations de l'utilisateur
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.opac.users.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
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
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone1" class="form-label">Téléphone principal</label>
                                    <input type="tel" class="form-control @error('phone1') is-invalid @enderror"
                                           id="phone1" name="phone1" value="{{ old('phone1') }}">
                                    @error('phone1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone2" class="form-label">Téléphone secondaire</label>
                                    <input type="tel" class="form-control @error('phone2') is-invalid @enderror"
                                           id="phone2" name="phone2" value="{{ old('phone2') }}">
                                    @error('phone2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="3">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Minimum 8 caractères</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" value="1"
                                       {{ old('is_approved', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_approved">
                                    Approuver automatiquement cet utilisateur
                                </label>
                                <small class="form-text text-muted d-block">
                                    Si décoché, l'utilisateur devra être approuvé manuellement pour accéder au portail.
                                </small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.opac.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Créer l'utilisateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Informations
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-semibold">Accès au portail</h6>
                    <p class="text-muted mb-3">
                        Les utilisateurs approuvés pourront se connecter au portail public OPAC et :
                    </p>
                    <ul class="text-muted">
                        <li>Effectuer des recherches dans le catalogue</li>
                        <li>Faire des demandes de documents</li>
                        <li>S'inscrire aux événements</li>
                        <li>Laisser des commentaires et avis</li>
                        <li>Participer aux discussions</li>
                    </ul>

                    <hr>

                    <h6 class="fw-semibold">Sécurité</h6>
                    <p class="text-muted mb-0">
                        Le mot de passe sera automatiquement chiffré lors de la sauvegarde.
                        L'utilisateur pourra le modifier via son profil.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
