@extends('layouts.app')

@section('title', 'Détails utilisateur OPAC')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ $user->first_name }} {{ $user->name }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.configurations.index') }}">OPAC</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.users.index') }}">Utilisateurs</a></li>
                        <li class="breadcrumb-item active">{{ $user->first_name }} {{ $user->name }}</li>
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
        <!-- Informations principales -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <div class="avatar-lg mx-auto mb-3">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-2">
                                {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->name, 0, 1)) }}
                            </span>
                        </div>
                        <h5 class="mb-1">{{ $user->first_name }} {{ $user->name }}</h5>
                        <p class="text-muted mb-3">{{ $user->email }}</p>

                        @if($user->is_approved)
                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                <i class="fas fa-check me-1"></i> Utilisateur approuvé
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                <i class="fas fa-clock me-1"></i> En attente d'approbation
                            </span>
                        @endif
                    </div>

                    <hr class="my-4">

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="p-2">
                                <h5 class="text-primary">{{ $user->documentRequests->count() }}</h5>
                                <small class="text-muted">Demandes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2">
                                <h5 class="text-info">{{ $user->feedbacks->count() }}</h5>
                                <small class="text-muted">Avis</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.opac.users.edit', $user) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>

                        @if(!$user->is_approved)
                            <form action="{{ route('admin.opac.users.approve', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-1"></i> Approuver
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.opac.users.disapprove', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-times me-1"></i> Révoquer l'approbation
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.opac.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations de contact -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-address-book text-primary me-2"></i>
                        Contact
                    </h5>
                </div>
                <div class="card-body">
                    @if($user->phone1)
                        <div class="mb-2">
                            <small class="text-muted">Téléphone principal :</small>
                            <div>{{ $user->phone1 }}</div>
                        </div>
                    @endif

                    @if($user->phone2)
                        <div class="mb-2">
                            <small class="text-muted">Téléphone secondaire :</small>
                            <div>{{ $user->phone2 }}</div>
                        </div>
                    @endif

                    @if($user->address)
                        <div>
                            <small class="text-muted">Adresse :</small>
                            <div>{{ $user->address }}</div>
                        </div>
                    @endif

                    @if(!$user->phone1 && !$user->phone2 && !$user->address)
                        <p class="text-muted mb-0">Aucune information de contact supplémentaire.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Activités et historique -->
        <div class="col-lg-8">
            <!-- Statistiques rapides -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt text-primary fs-1"></i>
                            <h4 class="mt-2">{{ $user->documentRequests->count() }}</h4>
                            <small class="text-muted">Demandes de documents</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-comments text-info fs-1"></i>
                            <h4 class="mt-2">{{ $user->feedbacks->count() }}</h4>
                            <small class="text-muted">Commentaires</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-check text-success fs-1"></i>
                            <h4 class="mt-2">{{ $user->eventRegistrations->count() }}</h4>
                            <small class="text-muted">Événements</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-search text-warning fs-1"></i>
                            <h4 class="mt-2">{{ $user->searchLogs->count() }}</h4>
                            <small class="text-muted">Recherches</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demandes de documents récentes -->
            @if($user->documentRequests->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-alt text-primary me-2"></i>
                            Demandes de documents récentes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Raison</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->documentRequests as $request)
                                        <tr>
                                            <td>{{ $request->request_type }}</td>
                                            <td>{{ Str::limit($request->reason, 50) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $request->status == 'approved' ? 'success' : ($request->status == 'rejected' ? 'danger' : 'warning') }}-subtle text-{{ $request->status == 'approved' ? 'success' : ($request->status == 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $request->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Événements récents -->
            @if($user->eventRegistrations->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-check text-success me-2"></i>
                            Inscriptions aux événements
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Événement</th>
                                        <th>Date événement</th>
                                        <th>Statut</th>
                                        <th>Inscription</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->eventRegistrations as $registration)
                                        <tr>
                                            <td>{{ $registration->event->name ?? 'Événement supprimé' }}</td>
                                            <td>
                                                @if($registration->event)
                                                    {{ $registration->event->start_date->format('d/m/Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $registration->status == 'confirmed' ? 'success' : ($registration->status == 'cancelled' ? 'danger' : 'warning') }}-subtle text-{{ $registration->status == 'confirmed' ? 'success' : ($registration->status == 'cancelled' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($registration->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $registration->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Journaux de recherche récents -->
            @if($user->searchLogs->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-search text-warning me-2"></i>
                            Recherches récentes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Terme recherché</th>
                                        <th>Résultats</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->searchLogs as $log)
                                        <tr>
                                            <td>{{ $log->search_term }}</td>
                                            <td>{{ $log->results_count ?? 0 }}</td>
                                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Informations système -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-muted me-2"></i>
                        Informations système
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Créé le :</small>
                            <div>{{ $user->created_at->format('d/m/Y à H:i') }}</div>
                            <small class="text-muted">({{ $user->created_at->diffForHumans() }})</small>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Modifié le :</small>
                            <div>{{ $user->updated_at->format('d/m/Y à H:i') }}</div>
                            <small class="text-muted">({{ $user->updated_at->diffForHumans() }})</small>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">ID utilisateur :</small>
                            <div><code>{{ $user->id }}</code></div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Statut compte :</small>
                            <div>
                                @if($user->is_approved)
                                    <span class="text-success">Actif</span>
                                @else
                                    <span class="text-warning">En attente</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
