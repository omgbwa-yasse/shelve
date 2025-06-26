@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Modifier le retour d'expérience</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('public.feedback.update', $feedback) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="title">Titre</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title', $feedback->title) }}" required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="type">Type de retour</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="bug" {{ old('type', $feedback->type) == 'bug' ? 'selected' : '' }}>Bug</option>
                                <option value="feature" {{ old('type', $feedback->type) == 'feature' ? 'selected' : '' }}>Nouvelle fonctionnalité</option>
                                <option value="improvement" {{ old('type', $feedback->type) == 'improvement' ? 'selected' : '' }}>Amélioration</option>
                                <option value="other" {{ old('type', $feedback->type) == 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="content">Description</label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="6" required>{{ old('content', $feedback->content) }}</textarea>
                            @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="priority">Priorité</label>
                            <select class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                <option value="low" {{ old('priority', $feedback->priority) == 'low' ? 'selected' : '' }}>Basse</option>
                                <option value="medium" {{ old('priority', $feedback->priority) == 'medium' ? 'selected' : '' }}>Moyenne</option>
                                <option value="high" {{ old('priority', $feedback->priority) == 'high' ? 'selected' : '' }}>Haute</option>
                            </select>
                            @error('priority')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="status">Statut</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="new" {{ old('status', $feedback->status) == 'new' ? 'selected' : '' }}>Nouveau</option>
                                <option value="in_progress" {{ old('status', $feedback->status) == 'in_progress' ? 'selected' : '' }}>En cours</option>
                                <option value="resolved" {{ old('status', $feedback->status) == 'resolved' ? 'selected' : '' }}>Résolu</option>
                                <option value="closed" {{ old('status', $feedback->status) == 'closed' ? 'selected' : '' }}>Fermé</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="{{ route('public.feedback.show', $feedback) }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
