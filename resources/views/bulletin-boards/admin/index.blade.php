<!-- resources/views/bulletin-boards/admin/index.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Administration du babillard</h2>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Publications totales</h6>
                        <h3 class="mb-0">{{ $stats['total_posts'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Événements actifs</h6>
                        <h3 class="mb-0">{{ $stats['total_events'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Utilisateurs</h6>
                        <h3 class="mb-0">{{ $stats['total_users'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paramètres -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Paramètres généraux</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('bulletin-boards.admin.settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Commentaires</label>
                                <div class="form-check">
                                    <input type="checkbox"
                                           name="allow_comments"
                                           class="form-check-input"
                                           value="1"
                                        {{ setting('bulletin_board.allow_comments', true) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Autoriser les commentaires
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Modération</label>
                                <div class="form-check">
                                    <input type="checkbox"
                                           name="moderation_required"
                                           class="form-check-input"
                                           value="1"
                                        {{ setting('bulletin_board.moderation_required', false) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Modération requise pour les publications
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Taille maximale des fichiers (MB)</label>
                                <input type="number"
                                       name="max_file_size"
                                       class="form-control"
                                       value="{{ setting('bulletin_board.max_file_size', 5) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Types de fichiers autorisés</label>
                                <input type="text"
                                       name="allowed_file_types"
                                       class="form-control"
                                       value="{{ implode(',', setting('bulletin_board.allowed_file_types', ['pdf','doc','docx','jpg','png'])) }}"
                                       placeholder="pdf,doc,docx,jpg,png">
                                <small class="text-muted">Séparez les extensions par des virgules</small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Enregistrer les paramètres
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Activités récentes -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Activités récentes</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @foreach($stats['recent_activities'] as $activity)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $activity->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                Par {{ $activity->user->name }}
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
