@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.index') }}">Tableaux d'affichage</a></li>
                    <li class="breadcrumb-item active">{{ $bulletinBoard->name }}</li>
                </ol>
            </nav>
            <h1>{{ $bulletinBoard->name }}</h1>
            <p class="text-muted">
                Créé par {{ $bulletinBoard->creator->name }} · {{ $bulletinBoard->created_at->format('d/m/Y') }}
            </p>
            <div class="mb-4">
                {{ $bulletinBoard->description }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="d-flex justify-content-end">
                @if($bulletinBoard->hasWritePermission(Auth::id()))
                    <div class="dropdown me-2">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-plus me-1"></i> Créer
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('bulletin-boards.events.create', $bulletinBoard->id) }}">
                                    <i class="fas fa-calendar-plus fa-fw me-1"></i> Nouvel événement
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('bulletin-boards.posts.create', $bulletinBoard->id) }}">
                                    <i class="fas fa-file-alt fa-fw me-1"></i> Nouvelle publication
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif

                @if($bulletinBoard->created_by == Auth::id() || $bulletinBoard->isUserAdmin(Auth::id()))
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i> Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('bulletin-boards.edit', $bulletinBoard->id) }}">
                                    <i class="fas fa-edit fa-fw me-1"></i> Modifier
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('bulletin-boards.manage-users', $bulletinBoard->id) }}">
                                    <i class="fas fa-users fa-fw me-1"></i> Gérer les utilisateurs
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('bulletin-boards.destroy', $bulletinBoard->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce tableau ?')">
                                        <i class="fas fa-trash fa-fw me-1"></i> Supprimer
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" id="events-tab" data-bs-toggle="tab" href="#events" role="tab">
                <i class="fas fa-calendar-alt me-1"></i> Événements
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="posts-tab" data-bs-toggle="tab" href="#posts" role="tab">
                <i class="fas fa-thumbtack me-1"></i> Publications
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="members-tab" data-bs-toggle="tab" href="#members" role="tab">
                <i class="fas fa-users me-1"></i> Membres
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="events" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Événements récents</h3>
                <a href="{{ route('bulletin-boards.events.index', $bulletinBoard->id) }}" class="btn btn-sm btn-outline-primary">
                    Voir tous les événements
                </a>
            </div>

            <div class="row">
                @forelse($bulletinBoard->events()->orderBy('start_date', 'desc')->take(6)->get() as $event)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $event->status == 'published' ? 'success' : ($event->status == 'draft' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                                <span class="text-muted small">
                                    {{ $event->start_date->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $event->name }}</h5>
                                <p class="card-text">{{ Str::limit($event->description, 100) }}</p>

                                @if($event->location)
                                    <div class="mb-2">
                                        <i class="fas fa-map-marker-alt fa-fw text-muted"></i> {{ $event->location }}
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="{{ route('bulletin-boards.events.show', [$bulletinBoard->id, $event->id]) }}" class="btn btn-sm btn-outline-primary">Voir les détails</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Aucun événement n'est disponible pour le moment.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="tab-pane fade" id="posts" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Publications récentes</h3>
                <a href="{{ route('bulletin-boards.posts.index', $bulletinBoard->id) }}" class="btn btn-sm btn-outline-primary">
                    Voir toutes les publications
                </a>
            </div>

            <div class="row">
                @forelse($bulletinBoard->posts()->orderBy('created_at', 'desc')->take(6)->get() as $post)
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $post->status == 'published' ? 'success' : ($post->status == 'draft' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                                <span class="text-muted small">
                                    {{ $post->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $post->name }}</h5>
                                <p class="card-text">{{ Str::limit($post->description, 150) }}</p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="{{ route('bulletin-boards.posts.show', [$bulletinBoard->id, $post->id]) }}" class="btn btn-sm btn-outline-primary">Voir les détails</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Aucune publication n'est disponible pour le moment.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="tab-pane fade" id="members" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Membres</h3>
                @if($bulletinBoard->isUserAdmin(Auth::id()))
                    <a href="{{ route('bulletin-boards.manage-users', $bulletinBoard->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-user-cog me-1"></i> Gérer les membres
                    </a>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Rôle</th>
                            <th>Autorisations</th>
                            <th>Depuis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bulletinBoard->users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-circle bg-primary text-white">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            {{ $user->name }}
                                            @if($user->id == $bulletinBoard->created_by)
                                                <span class="badge bg-info ms-1">Créateur</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $user->pivot->role == 'super_admin' ? 'danger' : ($user->pivot->role == 'admin' ? 'primary' : 'success') }}">
                                        {{ ucfirst($user->pivot->role) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($user->pivot->permissions) }}</span>
                                </td>
                                <td>{{ $user->pivot->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucun membre trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}
.avatar-circle {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
@endsection
