@extends('layouts.app')

@section('content')
<div class="container-fluid">

    @include('workplaces.partials.site-header', ['activeTab' => 'activities'])

    {{-- ==================== FILTERS ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('workplaces.activities.index', $workplace) }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Type d'activité</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Toutes les activités</option>
                        @php
                            $activityTypes = [
                                'shared_folder'   => 'Dossier partagé',
                                'shared_document' => 'Document partagé',
                                'deleted_folder'  => 'Dossier retiré',
                                'deleted_document'=> 'Document retiré',
                                'member_added'    => 'Membre ajouté',
                                'member_removed'  => 'Membre retiré',
                                'settings_updated'=> 'Paramètres modifiés',
                            ];
                        @endphp
                        @foreach($activityTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Du</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Au</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Membre</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Tous les membres</option>
                        @foreach($workplace->members as $member)
                            <option value="{{ $member->user_id }}" {{ request('user_id') == $member->user_id ? 'selected' : '' }}>
                                {{ $member->user->name ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                    @if(request()->hasAny(['type', 'user_id', 'date_from', 'date_to']))
                        <a href="{{ route('workplaces.activities.index', $workplace) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== ACTIVITY TIMELINE ==================== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center" style="background: #fafbfc; border-bottom: 1px solid #eef0f3; padding: 0.65rem 1.25rem;">
            <div>
                <i class="bi bi-activity me-2 text-success"></i>
                <span class="fw-semibold" style="font-size: 0.875rem;">Historique des activités</span>
                <span class="badge bg-secondary rounded-pill ms-2">{{ $activities->total() }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            @forelse($activities as $activity)
            <div class="activity-item d-flex align-items-start px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="transition: background 0.15s;">
                {{-- Timeline dot + icon --}}
                <div class="me-3 mt-1 position-relative">
                    @php
                        $typeConfig = [
                            'shared_folder'    => ['icon' => 'bi-folder-plus',        'bg' => '#d1fae5', 'color' => '#059669'],
                            'shared_document'  => ['icon' => 'bi-file-earmark-plus',  'bg' => '#dbeafe', 'color' => '#2563eb'],
                            'deleted_folder'   => ['icon' => 'bi-folder-minus',       'bg' => '#fee2e2', 'color' => '#dc2626'],
                            'deleted_document' => ['icon' => 'bi-file-earmark-minus', 'bg' => '#fee2e2', 'color' => '#dc2626'],
                            'member_added'     => ['icon' => 'bi-person-plus',        'bg' => '#e0e7ff', 'color' => '#4f46e5'],
                            'member_removed'   => ['icon' => 'bi-person-dash',        'bg' => '#fef3c7', 'color' => '#d97706'],
                            'settings_updated' => ['icon' => 'bi-gear',               'bg' => '#f3f4f6', 'color' => '#6b7280'],
                        ];
                        $cfg = $typeConfig[$activity->activity_type] ?? ['icon' => 'bi-circle-fill', 'bg' => '#f3f4f6', 'color' => '#6b7280'];
                    @endphp
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: {{ $cfg['bg'] }};">
                        <i class="bi {{ $cfg['icon'] }}" style="font-size: 0.9rem; color: {{ $cfg['color'] }};"></i>
                    </div>
                    @if(!$loop->last)
                        <div style="position: absolute; left: 50%; top: 36px; bottom: -12px; width: 2px; background: #e5e7eb; transform: translateX(-50%);"></div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="fw-semibold small">{{ $activity->user->name ?? 'Système' }}</span>
                            <span class="text-muted small ms-1">{{ $activity->description }}</span>
                        </div>
                        <span class="text-muted flex-shrink-0 ms-3" style="font-size: 0.7rem; white-space: nowrap;">
                            <i class="bi bi-clock me-1"></i>{{ $activity->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge rounded-pill" style="font-size: 0.6rem; background: {{ $cfg['bg'] }}; color: {{ $cfg['color'] }};">
                            {{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}
                        </span>
                        <span class="text-muted" style="font-size: 0.65rem;">
                            {{ $activity->created_at->translatedFormat('d M Y à H:i') }}
                        </span>
                        @if($activity->ip_address)
                            <span class="text-muted" style="font-size: 0.6rem;">
                                <i class="bi bi-geo-alt me-1"></i>{{ $activity->ip_address }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="bi bi-clock-history d-block mb-3" style="font-size: 3rem; opacity: 0.15;"></i>
                <h6 class="text-muted">Aucune activité enregistrée</h6>
                <p class="text-muted small mb-0">Les actions effectuées dans cet espace apparaîtront ici</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($activities->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-2 px-4">
            <small class="text-muted">
                Affichage de {{ $activities->firstItem() }} à {{ $activities->lastItem() }} sur {{ $activities->total() }} activités
            </small>
            {{ $activities->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.activity-item:hover {
    background: #f8f9fa;
}
</style>
@endpush
