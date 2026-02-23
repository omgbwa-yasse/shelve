@extends('layouts.app')

@section('content')
<div class="container-fluid workplace-dashboard">

    @include('workplaces.partials.site-header', ['activeTab' => 'dashboard'])

    {{-- ==================== STATS ROW ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card stat-dashlet border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-3" style="width: 48px; height: 48px; background: rgba(13,110,253,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-people-fill text-primary" style="font-size: 1.4rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Membres</div>
                        <div class="fw-bold fs-4 lh-1 mt-1">{{ $workplace->members_count }}</div>
                        @if($workplace->max_members)
                            <div class="progress mt-2" style="height: 4px; width: 80px;">
                                <div class="progress-bar bg-primary" style="width: {{ min(($workplace->members_count / $workplace->max_members) * 100, 100) }}%"></div>
                            </div>
                            <div class="text-muted" style="font-size: 0.65rem;">sur {{ $workplace->max_members }} max</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-dashlet border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-3" style="width: 48px; height: 48px; background: rgba(25,135,84,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-folder-fill text-success" style="font-size: 1.4rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Dossiers</div>
                        <div class="fw-bold fs-4 lh-1 mt-1">{{ $workplace->folders_count }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-dashlet border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-3" style="width: 48px; height: 48px; background: rgba(255,193,7,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-file-earmark-text-fill text-warning" style="font-size: 1.4rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Documents</div>
                        <div class="fw-bold fs-4 lh-1 mt-1">{{ $workplace->documents_count }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-dashlet border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-3" style="width: 48px; height: 48px; background: rgba(111,66,193,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-hdd-fill text-purple" style="font-size: 1.4rem; color: #6f42c1;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Stockage</div>
                        <div class="fw-bold fs-4 lh-1 mt-1">{{ number_format($workplace->storageUsedMb, 1) }} <small class="fw-normal text-muted" style="font-size: 0.75rem;">MB</small></div>
                        @if($workplace->max_storage_mb)
                            <div class="progress mt-2" style="height: 4px; width: 80px;">
                                <div class="progress-bar" style="width: {{ $workplace->storagePercentage }}%; background: #6f42c1;"></div>
                            </div>
                            <div class="text-muted" style="font-size: 0.65rem;">sur {{ $workplace->max_storage_mb }} MB</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== DASHLETS GRID ==================== --}}
    <div class="row g-4">
        {{-- ============ LEFT COLUMN ============ --}}
        <div class="col-lg-8">

            {{-- DASHLET: Welcome / About --}}
            @if($workplace->description)
            <div class="card dashlet mb-4 border-0 shadow-sm">
                <div class="card-header dashlet-header d-flex align-items-center">
                    <i class="bi bi-info-circle me-2 text-primary"></i>
                    <span class="fw-semibold">À propos de cet espace</span>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="me-3" style="min-width: 44px;">
                            <div style="width: 44px; height: 44px; background: {{ $workplace->color ?? '#2c3e6b' }}; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.2rem;">
                                <i class="bi {{ $workplace->icon ?? 'bi-building' }}"></i>
                            </div>
                        </div>
                        <div>
                            <p class="mb-2">{{ $workplace->description }}</p>
                            <div class="d-flex flex-wrap gap-3 text-muted small">
                                <span><i class="bi bi-person me-1"></i>Créé par <strong>{{ $workplace->owner->name ?? '—' }}</strong></span>
                                <span><i class="bi bi-calendar3 me-1"></i>{{ $workplace->created_at->translatedFormat('d M Y') }}</span>
                                @if($workplace->start_date)
                                    <span><i class="bi bi-calendar-range me-1"></i>{{ $workplace->start_date->translatedFormat('d/m/Y') }} — {{ $workplace->end_date?->translatedFormat('d/m/Y') ?? '∞' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- DASHLET: Pinned Folders --}}
            @php
                $pinnedFolders = $workplace->folders->where('is_pinned', true)->take(4);
            @endphp
            @if($pinnedFolders->isNotEmpty())
            <div class="card dashlet mb-4 border-0 shadow-sm">
                <div class="card-header dashlet-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-pin-angle-fill me-2 text-info"></i>
                        <span class="fw-semibold">Dossiers épinglés</span>
                    </div>
                    <a href="{{ route('workplaces.content.folders', $workplace) }}" class="btn btn-sm btn-outline-secondary border-0">
                        Voir tout <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($pinnedFolders as $pf)
                        <div class="col-sm-6 col-md-3">
                            <div class="text-center p-3 rounded-3" style="background: #f8f9fa; transition: background 0.2s;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                                <i class="bi bi-folder-fill text-warning d-block mb-2" style="font-size: 2rem;"></i>
                                <div class="fw-semibold small text-truncate" title="{{ $pf->folder->name ?? '—' }}">{{ $pf->folder->name ?? 'Sans titre' }}</div>
                                <div class="text-muted" style="font-size: 0.65rem;">{{ $pf->shared_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- DASHLET: Featured Documents --}}
            @php
                $featuredDocs = $workplace->documents->where('is_featured', true)->take(4);
            @endphp
            @if($featuredDocs->isNotEmpty())
            <div class="card dashlet mb-4 border-0 shadow-sm">
                <div class="card-header dashlet-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-star-fill me-2 text-warning"></i>
                        <span class="fw-semibold">Documents en Vedette</span>
                    </div>
                    <a href="{{ route('workplaces.content.documents', $workplace) }}" class="btn btn-sm btn-outline-secondary border-0">
                        Voir tout <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($featuredDocs as $fd)
                        <div class="col-sm-6">
                            <a href="{{ route('workplaces.content.viewDocument', [$workplace, $fd]) }}" class="text-decoration-none">
                                <div class="d-flex align-items-center p-2 rounded-3" style="background: #f8f9fa; transition: background 0.2s;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                                    <div class="me-3" style="width: 40px; height: 40px; background: rgba(255,193,7,0.15); border-radius: 8px; display:flex; align-items:center; justify-content:center;">
                                        <i class="bi bi-file-earmark-star text-warning" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold small text-dark text-truncate">{{ $fd->document->name ?? 'Sans titre' }}</div>
                                        <div class="text-muted" style="font-size: 0.65rem;">Partagé {{ $fd->shared_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- DASHLET: Recent Content --}}
            <div class="card dashlet mb-4 border-0 shadow-sm">
                <div class="card-header dashlet-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        <span class="fw-semibold">Contenu récent</span>
                    </div>
                    <div>
                        <ul class="nav nav-pills nav-sm" id="contentTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active px-3 py-1" id="docs-tab" data-bs-toggle="pill" data-bs-target="#docsPane" type="button" role="tab" style="font-size: 0.8rem;">
                                    <i class="bi bi-file-earmark me-1"></i>Documents
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-3 py-1" id="folders-tab" data-bs-toggle="pill" data-bs-target="#foldersPane" type="button" role="tab" style="font-size: 0.8rem;">
                                    <i class="bi bi-folder me-1"></i>Dossiers
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        {{-- Documents Tab --}}
                        <div class="tab-pane fade show active" id="docsPane" role="tabpanel">
                            <div class="list-group list-group-flush">
                                @forelse($workplace->documents->take(8) as $doc)
                                <a href="{{ route('workplaces.content.viewDocument', [$workplace, $doc]) }}" class="list-group-item list-group-item-action border-0 px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3" style="width: 36px; height: 36px; background: #e8f4fd; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-file-earmark-text text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-semibold small text-truncate me-2">{{ $doc->document->name ?? 'Sans titre' }}</span>
                                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                    @if($doc->is_featured)
                                                        <span class="badge bg-warning text-dark" style="font-size: 0.6rem;"><i class="bi bi-star-fill"></i> Vedette</span>
                                                    @endif
                                                    <span class="badge bg-light text-muted" style="font-size: 0.6rem;">{{ $doc->access_level }}</span>
                                                </div>
                                            </div>
                                            <div class="text-muted d-flex gap-3" style="font-size: 0.7rem;">
                                                @if($doc->sharedBy)
                                                    <span><i class="bi bi-person me-1"></i>{{ $doc->sharedBy->name ?? '—' }}</span>
                                                @endif
                                                <span><i class="bi bi-clock me-1"></i>{{ $doc->shared_at->diffForHumans() }}</span>
                                                @if($doc->share_note)
                                                    <span class="text-truncate" style="max-width: 200px;"><i class="bi bi-chat-left-text me-1"></i>{{ $doc->share_note }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                @empty
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-file-earmark-x d-block mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <span class="small">Aucun document partagé dans cet espace</span>
                                    <div class="mt-2">
                                        <a href="{{ route('workplaces.content.documents', $workplace) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-plus-lg me-1"></i>Partager un document
                                        </a>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                            @if($workplace->documents->count() > 8)
                            <div class="text-center py-2 border-top">
                                <a href="{{ route('workplaces.content.documents', $workplace) }}" class="btn btn-sm btn-link text-decoration-none">
                                    Voir les {{ $workplace->documents->count() }} documents <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                            @endif
                        </div>

                        {{-- Folders Tab --}}
                        <div class="tab-pane fade" id="foldersPane" role="tabpanel">
                            <div class="list-group list-group-flush">
                                @forelse($workplace->folders->take(8) as $folder)
                                <div class="list-group-item border-0 px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3" style="width: 36px; height: 36px; background: #fef9e7; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-folder-fill text-warning"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-semibold small text-truncate me-2">{{ $folder->folder->name ?? 'Sans titre' }}</span>
                                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                    @if($folder->is_pinned)
                                                        <span class="badge bg-info text-dark" style="font-size: 0.6rem;"><i class="bi bi-pin-fill"></i> Épinglé</span>
                                                    @endif
                                                    <span class="badge bg-light text-muted" style="font-size: 0.6rem;">{{ $folder->access_level }}</span>
                                                </div>
                                            </div>
                                            <div class="text-muted d-flex gap-3" style="font-size: 0.7rem;">
                                                @if($folder->sharedBy)
                                                    <span><i class="bi bi-person me-1"></i>{{ $folder->sharedBy->name ?? '—' }}</span>
                                                @endif
                                                <span><i class="bi bi-clock me-1"></i>{{ $folder->shared_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-folder-x d-block mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <span class="small">Aucun dossier partagé dans cet espace</span>
                                    <div class="mt-2">
                                        <a href="{{ route('workplaces.content.folders', $workplace) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-plus-lg me-1"></i>Partager un dossier
                                        </a>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                            @if($workplace->folders->count() > 8)
                            <div class="text-center py-2 border-top">
                                <a href="{{ route('workplaces.content.folders', $workplace) }}" class="btn btn-sm btn-link text-decoration-none">
                                    Voir les {{ $workplace->folders->count() }} dossiers <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ RIGHT COLUMN ============ --}}
        <div class="col-lg-4">

            {{-- DASHLET: Quick Actions --}}
            <div class="card dashlet mb-4 border-0 shadow-sm">
                <div class="card-header dashlet-header">
                    <i class="bi bi-lightning-fill me-2 text-warning"></i>
                    <span class="fw-semibold">Actions rapides</span>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('workplaces.content.documents', $workplace) }}" class="btn btn-outline-primary btn-sm text-start">
                            <i class="bi bi-file-earmark-plus me-2"></i>Partager un document
                        </a>
                        <a href="{{ route('workplaces.content.folders', $workplace) }}" class="btn btn-outline-success btn-sm text-start">
                            <i class="bi bi-folder-plus me-2"></i>Partager un dossier
                        </a>
                        <a href="{{ route('workplaces.members.index', $workplace) }}" class="btn btn-outline-info btn-sm text-start">
                            <i class="bi bi-person-plus me-2"></i>Inviter un membre
                        </a>
                        @can('update', $workplace)
                        <a href="{{ route('workplaces.edit', $workplace) }}" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-pencil-square me-2"></i>Modifier l'espace
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- DASHLET: Members --}}
            <div class="card dashlet mb-4 border-0 shadow-sm">
                <div class="card-header dashlet-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-people-fill me-2 text-primary"></i>
                        <span class="fw-semibold">Membres</span>
                        <span class="badge bg-primary rounded-pill ms-1">{{ $workplace->members->count() }}</span>
                    </div>
                    <a href="{{ route('workplaces.members.index', $workplace) }}" class="btn btn-sm btn-outline-secondary border-0">
                        Gérer <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($workplace->members->take(6) as $member)
                        <div class="list-group-item border-0 px-4 py-2">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @if($member->user && $member->user->profile_photo_path)
                                        <img src="{{ $member->user->profile_photo_url }}" alt="" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: {{ ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'][$loop->index % 6] }}; color: #fff; font-weight: 600; font-size: 0.85rem;">
                                            {{ strtoupper(substr($member->user->name ?? '?', 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold small text-truncate">{{ $member->user->name ?? '—' }}</div>
                                    <div class="d-flex align-items-center gap-1">
                                        @php
                                            $roleColors = ['owner' => 'danger', 'admin' => 'warning', 'editor' => 'info', 'viewer' => 'secondary', 'member' => 'primary'];
                                        @endphp
                                        <span class="badge bg-{{ $roleColors[$member->role] ?? 'secondary' }}" style="font-size: 0.6rem;">{{ ucfirst($member->role) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($workplace->members->count() > 6)
                    <div class="text-center py-2 border-top">
                        <a href="{{ route('workplaces.members.index', $workplace) }}" class="btn btn-sm btn-link text-decoration-none small">
                            +{{ $workplace->members->count() - 6 }} autre(s) membre(s) <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- DASHLET: Site Activity Feed --}}
            <div class="card dashlet mb-4 border-0 shadow-sm">
                <div class="card-header dashlet-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-activity me-2 text-success"></i>
                        <span class="fw-semibold">Activité récente</span>
                    </div>
                    <a href="{{ route('workplaces.activities.index', $workplace) }}" class="btn btn-sm btn-outline-secondary border-0">
                        Historique <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @forelse($workplace->activities as $activity)
                    <div class="px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex align-items-start">
                            <div class="me-3 mt-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #e8f5e9;">
                                    @php
                                        $activityIcons = [
                                            'document_shared' => 'bi-file-earmark-plus text-success',
                                            'folder_shared'   => 'bi-folder-plus text-success',
                                            'member_added'    => 'bi-person-plus text-info',
                                            'member_removed'  => 'bi-person-dash text-danger',
                                            'document_removed'=> 'bi-file-earmark-minus text-danger',
                                            'folder_removed'  => 'bi-folder-minus text-danger',
                                            'settings_updated'=> 'bi-gear text-secondary',
                                        ];
                                        $icon = $activityIcons[$activity->activity_type] ?? 'bi-circle-fill text-primary';
                                    @endphp
                                    <i class="bi {{ $icon }}" style="font-size: 0.75rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="small">
                                    <strong>{{ $activity->user->name ?? '—' }}</strong>
                                    <span class="text-muted">{{ $activity->description }}</span>
                                </div>
                                <div class="text-muted" style="font-size: 0.65rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $activity->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-clock d-block mb-2" style="font-size: 1.5rem; opacity: 0.3;"></i>
                        <span class="small">Aucune activité récente</span>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- DASHLET: Workspace Info --}}
            <div class="card dashlet border-0 shadow-sm">
                <div class="card-header dashlet-header">
                    <i class="bi bi-info-circle me-2 text-secondary"></i>
                    <span class="fw-semibold">Informations</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                        <tbody>
                            <tr><td class="text-muted border-0 ps-4 py-2" style="width: 40%;">Code</td><td class="border-0 py-2 fw-semibold">{{ $workplace->code }}</td></tr>
                            <tr><td class="text-muted ps-4 py-2">Catégorie</td><td class="py-2">{{ $workplace->category->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted ps-4 py-2">Propriétaire</td><td class="py-2">{{ $workplace->owner->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted ps-4 py-2">Visibilité</td><td class="py-2">{{ $workplace->is_public ? 'Public' : 'Privé' }}</td></tr>
                            <tr><td class="text-muted ps-4 py-2">Créé le</td><td class="py-2">{{ $workplace->created_at->translatedFormat('d M Y') }}</td></tr>
                            @if($workplace->start_date)
                            <tr><td class="text-muted ps-4 py-2">Période</td><td class="py-2">{{ $workplace->start_date->translatedFormat('d/m/Y') }} — {{ $workplace->end_date?->translatedFormat('d/m/Y') ?? '∞' }}</td></tr>
                            @endif
                            @if($workplace->allow_external_sharing)
                            <tr><td class="text-muted ps-4 py-2">Partage externe</td><td class="py-2"><span class="badge bg-success">Activé</span></td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ===================== WORKPLACE DASHBOARD ===================== */
.workplace-dashboard .dashlet {
    border-radius: 0.5rem;
    overflow: hidden;
}
.workplace-dashboard .dashlet-header {
    background: #fafbfc;
    border-bottom: 1px solid #eef0f3;
    padding: 0.65rem 1.25rem;
    font-size: 0.875rem;
}
.workplace-dashboard .stat-dashlet {
    border-radius: 0.75rem;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.workplace-dashboard .stat-dashlet:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}

/* Content Tabs (pills inside dashlet) */
.nav-sm .nav-link {
    font-size: 0.75rem !important;
    padding: 0.25rem 0.6rem !important;
    border-radius: 1rem;
}

/* Activity feed items hover */
.workplace-dashboard .card-body .px-4.py-3:hover {
    background: #f8f9fa;
}

/* List group action hover refinement */
.workplace-dashboard .list-group-item-action:hover {
    background: #f0f4ff;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .stat-dashlet .card-body {
        padding: 0.75rem !important;
    }
}
</style>
@endpush
