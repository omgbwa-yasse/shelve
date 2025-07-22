@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('bulletin-boards.index') }}">Bulletin Boards</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Notifications</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Actions</h6>
                    <fieldset class="btn-group">
                        <a href="{{ route('notifications.current') }}"
                           class="btn btn-outline-primary btn-sm {{ $type === 'user' ? 'active' : '' }}">
                            <i class="bi bi-person"></i> Mes notifications
                        </a>
                        @if(auth()->user() && isset(auth()->user()->current_organisation_id))
                        <a href="{{ route('notifications.organisation') }}"
                           class="btn btn-outline-primary btn-sm {{ $type === 'organisation' ? 'active' : '' }}">
                            <i class="bi bi-building"></i> Notifications organisation
                        </a>
                        @endif
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Statistiques</h6>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-primary">{{ $notifications->total() }}</h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-warning">{{ $unreadCount }}</h4>
                                <small class="text-muted">Non lues</small>
                            </div>
                        </div>
                    </div>
                    @if($unreadCount > 0)
                    <div class="mt-3">
                        <button class="btn btn-sm btn-success btn-block" onclick="markAllAsRead()">
                            <i class="bi bi-check-circle"></i> Marquer tout comme lu
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ $title }}
                @if($type === 'organisation')
                    (Organisation)
                @else
                    (Personnel)
                @endif
            </h6>
            <div>
                <span class="badge badge-info">{{ $notifications->total() }} notifications</span>
                @if($unreadCount > 0)
                <span class="badge badge-warning">{{ $unreadCount }} non lues</span>
                @endif
            </div>
        </div>

        <div class="card-body">
            @if($notifications->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash display-4 text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune notification</h5>
                    <p class="text-muted">
                        @if($type === 'organisation')
                            Il n'y a actuellement aucune notification pour cette organisation.
                        @else
                            Vous n'avez actuellement aucune notification.
                        @endif
                    </p>
                </div>
            @else
                <div class="list-group">
                    @foreach($notifications as $notification)
                        <div class="list-group-item list-group-item-action {{ !$notification->is_read ? 'border-left-primary' : '' }}">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 {{ !$notification->is_read ? 'font-weight-bold' : '' }}">
                                            {{ $notification->name }}
                                        </h6>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>

                                    <p class="mb-2 text-muted">
                                        @if($notification->message)
                                            {{ Str::limit($notification->message, 100) }}
                                        @else
                                            {{ Str::limit($notification->getFormattedMessage(), 100) }}
                                        @endif
                                    </p>

                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-info mr-2">{{ $notification->module->label() }}</span>
                                        <span class="badge badge-secondary mr-2">{{ $notification->action->label() }}</span>
                                        @if($notification->user && $type === 'organisation')
                                            <span class="badge badge-light mr-2">{{ $notification->user->name }}</span>
                                        @endif
                                        @if($notification->organisation && $type === 'user')
                                            <span class="badge badge-light mr-2">{{ $notification->organisation->name }}</span>
                                        @endif
                                        @if(!$notification->is_read)
                                            <span class="badge badge-warning">Non lue</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="ml-3">
                                    <div class="btn-group-vertical" role="group">
                                        <a href="{{ route('notifications.show', $notification->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Voir
                                        </a>
                                        @if(!$notification->is_read)
                                        <button class="btn btn-sm btn-outline-success mt-1"
                                                onclick="markAsRead([{{ $notification->id }}])">
                                            <i class="bi bi-check"></i> Lu
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function markAsRead(notificationIds) {
    fetch('/bulletin-boards/notifications/api/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            notification_ids: notificationIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors du marquage des notifications');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors du marquage des notifications');
    });
}

function markAllAsRead() {
    const scope = '{{ $type }}'; // 'user' ou 'organisation'

    fetch('/bulletin-boards/notifications/api/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            scope: scope
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors du marquage des notifications');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors du marquage des notifications');
    });
}
</script>

<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}

.list-group-item {
    transition: all 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>
@endsection
