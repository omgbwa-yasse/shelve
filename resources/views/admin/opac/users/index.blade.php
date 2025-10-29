@extends('layouts.app')

@section('title', 'Gestion des utilisateurs OPAC')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Gestion des utilisateurs OPAC</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.configurations.index') }}">OPAC</a></li>
                        <li class="breadcrumb-item active">Utilisateurs</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de statut -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-users text-primary me-2"></i>
                            Liste des utilisateurs OPAC
                        </h4>
                        <a href="{{ route('admin.opac.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Ajouter un utilisateur
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Filtres de recherche -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Nom, prénom ou email...">
                        </div>
                        <div class="col-md-3">
                            <label for="approval_status" class="form-label">Statut</label>
                            <select class="form-select" id="approval_status" name="approval_status">
                                <option value="">Tous les statuts</option>
                                <option value="1" {{ request('approval_status') == '1' ? 'selected' : '' }}>Approuvé</option>
                                <option value="0" {{ request('approval_status') == '0' ? 'selected' : '' }}>En attente</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filtrer
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <a href="{{ route('admin.opac.users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Effacer
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Statistiques -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-users text-primary fs-1"></i>
                                    <h5 class="mt-2">{{ $users->total() }}</h5>
                                    <small class="text-muted">Total utilisateurs</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle text-success fs-1"></i>
                                    <h5 class="mt-2">{{ $users->where('is_approved', true)->count() }}</h5>
                                    <small class="text-muted">Approuvés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock text-warning fs-1"></i>
                                    <h5 class="mt-2">{{ $users->where('is_approved', false)->count() }}</h5>
                                    <small class="text-muted">En attente</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar text-info fs-1"></i>
                                    <h5 class="mt-2">{{ $users->whereBetween('created_at', [now()->subDays(7), now()])->count() }}</h5>
                                    <small class="text-muted">Cette semaine</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table des utilisateurs -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Statut</th>
                                    <th>Activités</th>
                                    <th>Inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $user->first_name }} {{ $user->name }}</h6>
                                                    @if($user->address)
                                                        <small class="text-muted">{{ Str::limit($user->address, 40) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $user->email }}</span>
                                        </td>
                                        <td>
                                            @if($user->phone1)
                                                <div>{{ $user->phone1 }}</div>
                                            @endif
                                            @if($user->phone2)
                                                <small class="text-muted">{{ $user->phone2 }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->is_approved)
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fas fa-check me-1"></i> Approuvé
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="fas fa-clock me-1"></i> En attente
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                @if($user->document_requests_count > 0)
                                                    <span class="badge bg-info-subtle text-info">
                                                        {{ $user->document_requests_count }} demandes
                                                    </span>
                                                @endif
                                                @if($user->feedbacks_count > 0)
                                                    <span class="badge bg-secondary-subtle text-secondary">
                                                        {{ $user->feedbacks_count }} avis
                                                    </span>
                                                @endif
                                                @if($user->event_registrations_count > 0)
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        {{ $user->event_registrations_count }} événements
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $user->created_at->format('d/m/Y') }}</span>
                                            <br>
                                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.opac.users.show', $user) }}">
                                                            <i class="fas fa-eye me-2"></i> Voir les détails
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.opac.users.edit', $user) }}">
                                                            <i class="fas fa-edit me-2"></i> Modifier
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @if(!$user->is_approved)
                                                        <li>
                                                            <form action="{{ route('admin.opac.users.approve', $user) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-success">
                                                                    <i class="fas fa-check me-2"></i> Approuver
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <form action="{{ route('admin.opac.users.disapprove', $user) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-warning">
                                                                    <i class="fas fa-times me-2"></i> Révoquer
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <form action="{{ route('admin.opac.users.destroy', $user) }}" method="POST"
                                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-trash me-2"></i> Supprimer
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <h5>Aucun utilisateur trouvé</h5>
                                                <p>Il n'y a pas d'utilisateurs correspondant à vos critères de recherche.</p>
                                                <a href="{{ route('admin.opac.users.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-1"></i> Ajouter le premier utilisateur
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <p class="text-muted mb-0">
                                    Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }}
                                    sur {{ $users->total() }} utilisateurs
                                </p>
                            </div>
                            <div>
                                {{ $users->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
