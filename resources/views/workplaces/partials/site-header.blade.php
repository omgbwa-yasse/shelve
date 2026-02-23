{{-- ==================== SITE BANNER ==================== --}}
<div class="site-banner mb-0" style="background: linear-gradient(135deg, {{ $workplace->color ?? '#2c3e6b' }} 0%, {{ $workplace->color ?? '#2c3e6b' }}cc 100%); border-radius: 0.5rem 0.5rem 0 0; padding: 1.75rem 2rem 1.25rem; color: #fff; position: relative; overflow: hidden;">
    <div class="d-flex align-items-start justify-content-between">
        <div class="d-flex align-items-center">
            <div class="site-icon me-3" style="width: 56px; height: 56px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
                <i class="bi {{ $workplace->icon ?? 'bi-building' }}"></i>
            </div>
            <div>
                <h2 class="mb-0 fw-bold" style="font-size: 1.5rem;">{{ $workplace->name }}</h2>
                <div class="mt-1" style="opacity: 0.85; font-size: 0.875rem;">
                    <span class="me-3"><i class="bi bi-tag me-1"></i>{{ $workplace->category->name ?? '—' }}</span>
                    <span class="me-3"><i class="bi bi-code-square me-1"></i>{{ $workplace->code }}</span>
                    @if($workplace->is_public)
                        <span class="badge bg-light text-dark"><i class="bi bi-globe2 me-1"></i>Public</span>
                    @else
                        <span class="badge" style="background: rgba(255,255,255,0.2);"><i class="bi bi-lock me-1"></i>Privé</span>
                    @endif
                    @if($workplace->status === 'archived')
                        <span class="badge bg-secondary ms-1"><i class="bi bi-archive me-1"></i>Archivé</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            @can('update', $workplace)
            <a href="{{ route('workplaces.edit', $workplace) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.3);">
                <i class="bi bi-pencil-square me-1"></i>Modifier
            </a>
            @endcan
            <a href="{{ route('workplaces.settings', $workplace) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.3);">
                <i class="bi bi-gear me-1"></i>Paramètres
            </a>
        </div>
    </div>
    {{-- Decorative shapes --}}
    <div style="position: absolute; right: -40px; top: -40px; width: 180px; height: 180px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
    <div style="position: absolute; right: 60px; bottom: -60px; width: 120px; height: 120px; background: rgba(255,255,255,0.03); border-radius: 50%;"></div>
</div>

{{-- ==================== NAVIGATION TABS ==================== --}}
@php
    $activeTab = $activeTab ?? 'dashboard';
@endphp
<ul class="nav nav-tabs site-nav-tabs mb-4" style="background: #f0f3f8; border-radius: 0 0 0.5rem 0.5rem; padding: 0 1rem; border: 1px solid #dee2e6; border-top: none;">
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'dashboard' ? 'active' : '' }}" href="{{ route('workplaces.show', $workplace) }}">
            <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'folders' ? 'active' : '' }}" href="{{ route('workplaces.content.folders', $workplace) }}">
            <i class="bi bi-folder me-1"></i>Dossiers
            @if($workplace->folders_count > 0)
                <span class="badge bg-secondary rounded-pill ms-1">{{ $workplace->folders_count }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }}" href="{{ route('workplaces.content.documents', $workplace) }}">
            <i class="bi bi-file-earmark-text me-1"></i>Documents
            @if($workplace->documents_count > 0)
                <span class="badge bg-secondary rounded-pill ms-1">{{ $workplace->documents_count }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'members' ? 'active' : '' }}" href="{{ route('workplaces.members.index', $workplace) }}">
            <i class="bi bi-people me-1"></i>Membres
            @if($workplace->members_count > 0)
                <span class="badge bg-secondary rounded-pill ms-1">{{ $workplace->members_count }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'activities' ? 'active' : '' }}" href="{{ route('workplaces.activities.index', $workplace) }}">
            <i class="bi bi-clock-history me-1"></i>Activités
        </a>
    </li>
</ul>

@push('styles')
<style>
.site-nav-tabs .nav-link {
    color: #5a6370;
    border: none;
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-bottom: 2px solid transparent;
    background: transparent;
    transition: color 0.15s, border-color 0.15s;
}
.site-nav-tabs .nav-link:hover {
    color: #2c3e6b;
    border-bottom-color: #cbd3e0;
}
.site-nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom-color: #0d6efd;
    background: transparent;
}
.site-nav-tabs .nav-link .badge {
    font-size: 0.6rem;
    vertical-align: middle;
}
@media (max-width: 991.98px) {
    .site-banner {
        border-radius: 0 !important;
        padding: 1.25rem 1rem 1rem !important;
    }
    .site-banner h2 {
        font-size: 1.2rem !important;
    }
    .site-nav-tabs {
        overflow-x: auto;
        flex-wrap: nowrap;
        border-radius: 0 !important;
    }
    .site-nav-tabs .nav-item {
        white-space: nowrap;
    }
}
</style>
@endpush
