@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.index') }}">Tableaux d'affichage</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.show', $BulletinBoard['id']) }}">{{ $BulletinBoard->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.events.index', $BulletinBoard['id']) }}">Événements</a></li>
                    <li class="breadcrumb-item active">Créer</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Créer un nouvel événement</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('bulletin-boards.events.store', $BulletinBoard['id']) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de l'événement</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Date de début</label>
                                <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Date de fin (optionnel)</label>
                                <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Lieu (optionnel)</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location') }}">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="status" id="status-draft" value="draft" {{ old('status') == 'draft' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning" for="status-draft">
                                    <i class="fas fa-pencil-alt me-1"></i> Brouillon
                                </label>

                                <input type="radio" class="btn-check" name="status" id="status-published" value="published" {{ old('status', 'published') == 'published' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success" for="status-published">
                                    <i class="fas fa-check-circle me-1"></i> Publié
                                </label>

                                <input type="radio" class="btn-check" name="status" id="status-cancelled" value="cancelled" {{ old('status') == 'cancelled' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary" for="status-cancelled">
                                    <i class="fas fa-ban me-1"></i> Annulé
                                </label>
                            </div>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="add_attachments" name="add_attachments" {{ old('add_attachments') ? 'checked' : '' }}>
                            <label class="form-check-label" for="add_attachments">Ajouter des pièces jointes après la création</label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('bulletin-boards.events.index', $BulletinBoard['id']) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Créer l'événement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
