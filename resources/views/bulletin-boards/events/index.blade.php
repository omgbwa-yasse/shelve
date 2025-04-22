@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Événements - {{ $bulletinBoard->name }}</h1>
            <p class="text-muted">{{ $bulletinBoard->description }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('bulletin-boards.events.create', $bulletinBoard) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvel événement
            </a>
            <a href="{{ route('bulletin-boards.show', $bulletinBoard) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('bulletin-boards.events.index', $bulletinBoard) }}" class="row g-3">
                <div class="col-md-4">
                    <select name="period" class="form-select">
                        <option value="">Toutes les périodes</option>
                        <option value="upcoming" {{ request('period') == 'upcoming' ? 'selected' : '' }}>À venir</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="organisation" class="form-select">
                        <option value="">Toutes les organisations</option>
                        @foreach($organisations as $organisation)
                            <option value="{{ $organisation->id }}" {{ request('organisation') == $organisation->id ? 'selected' : '' }}>
                                {{ $organisation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                    <a href="{{ route('bulletin-boards.events.index', $bulletinBoard) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if($events->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Date de début</th>
                                <th>Date de fin</th>
                                <th>Lieu</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                                <tr>
                                    <td>
                                        <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard, $event]) }}">
                                            {{ $event->name }}
                                        </a>
                                    </td>
                                    <td>{{ $event->start_date->format('d/m/Y H:i') }}</td>
                                    <td>{{ $event->end_date ? $event->end_date->format('d/m/Y H:i') : '-' }}</td>
                                    <td>{{ $event->location ?: '-' }}</td>
                                    <td>
                                        @if($event->status == 'published')
                                            <span class="badge bg-success">Publié</span>
                                        @elseif($event->status == 'draft')
                                            <span class="badge bg-warning">Brouillon</span>
                                        @elseif($event->status == 'cancelled')
                                            <span class="badge bg-danger">Annulé</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard, $event]) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('bulletin-boards.events.edit', [$bulletinBoard, $event]) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $event->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $events->links() }}
            @else
                <div class="text-center p-4">
                    <p>Aucun événement trouvé.</p>
                    <a href="{{ route('bulletin-boards.events.create', $bulletinBoard) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer un événement
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($events as $event)
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal{{ $event->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $event->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel{{ $event->id }}">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir supprimer l'événement "{{ $event->name }}" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('bulletin-boards.events.destroy', [$bulletinBoard, $event]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
