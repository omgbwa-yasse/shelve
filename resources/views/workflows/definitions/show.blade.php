@extends('layouts.app')

@push('styles')
<style>
    /* ── Timeline Steps Display ──────────────────────────────── */
    .process-timeline { list-style: none; padding: 0; margin: 0; }
    .process-timeline .tl-item {
        display: flex; align-items: flex-start; gap: 0;
        position: relative;
        margin-bottom: 0;
    }
    .process-timeline .tl-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 19px; top: 44px; bottom: -12px;
        width: 2px;
        background: #dee2e6;
        z-index: 0;
    }
    .tl-num {
        width: 40px; height: 40px; min-width: 40px;
        border-radius: 50%;
        background: #0d6efd; color: #fff;
        font-weight: 700; font-size: .9rem;
        display: flex; align-items: center; justify-content: center;
        margin-right: 14px; margin-top: 2px;
        position: relative; z-index: 1;
        flex-shrink: 0;
    }
    .tl-card {
        flex: 1;
        background: #fff; border: 1px solid #e9ecef;
        border-radius: 10px; padding: 12px 16px;
        margin-bottom: 12px;
        box-shadow: 0 1px 5px rgba(0,0,0,.04);
    }
    .tl-name { font-weight: 600; color: #1e293b; }
    .tl-meta { font-size: .78rem; color: #6c757d; margin-top: 3px; }

    .tl-start, .tl-end {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 14px; border-radius: 8px;
        font-size: .88rem; font-weight: 600;
        margin-bottom: 0;
    }
    .tl-start { background: #e0f0ff; color: #0553b1; border: 1.5px dashed #84b9f4; margin-bottom: 8px; }
    .tl-end   { background: #d1e7dd; color: #0a3622; border: 1.5px dashed #86c9a3; margin-top: 8px; }

    /* ── Section card ─────────────────────────────────────────── */
    .section-card { background:#fff; border:1px solid #e9ecef; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,.05); overflow:hidden; }
    .section-card-header { padding:14px 20px; border-bottom:1px solid #f0f2f5; display:flex; align-items:center; gap:10px; }
    .section-card-header h5 { margin:0; font-size:.93rem; font-weight:700; }
    .section-card-body { padding:20px; }

    /* ── Instance row ─────────────────────────────────────────── */
    .instance-row { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f1f3f5; }
    .instance-row:last-child { border-bottom:none; }

    /* ── Status pill ──────────────────────────────────────────── */
    .s-badge { font-size:.72rem; font-weight:700; padding:4px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px; }
    .s-active   { background:#d1e7dd; color:#0a3622; }
    .s-draft    { background:#fff3cd; color:#856404; }
    .s-archived { background:#e2e3e5; color:#383d41; }
    .s-running  { background:#cfe2ff; color:#084298; }
    .s-completed{ background:#d1e7dd; color:#0a3622; }
    .s-paused   { background:#fff3cd; color:#856404; }
    .s-cancelled{ background:#f8d7da; color:#842029; }

    /* action btn */
    .action-btn { width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-size:.85rem; border:none; cursor:pointer; text-decoration:none; transition:opacity .15s; }
    .action-btn:hover { opacity:.78; }
    .ab-view  { background:#e0f0ff; color:#0553b1; }
    .ab-edit  { background:#fff8e1; color:#c77700; }
    .ab-start { background:#d1e7dd; color:#086b3f; }
    .ab-del   { background:#f8d7da; color:#842029; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Header ─────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted small mb-1">
                <a href="{{ route('workflows.definitions.index') }}" class="text-decoration-none text-muted">
                    <i class="bi bi-diagram-3"></i> {{ __('Processus') }}
                </a>
                <i class="bi bi-chevron-right mx-1" style="font-size:.7rem;"></i>
                {{ $definition->name }}
            </p>
            <h2 class="fw-bold mb-0 d-flex align-items-center gap-2">
                {{ $definition->name }}
                @switch($definition->status)
                    @case('active')   <span class="s-badge s-active"><i class="bi bi-play-circle"></i> {{ __('Actif') }}</span> @break
                    @case('draft')    <span class="s-badge s-draft"><i class="bi bi-pencil"></i> {{ __('Brouillon') }}</span> @break
                    @case('archived') <span class="s-badge s-archived"><i class="bi bi-archive"></i> {{ __('Archivé') }}</span> @break
                @endswitch
            </h2>
        </div>
        <div class="d-flex gap-2">
            @if($definition->status === 'active')
                <a href="{{ route('workflows.instances.create') }}?definition={{ $definition->id }}"
                   class="btn btn-success d-flex align-items-center gap-1">
                    <i class="bi bi-play-fill"></i> {{ __('Démarrer') }}
                </a>
            @endif
            <a href="{{ route('workflows.definitions.edit', $definition) }}"
               class="btn btn-warning d-flex align-items-center gap-1">
                <i class="bi bi-pencil"></i> {{ __('Modifier les étapes') }}
            </a>
            <a href="{{ route('workflows.definitions.index') }}"
               class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ── Left: Steps + Instances ─────────────────── --}}
        <div class="col-lg-8">

            {{-- Steps timeline --}}
            <div class="section-card mb-4">
                <div class="section-card-header">
                    <i class="bi bi-list-ol text-primary"></i>
                    <h5>{{ __('Étapes du processus') }}</h5>
                    <a href="{{ route('workflows.definitions.edit', $definition) }}"
                       class="ms-auto btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                        <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                    </a>
                </div>
                <div class="section-card-body">
                    @php
                        $steps = [];
                        try {
                            if($definition->bpmn_xml) {
                                $xml = new SimpleXMLElement($definition->bpmn_xml);
                                $xml->registerXPathNamespace('bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');
                                $tasks = $xml->xpath('//bpmn:userTask|//bpmn:task');
                                foreach($tasks as $t) {
                                    $steps[] = (string)($t['name'] ?? $t['id']);
                                }
                            }
                        } catch(\Exception $e) {}
                    @endphp

                    @if(count($steps) > 0)
                        <div class="tl-start"><i class="bi bi-play-circle-fill fs-5"></i> {{ __('Début du processus') }}</div>
                        <ul class="process-timeline mt-2">
                            @foreach($steps as $i => $stepName)
                                <li class="tl-item">
                                    <div class="tl-num">{{ $i + 1 }}</div>
                                    <div class="tl-card">
                                        <div class="tl-name">{{ $stepName }}</div>
                                        <div class="tl-meta"><i class="bi bi-person me-1"></i>{{ __('Tâche utilisateur') }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="tl-end mt-1"><i class="bi bi-check-circle-fill fs-5"></i> {{ __('Fin du processus') }}</div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-list-ol fs-1 d-block mb-2" style="opacity:.3;"></i>
                            {{ __('Aucune étape définie.') }}
                            <a href="{{ route('workflows.definitions.edit', $definition) }}" class="d-block mt-2 text-primary">
                                {{ __('→ Ajouter des étapes') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Instances --}}
            @if($definition->instances->count() > 0)
                <div class="section-card">
                    <div class="section-card-header">
                        <i class="bi bi-collection-play text-primary"></i>
                        <h5>{{ __('Instances récentes') }}</h5>
                        <a href="{{ route('workflows.instances.index') }}" class="ms-auto text-muted small text-decoration-none">
                            {{ __('Voir tout') }} <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="section-card-body" style="padding-top:10px;">
                        @foreach($definition->instances->take(8) as $instance)
                            <div class="instance-row">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small">{{ $instance->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">
                                        {{ __('Démarré par') }} {{ $instance->starter->name ?? 'N/A' }}
                                        · {{ $instance->started_at->format('d/m/Y') }}
                                    </div>
                                </div>
                                @switch($instance->status)
                                    @case('running')   <span class="s-badge s-running"><i class="bi bi-arrow-repeat"></i> {{ __('En cours') }}</span> @break
                                    @case('completed') <span class="s-badge s-completed"><i class="bi bi-check-circle"></i> {{ __('Terminé') }}</span> @break
                                    @case('paused')    <span class="s-badge s-paused"><i class="bi bi-pause-circle"></i> {{ __('En pause') }}</span> @break
                                    @case('cancelled') <span class="s-badge s-cancelled"><i class="bi bi-x-circle"></i> {{ __('Annulé') }}</span> @break
                                @endswitch
                                <a href="{{ route('workflows.instances.show', $instance) }}" class="action-btn ab-view">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- ── Right: Info + Actions ────────────────────── --}}
        <div class="col-lg-4">

            {{-- Quick actions --}}
            <div class="section-card mb-3">
                <div class="section-card-header">
                    <i class="bi bi-lightning-charge text-warning"></i>
                    <h5>{{ __('Actions rapides') }}</h5>
                </div>
                <div class="section-card-body d-flex flex-column gap-2">
                    @if($definition->status === 'active')
                        <a href="{{ route('workflows.instances.create') }}?definition={{ $definition->id }}"
                           class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-play-fill"></i> {{ __('Démarrer une instance') }}
                        </a>
                    @endif
                    <a href="{{ route('workflows.definitions.edit', $definition) }}"
                       class="btn btn-warning w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-pencil"></i> {{ __('Modifier les étapes') }}
                    </a>
                    <form action="{{ route('workflows.definitions.destroy', $definition) }}" method="POST"
                          onsubmit="return confirm('{{ __('Supprimer ce processus ?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-trash"></i> {{ __('Supprimer') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Stats --}}
            <div class="section-card mb-3">
                <div class="section-card-header">
                    <i class="bi bi-bar-chart-line text-info"></i>
                    <h5>{{ __('Statistiques') }}</h5>
                </div>
                <div class="section-card-body">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="p-2 rounded-3" style="background:#f8f9fa;">
                                <div style="font-size:1.6rem; font-weight:700; color:#0d6efd;">
                                    {{ $definition->instances->count() }}
                                </div>
                                <div class="text-muted small">{{ __('Total') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3" style="background:#f8f9fa;">
                                <div style="font-size:1.6rem; font-weight:700; color:#198754;">
                                    {{ $definition->instances->where('status','running')->count() }}
                                </div>
                                <div class="text-muted small">{{ __('En cours') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3" style="background:#f8f9fa;">
                                <div style="font-size:1.6rem; font-weight:700; color:#198754;">
                                    {{ $definition->instances->where('status','completed')->count() }}
                                </div>
                                <div class="text-muted small">{{ __('Terminées') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3" style="background:#f8f9fa;">
                                <div style="font-size:1.6rem; font-weight:700; color:#6c757d;">
                                    {{ $definition->transitions->count() }}
                                </div>
                                <div class="text-muted small">{{ __('Transitions') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Metadata --}}
            <div class="section-card">
                <div class="section-card-header">
                    <i class="bi bi-info-circle text-secondary"></i>
                    <h5>{{ __('Informations') }}</h5>
                </div>
                <div class="section-card-body">
                    <dl class="row g-1 small mb-0">
                        <dt class="col-5 text-muted">{{ __('Version') }}</dt>
                        <dd class="col-7"><span class="badge" style="background:#e0f0ff;color:#0553b1;">v{{ $definition->version }}</span></dd>

                        <dt class="col-5 text-muted">{{ __('Créé par') }}</dt>
                        <dd class="col-7 fw-semibold">{{ $definition->creator->name ?? 'N/A' }}</dd>

                        <dt class="col-5 text-muted">{{ __('Créé le') }}</dt>
                        <dd class="col-7">{{ $definition->created_at->format('d/m/Y H:i') }}</dd>

                        @if($definition->updated_at && $definition->updated_at != $definition->created_at)
                            <dt class="col-5 text-muted">{{ __('Modifié le') }}</dt>
                            <dd class="col-7">{{ $definition->updated_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
