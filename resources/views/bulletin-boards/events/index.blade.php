@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.index') }}">Tableaux d'affichage</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.show', $bulletinBoard['id']) }}">{{ $bulletinBoard->name }}</a></li>
                    <li class="breadcrumb-item active">Événements</li>
                </ol>
            </nav>
            <h1>Liste des événements</h1>
            <p class="text-muted">{{ $bulletinBoard->name }}</p>
        </div>
        <div class="col-lg-4 text-lg-end d-flex justify-content-lg-end align-items-center">
            @if($bulletinBoard->hasWritePermission(Auth::id()))
                <a href="{{ route('bulletin-boards.events.create', $bulletinBoard['id']) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Créer un événement
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="eventTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" href="#upcoming" role="tab" aria-controls="upcoming" aria-selected="true">
                        <i class="fas fa-calendar-day me-1"></i> À venir
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="past-tab" data-bs-toggle="tab" href="#past" role="tab" aria-controls="past" aria-selected="false">
                        <i class="fas fa-calendar-check me-1"></i> Passés
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="all-tab" data-bs-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="false">
                        <i class="fas fa-calendar-alt me-1"></i> Tous
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="eventTabsContent">
                <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Date</th>
                                    <th>Lieu</th>
                                    <th>Statut</th>
                                    <th>Créé par</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $upcomingEvents = $events->filter(function($event) {
                                        return $event->start_date->isFuture();
                                    });
                                @endphp

                                @forelse($upcomingEvents as $event)
                                    <tr>
                                        <td>
                                            <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard['id'], $event->id]) }}" class="fw-bold text-decoration-none">
                                                {{ $event->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>{{ $event->start_date->format('d/m/Y H:i') }}</div>
                                            @if($event->end_date)
                                                <div class="text-muted small">jusqu'au {{ $event->end_date->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $event->location ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $event->status == 'published' ? 'success' : ($event->status == 'draft' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($event->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $event->creator->name }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard['id'], $event->id]) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($event->canBeEditedBy(Auth::user()))
                                                    <a href="{{ route('bulletin-boards.events.edit', [$bulletinBoard['id'], $event->id]) }}" class="btn btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-calendar fa-2x mb-3"></i>
                                                <p>Aucun événement à venir n'est disponible.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Date</th>
                                    <th>Lieu</th>
                                    <th>Statut</th>
                                    <th>Créé par</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $pastEvents = $events->filter(function($event) {
                                        return !$event->start_date->isFuture();
                                    });
                                @endphp

                                @forelse($pastEvents as $event)
                                    <tr>
                                        <td>
                                            <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard['id'], $event->id]) }}" class="fw-bold text-decoration-none">
                                                {{ $event->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>{{ $event->start_date->format('d/m/Y H:i') }}</div>
                                            @if($event->end_date)
                                                <div class="text-muted small">jusqu'au {{ $event->end_date->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $event->location ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $event->status == 'published' ? 'success' : ($event->status == 'draft' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($event->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $event->creator->name }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard['id'], $event->id]) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($event->canBeEditedBy(Auth::user()))
                                                    <a href="{{ route('bulletin-boards.events.edit', [$bulletinBoard['id'], $event->id]) }}" class="btn btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-calendar-check fa-2x mb-3"></i>
                                                <p>Aucun événement passé n'est disponible.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Date</th>
                                    <th>Lieu</th>
                                    <th>Statut</th>
                                    <th>Créé par</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                    <tr>
                                        <td>
                                            <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard['id'], $event->id]) }}" class="fw-bold text-decoration-none">
                                                {{ $event->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>{{ $event->start_date->format('d/m/Y H:i') }}</div>
                                            @if($event->end_date)
                                                <div class="text-muted small">jusqu'au {{ $event->end_date->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $event->location ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $event->status == 'published' ? 'success' : ($event->status == 'draft' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($event->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $event->creator->name }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard['id'], $event->id]) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($event->canBeEditedBy(Auth::user()))
                                                    <a href="{{ route('bulletin-boards.events.edit', [$bulletinBoard['id'], $event->id]) }}" class="btn btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                                                <p>Aucun événement n'est disponible.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        {{ $events->links() }}
    </div>

    <div class="mt-4">
        <a href="{{ route('bulletin-boards.show', $bulletinBoard['id']) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour au tableau d'affichage
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Restore active tab from localStorage if available
        const activeTabId = localStorage.getItem('activeEventTab');
        if (activeTabId) {
            const tab = document.querySelector(activeTabId);
            if (tab) {
                const tabInstance = new bootstrap.Tab(tab);
                tabInstance.show();
            }
        }

        // Save active tab to localStorage when clicked
        const tabs = document.querySelectorAll('a[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                localStorage.setItem('activeEventTab', '#' + event.target.id);
            });
        });
    });
</script>
@endsection
