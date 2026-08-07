@extends('layouts.app')

@section('title', 'Modifier utilisateur OPAC')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Modifier {{ $user->first_name }} {{ $user->name }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.configurations.index') }}">OPAC</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.users.index') }}">Utilisateurs</a></li>
                        <li class="breadcrumb-item active">Modifier {{ $user->first_name }} {{ $user->name }}</li>
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
                        <i class="fas fa-user-edit text-primary me-2"></i>
                        Modifier les informations
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.opac.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone1" class="form-label">Téléphone principal</label>
                                    <input type="tel" class="form-control @error('phone1') is-invalid @enderror"
                                           id="phone1" name="phone1" value="{{ old('phone1', $user->phone1) }}">
                                    @error('phone1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone2" class="form-label">Téléphone secondaire</label>
                                    <input type="tel" class="form-control @error('phone2') is-invalid @enderror"
                                           id="phone2" name="phone2" value="{{ old('phone2', $user->phone2) }}">
                                    @error('phone2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-semibold">Changer le mot de passe</h6>
                            <p class="text-muted small">Laissez vide pour conserver le mot de passe actuel</p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Nouveau mot de passe</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="password" name="password">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Minimum 8 caractères</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                        <input type="password" class="form-control"
                                               id="password_confirmation" name="password_confirmation">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" value="1"
                                       {{ old('is_approved', $user->is_approved) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_approved">
                                    Utilisateur approuvé
                                </label>
                                <small class="form-text text-muted d-block">
                                    Décochez pour révoquer l'accès au portail public.
                                </small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.opac.users.show', $user) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Sauvegarder les modifications
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
                        Informations système
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Créé le :</small>
                        <div>{{ $user->created_at->format('d/m/Y à H:i') }}</div>
                        <small class="text-muted">({{ $user->created_at->diffForHumans() }})</small>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Dernière modification :</small>
                        <div>{{ $user->updated_at->format('d/m/Y à H:i') }}</div>
                        <small class="text-muted">({{ $user->updated_at->diffForHumans() }})</small>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">ID utilisateur :</small>
                        <div><code>{{ $user->id }}</code></div>
                    </div>

                    <hr>

                    <h6 class="fw-semibold">Activité</h6>
                    <ul class="list-unstyled">
                        <li><strong>{{ $user->documentRequests()->count() }}</strong> demandes de documents</li>
                        <li><strong>{{ $user->feedbacks()->count() }}</strong> commentaires/avis</li>
                        <li><strong>{{ $user->eventRegistrations()->count() }}</strong> inscriptions événements</li>
                        <li><strong>{{ $user->searchLogs()->count() }}</strong> recherches effectuées</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt text-warning me-2"></i>
                        Actions dangereuses
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="text-danger">Supprimer l'utilisateur</h6>
                    <p class="text-muted small">
                        Cette action est irréversible. Toutes les données associées à cet utilisateur seront supprimées.
                    </p>
                    <form action="{{ route('admin.opac.users.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('Êtes-vous absolument sûr de vouloir supprimer cet utilisateur ? Cette action ne peut pas être annulée.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash me-1"></i> Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
