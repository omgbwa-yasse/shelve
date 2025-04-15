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
            <div class="d-flex justify-content-between align-items-center flex-wrap">
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

                <div class="d-flex mt-2 mt-md-0">
                    <div class="dropdown me-2">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i> Filtrer
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item {{ request('status') == 'all' || !request('status') ? 'active' : '' }}" href="{{ route('bulletin-boards.events.index', [$bulletinBoard['id']]) }}">Tous les statuts</a></li>
                            <li><a class="dropdown-item {{ request('status') == 'published' ? 'active' : '' }}" href="{{ route('bulletin-boards.events.index', [$bulletinBoard['id'], 'status' => 'published']) }}">Publiés</a></li>
                            <li><a class="dropdown-item {{ request('status') == 'draft' ? 'active' : '' }}" href="{{ route('bulletin-boards.events.index', [$bulletinBoard['id'], 'status' => 'draft']) }}">Brouillons</a></li>
                            <li><a class="dropdown-item {{ request('status') == 'cancelled' ? 'active' : '' }}" href="{{ route('bulletin-boards.events.index', [$bulletinBoard['id'], 'status' => 'cancelled']) }}">Annulés</a></li>
                        </ul>
                    </div>

                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('bulletin-boards.events.index', [$bulletinBoard['id'], 'view' => 'list']) }}" class="btn btn-outline-secondary {{ request('view', 'list') == 'list' ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                        </a>
                        <a href="{{ route('bulletin-boards.events.index', [$bulletinBoard['id'], 'view' => 'calendar']) }}" class="btn btn-outline-secondary {{ request('view') == 'calendar' ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="tab-content" id="eventTabsContent">
                <!-- Vue Liste -->
                @if(request('view') != 'calendar')
                    <!-- Onglet À venir -->
                    <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                        <div class="table-responsive d-none d-md-block">
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
                                                @if($event->attachments->isNotEmpty())
                                                    <span class="badge bg-info ms-1"><i class="fas fa-paperclip"></i> {{ $event->attachments->count() }}</span>
                                                @endif
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

                        <!-- Vue mobile à venir -->
                        <div class="d-md-none">
                            @forelse($upcomingEvents as $event)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">
                                                <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard['id'], $event->id]) }}" class="text-decoration-none">
                                                    {{ $event->name }}
                                                </a>
                                            </h5>
                                            <span class="badge bg-{{ $event->status == 'published' ? 'success' : ($event->status == 'draft' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($event->status) }}
                                            </span>
                                        </div>
                                        <p class="card-text small">
                                            <i class="fas fa-calendar-day text-muted me-1"></i> {{ $event->start_date->format('d/m/Y H:i') }}
                                            @if($event->end_date)
                                                <br><i class="fas fa-calendar-check text-muted me-1"></i> {{ $event->end_date->format('d/m/Y H:i') }}
                                            @endif
                                            @if($event->location)
                                                <br><i class="fas fa-map-marker-alt text-muted me-1"></i> {{ $event->location }}
                                            @endif
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Par {{ $event->creator->name }}</small>
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
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar fa-2x mb-3"></i>
                                        <p>Aucun événement à venir n'est disponible.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Onglet Passés -->
                    <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
                        <div class="table-responsive d-none d-md-block">
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
                                                @if($event->attachments->isNotEmpty())
                                                    <span class="badge bg-info ms-1"><i class="fas fa-paperclip"></i> {{ $event->attachments->count() }}</span>
                                                @endif
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

                        <!-- Vue mobile passés -->
                        <div class="d-md-none">
                            @forelse($pastEvents as $event)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">
                                                <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard['id'], $event->id]) }}" class="text-decoration-none">
                                                    {{ $event->name }}
                                                </a>
                                            </h5>
                                            <span class="badge bg-{{ $event->status == 'published' ? 'success' : ($event->status == 'draft' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($event->status) }}
                                            </span>
                                        </div>
                                        <p class="card-text small">
                                            <i class="fas fa-calendar-day text-muted me-1"></i> {{ $event->start_date->format('d/m/Y H:i') }}
                                            @if($event->end_date)
                                                <br><i class="fas fa-calendar-check text-muted me-1"></i> {{ $event->end_date->format('d/m/Y H:i') }}
                                            @endif
                                            @if($event->location)
                                                <br><i class="fas fa-map-marker-alt text-muted me-1"></i> {{ $event->location }}
                                            @endif
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Par {{ $event->creator->name }}</small>
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
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-check fa-2x mb-3"></i>
                                        <p>Aucun événement passé n'est disponible.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Onglet Tous -->
                    <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
                        <div class="table-responsive d-none d-md-block">
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
                                                @if($event->attachments->isNotEmpty())
                                                    <span class="badge bg-info ms-1"><i class="fas fa-paperclip"></i> {{ $event->attachments->count() }}</span>
                                                @endif
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

                        <!-- Vue mobile tous -->
                        <div class="d-md-none">
                            @forelse($events as $event)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">
                                                <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard['id'], $event->id]) }}" class="text-decoration-none">
                                                    {{ $event->name }}
                                                </a>
                                            </h5>
                                            <span class="badge bg-{{ $event->status == 'published' ? 'success' : ($event->status == 'draft' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($event->status) }}
                                            </span>
                                        </div>
                                        <p class="card-text small">
                                            <i class="fas fa-calendar-day text-muted me-1"></i> {{ $event->start_date->format('d/m/Y H:i') }}
                                            @if($event->end_date)
                                                <br><i class="fas fa-calendar-check text-muted me-1"></i> {{ $event->end_date->format('d/m/Y H:i') }}
                                            @endif
                                            @if($event->location)
                                                <br><i class="fas fa-map-marker-alt text-muted me-1"></i> {{ $event->location }}
                                            @endif
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Par {{ $event->creator->name }}</small>
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
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                                        <p>Aucun événement n'est disponible.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <!-- Vue Calendrier -->
                    <div class="calendar-container">
                        <div id="calendar" class="mb-4"></div>
                        <div id="event-details" class="card d-none">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0" id="event-title"></h5>
                                <button type="button" class="btn-close" id="close-event-details"></button>
                            </div>
                            <div class="card-body">
                                <p class="card-text" id="event-description"></p>
                                <div id="event-time" class="mb-2">
                                    <i class="fas fa-clock me-2"></i> <span></span>
                                </div>
                                <div id="event-location" class="mb-2">
                                    <i class="fas fa-map-marker-alt me-2"></i> <span></span>
                                </div>
                                <div id="event-status" class="mb-3">
                                    <span class="badge"></span>
                                </div>
                                <a href="#" class="btn btn-primary" id="view-event">Voir les détails</a>
                            </div>
                        </div>
                    </div>
                @endif
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

        @if(request('view') == 'calendar')
        // Initialisation du calendrier si la vue est "calendar"
        const calendarEl = document.getElementById('calendar');
        const eventDetails = document.getElementById('event-details');

        // Données pour le calendrier (à adapter selon vos besoins)
        const events = @json($events->map(function($event) use ($bulletinBoard) {
            return [
                'id' => $event->id,
                'title' => $event->name,
                'start' => $event->start_date->format('Y-m-d\TH:i:s'),
                'end' => $event->end_date ? $event->end_date->format('Y-m-d\TH:i:s') : null,
                'url' => route('bulletin-boards.events.show', [$bulletinBoard['id'], $event->id]),
                'description' => Str::limit($event->description, 150),
                'location' => $event->location,
                'status' => $event->status,
                'backgroundColor' => $event->status == 'published' ? '#198754' : ($event->status == 'draft' ? '#ffc107' : '#6c757d'),
                'borderColor' => $event->status == 'published' ? '#198754' : ($event->status == 'draft' ? '#ffc107' : '#6c757d'),
            ];
        }));

        // Initialisation du calendrier avec FullCalendar
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            locale: 'fr',
            events: events,
            eventClick: function(info) {
                info.jsEvent.preventDefault();

                const eventTitle = document.getElementById('event-title');
                const eventDescription = document.getElementById('event-description');
                const eventTime = document.querySelector('#event-time span');
                const eventLocation = document.querySelector('#event-location span');
                const eventStatus = document.querySelector('#event-status .badge');
                const viewEventBtn = document.getElementById('view-event');

                eventTitle.textContent = info.event.title;
                eventDescription.textContent = info.event.extendedProps.description;

                const startDate = new Date(info.event.start);
                const startFormatted = startDate.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                let timeText = startFormatted;
                if (info.event.end) {
                    const endDate = new Date(info.event.end);
                    const endFormatted = endDate.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    timeText += ` - ${endFormatted}`;
                }

                eventTime.textContent = timeText;

                if (info.event.extendedProps.location) {
                    document.getElementById('event-location').classList.remove('d-none');
                    eventLocation.textContent = info.event.extendedProps.location;
                } else {
                    document.getElementById('event-location').classList.add('d-none');
                }

                const status = info.event.extendedProps.status;
                eventStatus.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                eventStatus.className = `badge bg-${status == 'published' ? 'success' : (status == 'draft' ? 'warning' : 'secondary')}`;

                viewEventBtn.href = info.event.url;

                eventDetails.classList.remove('d-none');
            }
        });

        calendar.render();

        // Fermer le détail de l'événement
        document.getElementById('close-event-details').addEventListener('click', function() {
            eventDetails.classList.add('d-none');
        });
        @endif
    });
</script>
@endsection

@push('styles')
@if(request('view') == 'calendar')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css' rel='stylesheet' />
<style>
    .calendar-container {
        position: relative;
    }

    #event-details {
        position: absolute;
        top: 50px;
        right: 10px;
        width: 350px;
        z-index: 10;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 767.98px) {
        #event-details {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 350px;
        }
    }
</style>
@endif
@endpush

@push('scripts')
@if(request('view') == 'calendar')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/fr.js'></script>
@endif
@endpush
