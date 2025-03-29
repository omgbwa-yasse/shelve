@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.index') }}">Tableaux d'affichage</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.show', $bulletinBoard->id) }}">{{ $bulletinBoard->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.events.index', $bulletinBoard->id) }}">Événements</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.events.show', [$bulletinBoard->id, $event->id]) }}">{{ $event->name }}</a></li>
                    <li class="breadcrumb-item active">Modifier</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Modifier l'événement</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('bulletin-boards.events.update', [$bulletinBoard->id, $event->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de l'événement</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $event->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description', $event->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Date de début</label>
                                <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $event->start_date->format('Y-m-d\TH:i')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Date de fin (optionnel)</label>
                                <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $event->end_date ? $event->end_date->format('Y-m-d\TH:i') : '') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Lieu (optionnel)</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $event->location) }}">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="draft" {{ old('status', $event->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="published" {{ old('status', $event->status) == 'published' ? 'selected' : '' }}>Publié</option>
                                <option value="cancelled" {{ old('status', $event->status) == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="attachments" class="form-label">Ajouter des pièces jointes</label>
                            <input type="file" class="form-control @error('attachments.*') is-invalid @enderror" id="attachments" name="attachments[]" multiple>
                            <div class="form-text">Vous pouvez sélectionner plusieurs fichiers (maximum 10MB par fichier).</div>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($event->attachments->isNotEmpty())
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Pièces jointes existantes</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Fichier</th>
                                                    <th>Type</th>
                                                    <th>Taille</th>
                                                    <th>Supprimer</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($event->attachments as $attachment)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2">
                                                                    <i class="fas {{ $attachment->getIconClass() }} fa-lg text-muted"></i>
                                                                </div>
                                                                <div>
                                                                    {{ $attachment->file_name }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ Str::upper(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) }}</td>
                                                        <td>{{ $attachment->getHumanReadableSize() }}</td>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="remove_attachments[]" value="{{ $attachment->id }}" id="attachment-{{ $attachment->id }}">
                                                                <label class="form-check-label" for="attachment-{{ $attachment->id }}">
                                                                    Supprimer
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard->id, $event->id]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
