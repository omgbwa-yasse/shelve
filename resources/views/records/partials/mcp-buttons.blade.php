{{-- 
    Boutons MCP pour les records
    Usage: @include('records.partials.mcp-buttons', ['record' => $record, 'style' => 'individual|batch'])
--}}

@php
    $style = $style ?? 'individual';
    $size = $size ?? 'sm';
    $showLabels = $showLabels ?? true;
@endphp

@if($style === 'individual' && isset($record))
    {{-- Boutons pour un record individuel --}}
    <div class="btn-group" role="group" aria-label="Actions MCP">
        {{-- Reformulation de titre --}}
        <button type="button" 
                class="btn btn-{{ $size }} btn-outline-primary mcp-action-btn" 
                data-action="title" 
                data-record-id="{{ $record->id }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                title="{{ __('Reformuler le titre selon les règles ISAD(G)') }}">
            <i class="bi bi-magic"></i>
            @if($showLabels) {{ __('reformulate_title') ?? 'Reformuler' }} @endif
        </button>

        {{-- Indexation thésaurus --}}
        <button type="button" 
                class="btn btn-{{ $size }} btn-outline-success mcp-action-btn" 
                data-action="thesaurus" 
                data-record-id="{{ $record->id }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                title="{{ __('Indexation automatique avec le thésaurus') }}">
            <i class="bi bi-tags"></i>
            @if($showLabels) {{ __('index_thesaurus') ?? 'Indexer' }} @endif
        </button>

        {{-- Résumé ISAD(G) --}}
        <button type="button" 
                class="btn btn-{{ $size }} btn-outline-info mcp-action-btn" 
                data-action="summary" 
                data-record-id="{{ $record->id }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                title="{{ __('Générer le résumé ISAD(G) - Élément 3.3.1') }}">
            <i class="bi bi-file-text"></i>
            @if($showLabels) {{ __('generate_summary') ?? 'Résumé' }} @endif
        </button>

        {{-- Traitement complet --}}
        <div class="btn-group" role="group">
            <button type="button" 
                    class="btn btn-{{ $size }} btn-outline-warning dropdown-toggle mcp-batch-btn" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"
                    data-record-id="{{ $record->id }}">
                <i class="bi bi-cpu"></i>
                @if($showLabels) {{ __('mcp_complete') ?? 'Complet' }} @endif
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item mcp-action-btn" 
                       href="#" 
                       data-action="all-preview" 
                       data-record-id="{{ $record->id }}">
                        <i class="bi bi-eye me-2"></i>{{ __('preview_all') ?? 'Prévisualiser tout' }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item mcp-action-btn" 
                       href="#" 
                       data-action="all-apply" 
                       data-record-id="{{ $record->id }}">
                        <i class="bi bi-check-circle me-2"></i>{{ __('apply_all') ?? 'Appliquer tout' }}
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" 
                       href="/admin/mcp" 
                       target="_blank">
                        <i class="bi bi-gear me-2"></i>{{ __('mcp_settings') ?? 'Configuration MCP' }}
                    </a>
                </li>
            </ul>
        </div>
    </div>

@elseif($style === 'batch')
    {{-- Boutons pour traitement par lots --}}
    <div class="btn-group" role="group" aria-label="Actions MCP par lots">
        <button type="button" 
                class="btn btn-{{ $size }} btn-outline-primary" 
                data-bs-toggle="modal" 
                data-bs-target="#mcpBatchModal">
            <i class="bi bi-layers"></i>
            @if($showLabels) {{ __('mcp_batch_process') ?? 'Traitement MCP par lots' }} @endif
        </button>
        
        <button type="button" 
                class="btn btn-{{ $size }} btn-outline-info" 
                onclick="window.open('/admin/mcp', '_blank')">
            <i class="bi bi-speedometer2"></i>
            @if($showLabels) {{ __('mcp_dashboard') ?? 'Dashboard MCP' }} @endif
        </button>
    </div>

@elseif($style === 'edit')
    {{-- Boutons pour la vue d'édition --}}
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" 
                class="btn btn-{{ $size }} btn-outline-primary mcp-action-btn" 
                data-action="title-preview" 
                data-record-id="{{ $record->id ?? '' }}">
            <i class="bi bi-magic me-1"></i>{{ __('suggest_title') ?? 'Suggérer un titre' }}
        </button>
        
        <button type="button" 
                class="btn btn-{{ $size }} btn-outline-success mcp-action-btn" 
                data-action="thesaurus-suggest" 
                data-record-id="{{ $record->id ?? '' }}">
            <i class="bi bi-tags me-1"></i>{{ __('suggest_keywords') ?? 'Suggérer des mots-clés' }}
        </button>
        
        <button type="button" 
                class="btn btn-{{ $size }} btn-outline-info mcp-action-btn" 
                data-action="summary-preview" 
                data-record-id="{{ $record->id ?? '' }}">
            <i class="bi bi-file-text me-1"></i>{{ __('generate_content') ?? 'Générer le contenu' }}
        </button>
    </div>
@endif

{{-- Styles CSS pour les boutons MCP --}}
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
</style>
@endpush

{{-- Scripts JavaScript pour les actions MCP --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gestionnaire pour les boutons d'action MCP
    document.querySelectorAll('.mcp-action-btn').forEach(button => {
        button.addEventListener('click', handleMcpAction);
    });
});

function handleMcpAction(event) {
    event.preventDefault();
    
    const button = event.currentTarget;
    const action = button.dataset.action;
    const recordId = button.dataset.recordId;
    
    if (!recordId) {
        showMcpNotification('Erreur: ID du record manquant', 'error');
        return;
    }
    
    // Désactiver le bouton pendant le traitement
    setButtonState(button, 'processing');
    
    // Déterminer l'endpoint selon l'action
    let endpoint, method = 'POST', isPreview = action.includes('preview');
    
    switch(action) {
        case 'title':
        case 'title-preview':
            endpoint = `/api/mcp/records/${recordId}/title/${isPreview ? 'preview' : 'reformulate'}`;
            break;
        case 'thesaurus':
        case 'thesaurus-suggest':
            endpoint = `/api/mcp/records/${recordId}/thesaurus/index`;
            break;
        case 'summary':
        case 'summary-preview':
            endpoint = `/api/mcp/records/${recordId}/summary/${isPreview ? 'preview' : 'generate'}`;
            break;
        case 'all-preview':
            endpoint = `/api/mcp/records/${recordId}/preview`;
            break;
        case 'all-apply':
            endpoint = `/api/mcp/records/${recordId}/process`;
            break;
        default:
            setButtonState(button, 'error');
            showMcpNotification('Action inconnue: ' + action, 'error');
            return;
    }
    
    // Effectuer la requête
    fetch(endpoint, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            features: action.startsWith('all') ? ['title', 'thesaurus', 'summary'] : [action.split('-')[0]]
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.message || 'Erreur inconnue');
        }
        
        setButtonState(button, 'success');
        showMcpNotification(data.message || 'Traitement réussi', 'success');
        
        // Afficher les résultats selon le type d'action
        if (isPreview || action.startsWith('all-preview')) {
            showMcpPreview(data);
        } else {
            // Recharger la page après un délai pour voir les changements
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    })
    .catch(error => {
        setButtonState(button, 'error');
        showMcpNotification('Erreur: ' + error.message, 'error');
        console.error('Erreur MCP:', error);
    });
}

function setButtonState(button, state) {
    // Retirer toutes les classes d'état
    button.classList.remove('mcp-processing', 'mcp-success', 'mcp-error');
    
    // Retirer les spinners existants
    const existingSpinner = button.querySelector('.spinner-border');
    if (existingSpinner) {
        existingSpinner.remove();
    }
    
    switch(state) {
        case 'processing':
            button.classList.add('mcp-processing');
            button.disabled = true;
            button.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-1" role="status"></span>');
            break;
        case 'success':
            button.classList.add('mcp-success');
            button.disabled = false;
            setTimeout(() => {
                button.classList.remove('mcp-success');
            }, 3000);
            break;
        case 'error':
            button.classList.add('mcp-error');
            button.disabled = false;
            setTimeout(() => {
                button.classList.remove('mcp-error');
            }, 5000);
            break;
        default:
            button.disabled = false;
    }
}

function showMcpNotification(message, type = 'info') {
    // Créer une notification toast
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
    
    const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${bgClass} text-white border-0">
                <i class="bi bi-robot me-2"></i>
                <strong class="me-auto">MCP</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toast = new bootstrap.Toast(document.getElementById(toastId));
    toast.show();
    
    // Supprimer le toast après fermeture
    document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}

function showMcpPreview(data) {
    // Créer ou mettre à jour une modale de prévisualisation
    let modal = document.getElementById('mcpPreviewModal');
    if (!modal) {
        modal = createPreviewModal();
    }
    
    const modalBody = modal.querySelector('.modal-body');
    let content = '<h6>Aperçu des modifications :</h6>';
    
    if (data.previews) {
        Object.entries(data.previews).forEach(([feature, preview]) => {
            content += formatPreviewContent(feature, preview);
        });
    } else if (data.preview) {
        content += formatPreviewContent('single', data.preview);
    }
    
    modalBody.innerHTML = content;
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

function formatPreviewContent(feature, preview) {
    let content = `<div class="mb-3 border rounded p-3">`;
    content += `<h6 class="text-primary">${feature.charAt(0).toUpperCase() + feature.slice(1)}</h6>`;
    
    if (typeof preview === 'object') {
        if (preview.original_title && preview.suggested_title) {
            content += `
                <div class="row">
                    <div class="col-6">
                        <strong>Actuel :</strong><br>
                        <span class="text-muted">${preview.original_title}</span>
                    </div>
                    <div class="col-6">
                        <strong>Suggéré :</strong><br>
                        <span class="text-success">${preview.suggested_title}</span>
                    </div>
                </div>`;
        } else if (preview.concepts_found !== undefined) {
            content += `<p><strong>Concepts trouvés :</strong> ${preview.concepts_found}</p>`;
            if (preview.concepts && preview.concepts.length > 0) {
                content += '<p><strong>Principaux concepts :</strong></p><ul>';
                preview.concepts.slice(0, 5).forEach(concept => {
                    content += `<li>${concept.preferred_label} (${Math.round(concept.weight * 100)}%)</li>`;
                });
                content += '</ul>';
            }
        } else {
            content += `<pre class="bg-light p-2 rounded">${JSON.stringify(preview, null, 2)}</pre>`;
        }
    } else {
        content += `<p class="bg-light p-2 rounded">${preview}</p>`;
    }
    
    content += '</div>';
    return content;
}

function createPreviewModal() {
    const modalHtml = `
        <div class="modal fade" id="mcpPreviewModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-robot me-2"></i>Aperçu MCP
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" onclick="applyPreviewChanges()">Appliquer les modifications</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    return document.getElementById('mcpPreviewModal');
}

function applyPreviewChanges() {
    // Cette fonction peut être implémentée pour appliquer directement les changements
    showMcpNotification('Fonctionnalité à implémenter : application directe des changements', 'info');
}
</script>
@endpush