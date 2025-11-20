@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Paramètres : {{ $workplace->name }}</h2>
                <a href="{{ route('workplaces.show', $workplace) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="list-group">
                        <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="list">Général</a>
                        <a href="#members" class="list-group-item list-group-item-action" data-bs-toggle="list">Membres</a>
                        <a href="#danger" class="list-group-item list-group-item-action text-danger" data-bs-toggle="list">Zone de danger</a>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="tab-content">
                        <!-- General Settings -->
                        <div class="tab-pane fade show active" id="general">
                            <div class="card">
                                <div class="card-header">Informations générales</div>
                                <div class="card-body">
                                    <p>Modifiez le nom, la description et les paramètres de visibilité de votre espace de travail.</p>
                                    <a href="{{ route('workplaces.edit', $workplace) }}" class="btn btn-primary">
                                        <i class="bi bi-pencil"></i> Modifier les informations
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Members Settings -->
                        <div class="tab-pane fade" id="members">
                            <div class="card">
                                <div class="card-header">Gestion des membres</div>
                                <div class="card-body">
                                    <p>Gérez les membres, les invitations et les rôles au sein de cet espace de travail.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $workplace->members_count }}</strong> membres actuels
                                        </div>
                                        <a href="{{ route('workplaces.members.index', $workplace) }}" class="btn btn-primary">
                                            <i class="bi bi-people"></i> Gérer les membres
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        <div class="tab-pane fade" id="danger">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">Zone de danger</div>
                                <div class="card-body">
                                    @if($workplace->status !== 'archived')
                                    <div class="mb-4">
                                        <h5>Archiver l'espace de travail</h5>
                                        <p class="text-muted">L'archivage rendra l'espace de travail en lecture seule pour tous les membres.</p>
                                        <form action="{{ route('workplaces.archive', $workplace) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir archiver cet espace de travail ?');">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bi bi-archive"></i> Archiver
                                            </button>
                                        </form>
                                    </div>
                                    <hr>
                                    @endif

                                    <div>
                                        <h5>Supprimer l'espace de travail</h5>
                                        <p class="text-muted">Cette action est irréversible. Toutes les données associées seront supprimées définitivement.</p>
                                        <form action="{{ route('workplaces.destroy', $workplace) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer DÉFINITIVEMENT cet espace de travail ? Cette action est irréversible.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-trash"></i> Supprimer définitivement
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
