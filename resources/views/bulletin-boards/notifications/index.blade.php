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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ $title }}
                @if($type === 'organisation')
                    (Organisation ID: {{ $id }})
                @else
                    (Utilisateur ID: {{ $id }})
                @endif
            </h6>
            <div>
                <span class="badge badge-info">{{ $notifications->count() }} notifications</span>
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
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $notification->title ?? 'Notification' }}</h6>
                                <small class="text-muted">{{ $notification->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-1">{{ $notification->message ?? 'Contenu de la notification' }}</p>
                            <small class="text-muted">
                                Type: {{ $notification->type ?? 'General' }}
                                @if(isset($notification->read_at))
                                    <span class="badge badge-success ml-2">Lu</span>
                                @else
                                    <span class="badge badge-warning ml-2">Non lu</span>
                                @endif
                            </small>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination sera ajoutée ici quand vous implémenterez la logique réelle -->
                <div class="d-flex justify-content-center mt-4">
                    {{-- $notifications->links() --}}
                </div>
            @endif
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Actions</h6>
                    <fieldset class="btn-group">
                        <a href="{{ route('bulletin-boards.notifications.user') }}"
                           class="btn btn-outline-primary btn-sm {{ $type === 'user' ? 'active' : '' }}">
                            <i class="bi bi-person"></i> Mes notifications
                        </a>
                        @if(auth()->user() && isset(auth()->user()->current_organisation_id))
                        <a href="{{ route('bulletin-boards.notifications.organisation') }}"
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
                    <h6 class="card-title">Informations</h6>
                    <p class="text-muted small mb-0">
                        Les notifications sont triées par ordre chronologique (les plus récentes en premier)
                        et affichées par groupe de 20.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.list-group-item:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75rem;
}

.display-4 {
    font-size: 3rem;
}
</style>
@endsection
