@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Détails du retour d'expérience</h2>
                    <div>
                        <a href="{{ route('public.feedback.edit', $feedback) }}" class="btn btn-warning">Modifier</a>
                        <a href="{{ route('public.feedback.index') }}" class="btn btn-secondary">Retour à la liste</a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Titre :</strong> {{ $feedback->title }}
                        </div>
                        <div class="col-md-6">
                            <strong>Type :</strong>
                            @switch($feedback->type)
                                @case('bug')
                                    <span class="badge bg-danger">Bug</span>
                                    @break
                                @case('feature')
                                    <span class="badge bg-primary">Nouvelle fonctionnalité</span>
                                    @break
                                @case('improvement')
                                    <span class="badge bg-info">Amélioration</span>
                                    @break
                                @case('other')
                                    <span class="badge bg-secondary">Autre</span>
                                    @break
                                @default
                                    <span class="badge bg-light">{{ $feedback->type }}</span>
                            @endswitch
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Priorité :</strong>
                            @switch($feedback->priority)
                                @case('low')
                                    <span class="badge bg-success">Basse</span>
                                    @break
                                @case('medium')
                                    <span class="badge bg-warning">Moyenne</span>
                                    @break
                                @case('high')
                                    <span class="badge bg-danger">Haute</span>
                                    @break
                                @default
                                    <span class="badge bg-light">{{ $feedback->priority }}</span>
                            @endswitch
                        </div>
                        <div class="col-md-6">
                            <strong>Statut :</strong>
                            @switch($feedback->status)
                                @case('new')
                                    <span class="badge bg-primary">Nouveau</span>
                                    @break
                                @case('in_progress')
                                    <span class="badge bg-warning">En cours</span>
                                    @break
                                @case('resolved')
                                    <span class="badge bg-success">Résolu</span>
                                    @break
                                @case('closed')
                                    <span class="badge bg-secondary">Fermé</span>
                                    @break
                                @default
                                    <span class="badge bg-light">{{ $feedback->status }}</span>
                            @endswitch
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Créé par :</strong> {{ $feedback->user->name ?? 'Anonyme' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date de création :</strong> {{ $feedback->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Description :</strong>
                        <div class="mt-2 p-3 bg-light border rounded">
                            {{ $feedback->content }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <form action="{{ route('public.feedback.destroy', $feedback) }}" method="POST"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce retour ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>

                        <div>
                            <small class="text-muted">
                                Dernière modification : {{ $feedback->updated_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
