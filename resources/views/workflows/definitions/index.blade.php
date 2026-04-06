@extends('layouts.app')

@push('styles')
<style>
    /* ── Process cards ────────────────────────────────────────── */
    .process-card {
        border: 1px solid #e9ecef;
        border-radius: 16px;
        overflow: hidden;
        transition: transform .18s, box-shadow .18s;
        box-shadow: 0 2px 10px rgba(0,0,0,.05);
    }
    .process-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }

    .process-card .card-top {
        padding: 20px 22px 14px;
        border-bottom: 1px solid #f0f2f5;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }
    .process-card .card-top h5 {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.3;
    }
    .process-card .card-meta {
        padding: 12px 22px 10px;
        font-size: .8rem;
        color: #6c757d;
    }
    .process-card .card-footer-actions {
        padding: 10px 22px 14px;
        display: flex;
        gap: 8px;
        align-items: center;
        border-top: 1px solid #f0f2f5;
    }

    /* step preview pills */
    .step-preview {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        padding: 10px 22px 0;
    }
    .step-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #0d6efd;
        display: inline-block;
    }

    /* ── KPI row ──────────────────────────────────────────────── */
    .def-kpi {
        display: flex;
        gap: 18px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .def-kpi-item {
        flex: 1;
        min-width: 130px;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,.06);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .def-kpi-icon {
        width: 44px; height: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .kpi-total   .def-kpi-icon { background:#e0f0ff; color:#0553b1; }
    .kpi-active  .def-kpi-icon { background:#d1e7dd; color:#0a3622; }
    .kpi-draft   .def-kpi-icon { background:#fff3cd; color:#856404; }
    .kpi-archive .def-kpi-icon { background:#e2e3e5; color:#383d41; }
    .def-kpi-num { font-size: 1.8rem; font-weight: 700; line-height: 1; }
    .def-kpi-label { font-size: .75rem; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; }

    /* status badges */
    .s-badge {
        font-size: .72rem; font-weight: 700;
        padding: 4px 10px; border-radius: 20px;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .s-active   { background: #d1e7dd; color: #0a3622; }
    .s-draft    { background: #fff3cd; color: #856404; }
    .s-archived { background: #e2e3e5; color: #383d41; }

    /* action btn */
    .action-btn {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .85rem; border: none; cursor: pointer;
        text-decoration: none;
        transition: opacity .15s;
    }
    .action-btn:hover { opacity: .78; }
    .ab-view  { background: #e0f0ff; color: #0553b1; }
    .ab-start { background: #d1e7dd; color: #086b3f; }
    .ab-edit  { background: #fff8e1; color: #c77700; }
    .ab-del   { background: #f8d7da; color: #842029; }

    /* empty state */
    .empty-state { padding: 60px 20px; text-align: center; }
    .empty-icon  { font-size: 4rem; color: #dee2e6; margin-bottom: 16px; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Header ─────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3 text-primary"></i> {{ __('Processus') }}
            </h2>
            <p class="text-muted small mb-0">{{ __('Définissez vos modèles de processus et gérez leurs instances') }}</p>
        </div>
        <a href="{{ route('workflows.definitions.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> {{ __('Nouveau processus') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── KPI Row ─────────────────────────────────────── --}}
    <div class="def-kpi">
        <div class="def-kpi-item kpi-total">
            <div class="def-kpi-icon"><i class="bi bi-layers"></i></div>
            <div>
                <div class="def-kpi-num">{{ $definitions->total() }}</div>
                <div class="def-kpi-label">{{ __('Total') }}</div>
            </div>
        </div>
        <div class="def-kpi-item kpi-active">
            <div class="def-kpi-icon"><i class="bi bi-play-circle"></i></div>
            <div>
                <div class="def-kpi-num">{{ $definitions->where('status', 'active')->count() }}</div>
                <div class="def-kpi-label">{{ __('Actifs') }}</div>
            </div>
        </div>
        <div class="def-kpi-item kpi-draft">
            <div class="def-kpi-icon"><i class="bi bi-pencil-square"></i></div>
            <div>
                <div class="def-kpi-num">{{ $definitions->where('status', 'draft')->count() }}</div>
                <div class="def-kpi-label">{{ __('Brouillons') }}</div>
            </div>
        </div>
        <div class="def-kpi-item kpi-archive">
            <div class="def-kpi-icon"><i class="bi bi-archive"></i></div>
            <div>
                <div class="def-kpi-num">{{ $definitions->where('status', 'archived')->count() }}</div>
                <div class="def-kpi-label">{{ __('Archivés') }}</div>
            </div>
        </div>
    </div>

    {{-- ── Process cards grid ──────────────────────────── --}}
    @if($definitions->count() > 0)
        <div class="row g-3 mb-3">
            @foreach($definitions as $definition)
                <div class="col-md-6 col-lg-4">
                    <div class="process-card h-100">
                        {{-- Top --}}
                        <div class="card-top">
                            <div>
                                <h5>
                                    <a href="{{ route('workflows.definitions.show', $definition) }}" class="text-decoration-none text-dark">
                                        {{ $definition->name }}
                                    </a>
                                </h5>
                                @if($definition->description)
                                    <p class="text-muted small mb-0 mt-1" style="line-height:1.4;">
                                        {{ Str::limit($definition->description, 80) }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex-shrink-0 d-flex flex-column align-items-end gap-1">
                                @switch($definition->status)
                                    @case('active')
                                        <span class="s-badge s-active"><i class="bi bi-play-circle"></i> {{ __('Actif') }}</span>
                                        @break
                                    @case('draft')
                                        <span class="s-badge s-draft"><i class="bi bi-pencil"></i> {{ __('Brouillon') }}</span>
                                        @break
                                    @case('archived')
                                        <span class="s-badge s-archived"><i class="bi bi-archive"></i> {{ __('Archivé') }}</span>
                                        @break
                                @endswitch
                                <span class="text-muted" style="font-size:.72rem;">v{{ $definition->version }}</span>
                            </div>
                        </div>

                        {{-- Meta --}}
                        <div class="card-meta d-flex gap-3">
                            <span title="{{ __('Instances en cours') }}">
                                <i class="bi bi-play-circle text-primary me-1"></i>
                                <strong>{{ $definition->instances->where('status','running')->count() }}</strong> {{ __('en cours') }}
                            </span>
                            <span title="{{ __('Instances terminées') }}">
                                <i class="bi bi-check-circle text-success me-1"></i>
                                <strong>{{ $definition->instances->where('status','completed')->count() }}</strong> {{ __('terminées') }}
                            </span>
                            <span class="ms-auto" title="{{ __('Créé par') }}">
                                <i class="bi bi-person me-1"></i>{{ $definition->creator->name ?? 'N/A' }}
                            </span>
                        </div>

                        {{-- Footer actions --}}
                        <div class="card-footer-actions">
                            @if($definition->status === 'active')
                                <a href="{{ route('workflows.instances.create') }}?definition={{ $definition->id }}"
                                   class="btn btn-sm btn-primary d-flex align-items-center gap-1 me-1">
                                    <i class="bi bi-play-fill"></i> {{ __('Démarrer') }}
                                </a>
                            @endif
                            <a href="{{ route('workflows.definitions.show', $definition) }}" class="action-btn ab-view" title="{{ __('Voir') }}"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('workflows.definitions.edit', $definition) }}" class="action-btn ab-edit" title="{{ __('Modifier les étapes') }}"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('workflows.definitions.destroy', $definition) }}" method="POST" class="ms-auto"
                                  onsubmit="return confirm('{{ __('Supprimer ce processus ?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn ab-del" title="{{ __('Supprimer') }}"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-2">
            {{ $definitions->links() }}
        </div>

    @else
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-diagram-3"></i></div>
                <h5 class="fw-semibold text-muted">{{ __('Aucun processus créé') }}</h5>
                <p class="text-muted small">{{ __('Créez votre premier modèle de processus pour commencer.') }}</p>
                <a href="{{ route('workflows.definitions.create') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('Créer un processus') }}
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
