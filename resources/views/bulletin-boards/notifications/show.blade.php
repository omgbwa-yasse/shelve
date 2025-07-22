@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Détail de la notification</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('bulletin-boards.index') }}">Bulletin Boards</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('notifications.organisation') }}">Notifications</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Détail</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        {{ $notification->name }}
                    </h6>
                    <div>
                        @if(!$notification->is_read)
                            <span class="badge badge-warning">Non lue</span>
                        @else
                            <span class="badge badge-success">Lue</span>
                        @endif
                        <span class="badge badge-info">{{ $notification->module->label() }}</span>
                        <span class="badge badge-secondary">{{ $notification->action->label() }}</span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Informations générales</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nom :</strong></td>
                                    <td>{{ $notification->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Module :</strong></td>
                                    <td>{{ $notification->module->label() }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Action :</strong></td>
                                    <td>{{ $notification->action->label() }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Créée le :</strong></td>
                                    <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Statut :</strong></td>
                                    <td>
                                        @if($notification->is_read)
                                            <span class="text-success">Lue</span>
                                        @else
                                            <span class="text-warning">Non lue</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Entités liées</h6>
                            <table class="table table-sm">
                                @if($notification->user)
                                <tr>
                                    <td><strong>Utilisateur :</strong></td>
                                    <td>{{ $notification->user->name }}</td>
                                </tr>
                                @endif
                                @if($notification->organisation)
                                <tr>
                                    <td><strong>Organisation :</strong></td>
                                    <td>{{ $notification->organisation->name }}</td>
                                </tr>
                                @endif
                                @if($notification->related_entity_type)
                                <tr>
                                    <td><strong>Type d'entité :</strong></td>
                                    <td>{{ $notification->related_entity_type }}</td>
                                </tr>
                                @endif
                                @if($notification->related_entity_id)
                                <tr>
                                    <td><strong>ID de l'entité :</strong></td>
                                    <td>{{ $notification->related_entity_id }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($notification->message)
                    <div class="mb-4">
                        <h6 class="text-muted">Message</h6>
                        <div class="alert alert-info">
                            {{ $notification->message }}
                        </div>
                    </div>
                    @else
                    <div class="mb-4">
                        <h6 class="text-muted">Message automatique</h6>
                        <div class="alert alert-secondary">
                            {{ $notification->getFormattedMessage() }}
                        </div>
                    </div>
                    @endif

                    @if($notification->getRelatedEntity())
                    <div class="mb-4">
                        <h6 class="text-muted">Entité associée</h6>
                        <div class="alert alert-light">
                            <strong>{{ $notification->related_entity_type }}</strong> - ID: {{ $notification->related_entity_id }}
                            <br>
                            <small class="text-muted">Informations détaillées de l'entité associée peuvent être affichées ici</small>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('notifications.organisation') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Retour aux notifications
                            </a>
                        </div>
                        <div>
                            @if(!$notification->is_read)
                            <button class="btn btn-success" onclick="markAsRead({{ $notification->id }})">
                                <i class="bi bi-check-circle"></i> Marquer comme lue
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions rapides</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('notifications.current') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-person"></i> Mes notifications
                        </a>
                        <a href="{{ route('notifications.organisation') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-building"></i> Notifications organisation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/bulletin-boards/notifications/api/mark-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            notification_ids: [notificationId]
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors du marquage de la notification');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors du marquage de la notification');
    });
}
</script>
@endsection
