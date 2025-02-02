<!-- resources/views/bulletin-boards/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>{{ $bulletinBoard->name }}</h1>
            <p class="text-muted">{{ $bulletinBoard->description }}</p>
        </div>
        <div class="col-md-4 text-end">
            @can('update', $bulletinBoard)
            <a href="{{ route('bulletin-boards.edit', $bulletinBoard) }}" class="btn btn-primary me-2">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            @endcan
            @can('delete', $bulletinBoard)
            <form action="{{ route('bulletin-boards.destroy', $bulletinBoard) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce tableau ?')">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </form>
            @endcan
        </div>
    </div>

    <div class="row">
        <!-- Événements récents -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Événements récents</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">Tous les événements</a>
                </div>
                <div class="card-body">
                    @forelse($bulletinBoard->events as $event)
                    <div class="mb-3">
                        <h6>{{ $event->title }}</h6>
                        <p class="text-muted mb-1">
                            <i class="bi bi-clock"></i> {{ $event->start_date->format('d/m/Y H:i') }}
                        </p>
                        <p class="mb-0">{{ Str::limit($event->description, 100) }}</p>
                    </div>
                    @empty
                    <p class="text-muted">Aucun événement récent.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Fichiers attachés -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-paperclip"></i> Fichiers récents</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">Tous les fichiers</a>
                </div>
                <div class="card-body">
                    @forelse($bulletinBoard->attachments as $attachment)
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-file-earmark me-2"></i>
                        <div>
                            <h6 class="mb-0">{{ $attachment->name }}</h6>
                            <small class="text-muted">
                                {{ number_format($attachment->size / 1024, 2) }} KB
                            </small>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted">Aucun fichier attaché.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Organisations -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Organisations</h5>
                </div>
                <div class="card-body">
                    @forelse($bulletinBoard->organisations as $organisation)
                    <div class="mb-2">
                        <span class="badge bg-secondary">{{ $organisation->name }}</span>
                        <small class="text-muted">({{ $organisation->pivot->access_level }})</small>
                    </div>
                    @empty
                    <p class="text-muted">Aucune organisation associée.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Administrateurs -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Administrateurs</h5>
                </div>
                <div class="card-body">
                    @forelse($bulletinBoard->administrators as $admin)
                    <div class="mb-2">
                        {{ $admin->name }}
                        <span class="badge bg-primary">{{ $admin->pivot->role }}</span>
                    </div>
                    @empty
                    <p class="text-muted">Aucun administrateur défini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
