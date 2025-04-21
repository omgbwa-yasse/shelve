@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.index') }}">Tableaux d'affichage</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.show', $BulletinBoard['id']) }}">{{ $BulletinBoard['name'] }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.events.index', $BulletinBoard['id']) }}">Événements</a></li>
                    <li class="breadcrumb-item active">{{ $event->name }}</li>
                </ol>
            </nav>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-{{ $event->status == 'published' ? 'success' : ($event->status == 'draft' ? 'warning' : 'secondary') }} me-2">
                            {{ ucfirst($event->status) }}
                        </span>
                        <span class="text-muted">Événement</span>
                    </div>

                    
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-1"></i> Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('bulletin-boards.events.edit', [$BulletinBoard['id'], $event->id]) }}">
                                        <i class="fas fa-edit fa-fw me-1"></i> Modifier
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" id="show-import-form">
                                        <i class="fas fa-paperclip fa-fw me-1"></i> Ajouter des pièces jointes
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                                        <i class="fas fa-toggle-on fa-fw me-1"></i> Changer le statut
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" id="export-event">
                                        <i class="fas fa-file-export fa-fw me-1"></i> Exporter (iCal)
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('bulletin-boards.events.destroy', [$BulletinBoard['id'], $event->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                            <i class="fas fa-trash fa-fw me-1"></i> Supprimer
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>

                </div>
                <div class="card-body">
                    <h1 class="card-title mb-3">{{ $event->name }}</h1>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <i class="fas fa-calendar-alt fa-fw text-muted me-2"></i>
                                <strong>Date de début:</strong>
                                {{ $event->start_date->format('d/m/Y à H:i') }}
                            </div>

                            @if($event->end_date)
                                <div class="mb-3">
                                    <i class="fas fa-calendar-check fa-fw text-muted me-2"></i>
                                    <strong>Date de fin:</strong>
                                    {{ $event->end_date->format('d/m/Y à H:i') }}
                                </div>

                                <div class="mb-3">
                                    <i class="fas fa-hourglass-half fa-fw text-muted me-2"></i>
                                    <strong>Durée:</strong>
                                    @php
                                        $duration = $event->start_date->diff($event->end_date);
                                        $durationText = [];

                                        if ($duration->d > 0) {
                                            $durationText[] = $duration->d . ' jour' . ($duration->d > 1 ? 's' : '');
                                        }
                                        if ($duration->h > 0) {
                                            $durationText[] = $duration->h . ' heure' . ($duration->h > 1 ? 's' : '');
                                        }
                                        if ($duration->i > 0) {
                                            $durationText[] = $duration->i . ' minute' . ($duration->i > 1 ? 's' : '');
                                        }

                                        echo implode(', ', $durationText);
                                    @endphp
                                </div>
                            @endif

                            @if($event->location)
                                <div class="mb-3">
                                    <i class="fas fa-map-marker-alt fa-fw text-muted me-2"></i>
                                    <strong>Lieu:</strong>
                                    {{ $event->location }}
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <i class="fas fa-user fa-fw text-muted me-2"></i>
                                <strong>Créé par:</strong>
                                {{ $event->creator->name }}
                            </div>

                            <div class="mb-3">
                                <i class="fas fa-clock fa-fw text-muted me-2"></i>
                                <strong>Créé le:</strong>
                                {{ $event->created_at->format('d/m/Y à H:i') }}
                            </div>

                            <div class="mb-3">
                                <i class="fas fa-edit fa-fw text-muted me-2"></i>
                                <strong>Dernière modification:</strong>
                                {{ $event->updated_at->format('d/m/Y à H:i') }}
                            </div>

                            <div class="mb-3">
                                <i class="fas fa-calendar-day fa-fw text-muted me-2"></i>
                                <strong>Statut actuel:</strong>
                                <span class="badge bg-{{ $event->status == 'published' ? 'success' : ($event->status == 'draft' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Description</h5>
                        </div>
                        <div class="card-body">
                            <div class="event-description">
                                {!! nl2br(e($event->description)) !!}
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pièces jointes</h5>
                            @if($event->canBeEditedBy(Auth::user()))
                                <button type="button" class="btn btn-sm btn-outline-primary" id="show-import-form-alt">
                                    <i class="fas fa-paperclip me-1"></i> Ajouter des pièces jointes
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            <div id="import-form" class="card mb-3 d-none">
                                <div class="card-header">
                                    <h5 class="mb-0">Importer des pièces jointes</h5>
                                </div>
                                <div class="card-body">
                                    <div id="upload-feedback" class="mb-3"></div>
                                    
                                    <div class="mb-3">
                                        <label for="attachment-file" class="form-label">Fichier à importer</label>
                                        <input type="file" class="form-control" id="attachment-file">
                                    </div>
                                    <div class="mb-3">
                                        <label for="attachment-name" class="form-label">Nom du fichier (optionnel)</label>
                                        <input type="text" class="form-control" id="attachment-name" placeholder="Nom personnalisé">
                                        <div class="form-text">Si laissé vide, le nom original du fichier sera utilisé.</div>
                                    </div>
                                    
                                </div>
                                <div class="card-footer">
                                    <button type="button" class="btn btn-secondary" id="cancel-import">Annuler</button>
                                    <button type="button" class="btn btn-primary" id="import-button">Importer</button>
                                </div>
                            </div>

                            <div id="attachments-list">
                                @if($event->attachments->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Fichier</th>
                                                    <th>Type</th>
                                                    <th>Taille</th>
                                                    <th>Ajouté par</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($event->attachments as $attachment)
                                                    <tr id="attachment-{{ $attachment->id }}" class="attachment-row">
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    {{ $attachment->name }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ Str::upper(pathinfo($attachment->name, PATHINFO_EXTENSION)) }}</td>
                                                        <td>{{ number_format($attachment->size / 1024, 2) }} KB</td>
                                                        <td>{{ $attachment->creator->name }}</td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="{{ route('events.attachments.download', [$BulletinBoard['id'], $event->id, $attachment->id]) }}" class="btn btn-outline-primary" target="_blank">
                                                                    <i class="fas fa-download"></i> Télécharger
                                                                </a>
                                                                <a href="{{ route('events.attachments.preview', [$BulletinBoard['id'], $event->id, $attachment->id]) }}" class="btn btn-outline-info" target="_blank">
                                                                    <i class="fas fa-eye"></i> Lire
                                                                </a>
                                                                    <button type="button" class="btn btn-outline-danger delete-attachment" data-attachment-id="{{ $attachment->id }}">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> Aucune pièce jointe n'est associée à cet événement.
                                        @if($event->canBeEditedBy(Auth::user()))
                                            <button type="button" class="btn btn-link p-0 alert-link" id="show-import-form-empty">
                                                Ajouter des pièces jointes
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between">

                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-share-alt me-1"></i> Partager
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" id="copy-link"><i class="fas fa-link me-2"></i> Copier le lien</a></li>
                                <li><a class="dropdown-item" href="mailto:?subject={{ urlencode($event->name) }}&body={{ urlencode(route('bulletin-boards.events.show', [$BulletinBoard['id'], $event->id])) }}"><i class="fas fa-envelope me-2"></i> Email</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" id="add-to-calendar"><i class="fas fa-calendar-plus me-2"></i> Ajouter au calendrier</a></li>
                            </ul>
                        </div>

                        @if($event->canBeEditedBy(Auth::user()))
                            <a href="{{ route('bulletin-boards.events.edit', [$BulletinBoard['id'], $event->id]) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de changement de statut -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">Changer le statut de l'événement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('bulletin-boards.events.updateStatus', [$BulletinBoard['id'], $event->id]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Nouveau statut</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="status" id="modal-status-draft" value="draft" {{ $event->status == 'draft' ? 'checked' : '' }}>
                            <label class="btn btn-outline-warning" for="modal-status-draft">
                                <i class="fas fa-pencil-alt me-1"></i> Brouillon
                            </label>

                            <input type="radio" class="btn-check" name="status" id="modal-status-published" value="published" {{ $event->status == 'published' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success" for="modal-status-published">
                                <i class="fas fa-check-circle me-1"></i> Publié
                            </label>

                            <input type="radio" class="btn-check" name="status" id="modal-status-cancelled" value="cancelled" {{ $event->status == 'cancelled' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="modal-status-cancelled">
                                <i class="fas fa-ban me-1"></i> Annulé
                            </label>
                        </div>
                    </div>

                    <div class="form-text text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i> Changer le statut affectera la visibilité de l'événement pour les autres utilisateurs.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Changer le statut</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/events.js') }}"></script>

@endsection