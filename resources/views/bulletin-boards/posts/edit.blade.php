@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.index') }}">Tableaux d'affichage</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.show', $bulletinBoard->id) }}">{{ $bulletinBoard->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.posts.index', $bulletinBoard->id) }}">Publications</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.posts.show', [$bulletinBoard->id, $post->id]) }}">{{ $post->name }}</a></li>
                    <li class="breadcrumb-item active">Modifier</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Modifier la publication</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('bulletin-boards.posts.update', [$bulletinBoard->id, $post->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Titre de la publication</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $post->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Contenu</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="6" required>{{ old('description', $post->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Date de début</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $post->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Date de fin (optionnel)</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $post->end_date ? $post->end_date->format('Y-m-d') : '') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="status" id="status-draft" value="draft" {{ old('status', $post->status) == 'draft' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning" for="status-draft">
                                    <i class="fas fa-pencil-alt me-1"></i> Brouillon
                                </label>

                                <input type="radio" class="btn-check" name="status" id="status-published" value="published" {{ old('status', $post->status) == 'published' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success" for="status-published">
                                    <i class="fas fa-check-circle me-1"></i> Publié
                                </label>

                                <input type="radio" class="btn-check" name="status" id="status-cancelled" value="cancelled" {{ old('status', $post->status) == 'cancelled' ? 'checked' : '' }}>
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
                            <label class="form-check-label" for="add_attachments">Ajouter des pièces jointes après la mise à jour</label>
                        </div>

                        @if($post->attachments->isNotEmpty())
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Pièces jointes existantes</h5>
                                    <a href="{{ route('posts.attachments.index', $post->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-paperclip me-1"></i> Gérer les pièces jointes
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Fichier</th>
                                                    <th>Type</th>
                                                    <th>Taille</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($post->attachments as $attachment)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    {{ $attachment->name }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ Str::upper(pathinfo($attachment->name, PATHINFO_EXTENSION)) }}</td>
                                                        <td>{{ number_format($attachment->size / 1024, 2) }} KB</td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-outline-primary" target="_blank">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                                <a href="{{ route('attachments.preview', $attachment->id) }}" class="btn btn-outline-info" target="_blank">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i> Aucune pièce jointe n'est associée à cette publication.
                                <a href="{{ route('posts.attachments.create', $post->id) }}" class="alert-link">Ajouter des pièces jointes</a>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('bulletin-boards.posts.show', [$bulletinBoard->id, $post->id]) }}" class="btn btn-outline-secondary">
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
