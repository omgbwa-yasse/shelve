@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $workplace->name }}</h2>
            <p class="text-muted">{{ $workplace->code }} · {{ $workplace->category->name }}</p>
        </div>
        <div class="col-md-4 text-end">
            @can('update', $workplace)
            <a href="{{ route('workplaces.edit', $workplace) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            @endcan
            <a href="{{ route('workplaces.settings', $workplace) }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear"></i> Paramètres
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Membres</h6>
                    <h3>{{ $workplace->members_count }} <small class="text-muted">/ {{ $workplace->max_members ?? '∞' }}</small></h3>
                    @if($workplace->max_members)
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar" style="width: {{ ($workplace->members_count / $workplace->max_members) * 100 }}%"></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Dossiers</h6>
                    <h3>{{ $workplace->folders_count }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Documents</h6>
                    <h3>{{ $workplace->documents_count }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Stockage</h6>
                    <h3>{{ number_format($workplace->storageUsedMb, 2) }} MB</h3>
                    @if($workplace->max_storage_mb)
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar" style="width: {{ $workplace->storagePercentage }}%"></div>
                    </div>
                    <small class="text-muted">/ {{ $workplace->max_storage_mb }} MB</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Description -->
            @if($workplace->description)
            <div class="card mb-4">
                <div class="card-body">
                    <h5>À propos</h5>
                    <p>{{ $workplace->description }}</p>
                </div>
            </div>
            @endif

            <!-- Recent Documents -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Documents récents</h5>
                    <a href="{{ route('workplaces.content.documents', $workplace) }}" class="btn btn-sm btn-link">Voir tout</a>
                </div>
                <div class="card-body">
                    @forelse($workplace->documents->take(5) as $doc)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <i class="bi bi-file-earmark"></i>
                            <a href="{{ route('workplaces.content.viewDocument', [$workplace, $doc]) }}">
                                {{ $doc->document->name ?? 'Sans titre' }}
                            </a>
                            @if($doc->is_featured)
                            <span class="badge bg-warning text-dark">★ Vedette</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ $doc->shared_at->diffForHumans() }}</small>
                    </div>
                    @empty
                    <p class="text-muted">Aucun document partagé</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Folders -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Dossiers récents</h5>
                    <a href="{{ route('workplaces.content.folders', $workplace) }}" class="btn btn-sm btn-link">Voir tout</a>
                </div>
                <div class="card-body">
                    @forelse($workplace->folders->take(5) as $folder)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <i class="bi bi-folder"></i>
                            {{ $folder->folder->name ?? 'Sans titre' }}
                            @if($folder->is_pinned)
                            <span class="badge bg-info">📌 Épinglé</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ $folder->shared_at->diffForHumans() }}</small>
                    </div>
                    @empty
                    <p class="text-muted">Aucun dossier partagé</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            <!-- Members -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Membres</h5>
                    <a href="{{ route('workplaces.members.index', $workplace) }}" class="btn btn-sm btn-link">Gérer</a>
                </div>
                <div class="card-body">
                    @foreach($workplace->members->take(5) as $member)
                    <div class="d-flex align-items-center mb-2">
                        <div class="me-2">
                            <i class="bi bi-person-circle fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div>{{ $member->user->name }}</div>
                            <small class="text-muted">{{ ucfirst($member->role) }}</small>
                        </div>
                    </div>
                    @endforeach
                    @if($workplace->members->count() > 5)
                    <small class="text-muted">et {{ $workplace->members->count() - 5 }} autre(s)...</small>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Activité récente</h5>
                </div>
                <div class="card-body">
                    @forelse($workplace->activities as $activity)
                    <div class="mb-3">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-circle-fill text-primary me-2" style="font-size: 8px; margin-top: 6px;"></i>
                            <div class="flex-grow-1">
                                <small>
                                    <strong>{{ $activity->user->name }}</strong>
                                    {{ $activity->description }}
                                </small>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $activity->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small">Aucune activité récente</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
