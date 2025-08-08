{{-- 
    Boutons MCP pour les records avec support test Mistral
    Usage: @include('records.partials.mcp-buttons-test', ['record' => $record, 'style' => 'individual|batch', 'mode' => 'mcp|mistral'])
--}}

@php
    $style = $style ?? 'individual';
    $size = $size ?? 'sm';
    $showLabels = $showLabels ?? true;
    // Déterminer le mode à partir des paramètres globaux si non fourni
    if (!isset($mode)) {
        try {
            $provider = app(\App\Services\SettingService::class)->get('ai_default_provider', 'ollama');
            $mode = $provider === 'mistral' ? 'mistral' : 'mcp';
        } catch (\Throwable $e) {
            $mode = 'mcp';
        }
    }
    $apiPrefix = $mode === 'mistral' ? '/api/mistral-test' : '/api/mcp';
    // ID du record sécurisé (évite les warnings si $record est absent ou invalide)
    $recordId = '';
    try {
        $recordId = isset($record) && is_object($record) && isset($record->id) ? (string)$record->id : '';
    } catch (\Throwable $e) {
        $recordId = '';
    }
@endphp

@if($style === 'individual' && isset($record))
    {{-- Boutons pour un record individuel --}}
    <div class="btn-group" role="group" aria-label="Actions {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }}">
        {{-- Reformulation de titre --}}
        <button type="button" 
                class="btn btn-{{ $size }} {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-primary' }} mcp-action-btn" 
                data-action="title" 
                data-record-id="{{ $recordId }}"
                data-api-prefix="{{ $apiPrefix }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                title="{{ __('Reformuler le titre selon les règles ISAD(G)') }} - {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }}">
            <i class="bi bi-magic"></i>
            @if($showLabels) {{ __('reformulate_title') ?? 'Reformuler' }} @endif
        </button>

        {{-- Indexation thésaurus --}}
        <button type="button" 
                class="btn btn-{{ $size }} {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-success' }} mcp-action-btn" 
                data-action="thesaurus" 
                data-record-id="{{ $recordId }}"
                data-api-prefix="{{ $apiPrefix }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                title="{{ __('Indexation automatique avec le thésaurus') }} - {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }}">
            <i class="bi bi-tags"></i>
            @if($showLabels) {{ __('index_thesaurus') ?? 'Indexer' }} @endif
        </button>

        {{-- Résumé ISAD(G) --}}
        <button type="button" 
                class="btn btn-{{ $size }} {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-info' }} mcp-action-btn" 
                data-action="summary" 
                data-record-id="{{ $recordId }}"
                data-api-prefix="{{ $apiPrefix }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                title="{{ __('Générer le résumé ISAD(G) - Élément 3.3.1') }} - {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }}">
            <i class="bi bi-file-text"></i>
            @if($showLabels) {{ __('generate_summary') ?? 'Résumé' }} @endif
        </button>

        {{-- Traitement complet --}}
        <div class="btn-group" role="group">
            <button type="button" 
                    class="btn btn-{{ $size }} {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-warning' }} dropdown-toggle mcp-batch-btn" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"
                    data-record-id="{{ $recordId }}"
                    data-api-prefix="{{ $apiPrefix }}">
                <i class="bi bi-cpu"></i>
                @if($showLabels) {{ __('mcp_complete') ?? 'Complet' }} @endif
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item mcp-action-btn" 
                       href="#" 
                       data-action="all-preview" 
                        data-record-id="{{ $recordId }}"
                       data-api-prefix="{{ $apiPrefix }}">
                        <i class="bi bi-eye me-2"></i>{{ __('preview_all') ?? 'Prévisualiser tout' }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item mcp-action-btn" 
                       href="#" 
                       data-action="all-apply" 
                        data-record-id="{{ $recordId }}"
                       data-api-prefix="{{ $apiPrefix }}">
                        <i class="bi bi-check-circle me-2"></i>{{ __('apply_all') ?? 'Appliquer tout' }}
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" 
                       href="{{ $mode === 'mistral' ? '/admin/mistral-test' : '/admin/mcp' }}" 
                       target="_blank">
                        <i class="bi bi-gear me-2"></i>{{ $mode === 'mistral' ? 'Configuration Mistral' : 'Configuration MCP' }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" 
                       href="/admin/mistral-test/compare" 
                       target="_blank">
                        <i class="bi bi-bar-chart me-2"></i>Comparer MCP vs Mistral
                    </a>
                </li>
            </ul>
        </div>
    </div>

@elseif($style === 'batch')
    {{-- Boutons pour traitement par lots --}}
    <div class="btn-group" role="group" aria-label="Actions {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }} par lots">
        <button type="button" 
                class="btn btn-{{ $size }} {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-primary' }}" 
                data-bs-toggle="modal" 
                data-bs-target="#mcpBatchModal"
                data-mode="{{ $mode }}">
            <i class="bi bi-layers"></i>
            @if($showLabels) {{ ($mode === 'mistral' ? 'Test Mistral' : 'Traitement MCP') . ' par lots' }} @endif
        </button>
        
        <button type="button" 
                class="btn btn-{{ $size }} btn-outline-info" 
                onclick="window.open('{{ $mode === 'mistral' ? '/admin/mistral-test' : '/admin/mcp' }}', '_blank')">
            <i class="bi bi-speedometer2"></i>
            @if($showLabels) {{ $mode === 'mistral' ? 'Dashboard Mistral' : 'Dashboard MCP' }} @endif
        </button>
    </div>

@elseif($style === 'edit')
    {{-- Boutons pour la vue d'édition --}}
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" 
                class="btn btn-{{ $size }} {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-primary' }} mcp-action-btn" 
                data-action="title-preview" 
                 data-record-id="{{ $recordId }}"
                data-api-prefix="{{ $apiPrefix }}">
            <i class="bi bi-magic me-1"></i>{{ __('suggest_title') ?? 'Suggérer un titre' }}
        </button>
        
        <button type="button" 
                class="btn btn-{{ $size }} {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-success' }} mcp-action-btn" 
                data-action="thesaurus-suggest" 
                 data-record-id="{{ $recordId }}"
                data-api-prefix="{{ $apiPrefix }}">
            <i class="bi bi-tags me-1"></i>{{ __('suggest_keywords') ?? 'Suggérer des mots-clés' }}
        </button>
        
        <button type="button" 
                class="btn btn-{{ $size }} {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-info' }} mcp-action-btn" 
                data-action="summary-preview" 
                 data-record-id="{{ $recordId }}"
                data-api-prefix="{{ $apiPrefix }}">
            <i class="bi bi-file-text me-1"></i>{{ __('generate_content') ?? 'Générer le contenu' }}
        </button>
    </div>

@elseif($style === 'edit-title')
    {{-- Bouton spécifique pour reformuler le titre --}}
    <div class="btn-group btn-group-sm">
        <button type="button" 
                class="btn {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-primary' }} mcp-action-btn" 
                data-action="title-preview" 
             data-record-id="{{ $recordId }}"
                data-api-prefix="{{ $apiPrefix }}"
                data-bs-toggle="tooltip" 
                title="Reformuler selon ISAD(G) - {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }}">
            <i class="bi bi-magic"></i> {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }} Reformuler
        </button>
    </div>

@elseif($style === 'edit-summary')
    {{-- Bouton spécifique pour générer le résumé --}}
    <div class="btn-group btn-group-sm">
        <button type="button" 
                class="btn {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-info' }} mcp-action-btn" 
                data-action="summary-preview" 
             data-record-id="{{ isset($record) ? $record->id : '' }}"
                data-api-prefix="{{ $apiPrefix }}"
                data-bs-toggle="tooltip" 
                title="Générer le résumé ISAD(G) - {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }}">
            <i class="bi bi-file-text"></i> {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }} Générer résumé
        </button>
    </div>

@elseif($style === 'edit-thesaurus')
    {{-- Bouton spécifique pour suggérer des mots-clés --}}
    <button type="button" 
            class="btn {{ $mode === 'mistral' ? 'btn-outline-warning' : 'btn-outline-success' }} btn-sm mcp-action-btn" 
            data-action="thesaurus-suggest" 
            data-record-id="{{ $recordId }}"
            data-api-prefix="{{ $apiPrefix }}"
            data-bs-toggle="tooltip" 
            title="Extraire des mots-clés automatiquement - {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }}">
        <i class="bi bi-tags"></i> {{ $mode === 'mistral' ? 'Mistral' : 'MCP' }} Suggérer mots-clés
    </button>
@endif

{{-- Styles CSS pour les boutons --}}
@push('styles')
<style>
.mcp-action-btn {
    transition: all 0.2s ease;
}

.mcp-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.mcp-processing {
    pointer-events: none;
    opacity: 0.6;
}

.mcp-processing .spinner-border {
    width: 1rem;
    height: 1rem;
}

.mcp-success {
    background-color: #d4edda !important;
    border-color: #c3e6cb !important;
    color: #155724 !important;
}

.mcp-error {
    background-color: #f8d7da !important;
    border-color: #f5c6cb !important;
    color: #721c24 !important;
}

.mistral-mode {
    border-left: 3px solid #ffc107;
    background-color: #fff8e1;
}

.btn-check:checked + .btn-outline-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}
</style>
@endpush

{{-- Note: Les scripts JavaScript pour les boutons MCP sont maintenant dans le fichier principal --}}