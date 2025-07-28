@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Rooms</h1>
        <a href="{{ route('rooms.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Ajouter une salle
        </a>

        <div id="roomList">
            @foreach ($rooms as $room)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5 class="card-title mb-2">
                                    <b>{{ $room->code ?? 'N/A' }}</b> - {{ $room->name ?? 'N/A' }} (ID: {{ $room->id ?? 'N/A' }})
                                    <span class="badge bg-{{ $room->visibility == 'public' ? 'success' : ($room->visibility == 'private' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($room->visibility ?? 'N/A') }}
                                    </span>
                                </h5>
                                <p class="card-text mb-1">
                                    <i class="bi bi-file-earmark-text"></i> <strong>Description:</strong> {{ $room->description ?? 'N/A' }}<br>
                                    <i class="bi bi-building"></i> <strong> Niveau:</strong> {{ $room->floor->name ?? 'N/A' }}<br>
                                    <i class="bi bi-map"></i> <strong> Bâtiment:</strong> {{ $room->floor->building->name ?? 'N/A' }} <br>
                                    <i class="bi bi-eye"></i> <strong>Visibilité:</strong>
                                    @switch($room->visibility)
                                        @case('public')
                                            <span class="text-success">Public</span>
                                            @break
                                        @case('private')
                                            <span class="text-danger">Privé</span>
                                            @break
                                        @case('inherit')
                                            <span class="text-warning">Hériter ({{ $room->getEffectiveVisibility() }})</span>
                                            @break
                                        @default
                                            <span class="text-muted">N/A</span>
                                    @endswitch<br>
                                    <i class="bi bi-gear"></i> <strong>Type de salle :</strong>
                                    @switch($room->type)
                                        @case('archives')
                                            <span class="text-primary">Salle d'archives</span>
                                            @break
                                        @case('producer')
                                            <span class="text-info">Local tampon (service producteur)</span>
                                            @break
                                        @default
                                            <span class="text-muted">N/A</span>
                                    @endswitch
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end text-center">
                                <div class="d-flex justify-content-md-end justify-content-center align-items-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('rooms.show', $room->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette salle?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
