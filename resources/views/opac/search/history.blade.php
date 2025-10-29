@extends('opac.layouts.app')

@section('title', 'Historique des recherches')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-clock-history me-2"></i>
                        Historique des recherches
                    </h1>
                    <p class="text-muted mb-0">Retrouvez toutes vos recherches précédentes</p>
                </div>
                <a href="{{ route('opac.search') }}" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>
                    Nouvelle recherche
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center">
                            <div class="h4 text-primary mb-1">{{ $totalSearches }}</div>
                            <small class="text-muted">Recherches totales</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center">
                            <div class="h4 text-success mb-1">{{ $recentSearches }}</div>
                            <small class="text-muted">Cette semaine</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center">
                            <div class="h4 text-info mb-1">{{ $popularTerms->count() }}</div>
                            <small class="text-muted">Termes uniques</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Search History -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-list-ul me-2"></i>
                                Historique détaillé
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @if($searchHistory->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($searchHistory as $search)
                                        <div class="list-group-item">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <h6 class="mb-1">
                                                        <i class="bi bi-search text-primary me-1"></i>
                                                        {{ $search->search_term }}
                                                    </h6>
                                                    @if($search->filters && !empty(json_decode($search->filters, true)))
                                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                                            @foreach(json_decode($search->filters, true) as $key => $value)
                                                                @if($value)
                                                                    <span class="badge bg-secondary small">
                                                                        {{ ucfirst($key) }}: {{ is_array($value) ? implode(', ', $value) : $value }}
                                                                    </span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-3 text-center">
                                                    <span class="badge {{ $search->results_count > 0 ? 'bg-success' : 'bg-warning' }}">
                                                        {{ $search->results_count }} résultat{{ $search->results_count > 1 ? 's' : '' }}
                                                    </span>
                                                </div>
                                                <div class="col-md-3 text-end">
                                                    <small class="text-muted d-block">
                                                        {{ $search->created_at->format('d/m/Y') }}
                                                    </small>
                                                    <small class="text-muted">
                                                        {{ $search->created_at->format('H:i') }}
                                                    </small>
                                                    <div class="mt-1">
                                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                                onclick="repeatSearch('{{ $search->search_term }}', '{{ $search->filters }}')"
                                                                title="Refaire cette recherche">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteSearch({{ $search->id }})"
                                                                title="Supprimer de l'historique">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Pagination -->
                                @if($searchHistory->hasPages())
                                    <div class="p-3 border-top">
                                        {{ $searchHistory->links() }}
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-search display-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucune recherche dans l'historique</h5>
                                    <p class="text-muted mb-4">
                                        Vos recherches apparaîtront ici au fur et à mesure que vous les effectuez.
                                    </p>
                                    <a href="{{ route('opac.search') }}" class="btn btn-primary">
                                        Commencer une recherche
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Popular Terms Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-star me-2"></i>
                                Termes populaires
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($popularTerms->count() > 0)
                                <div class="d-flex flex-column gap-2">
                                    @foreach($popularTerms as $term)
                                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                            <span class="fw-medium">{{ $term->search_term }}</span>
                                            <span class="badge bg-primary rounded-pill">{{ $term->count }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Vos termes de recherche les plus fréquents
                                    </small>
                                </div>
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-bar-chart mb-2 display-6"></i>
                                    <div>Aucune donnée disponible</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-lightning me-2"></i>
                                Actions rapides
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('opac.search') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-search me-1"></i>
                                    Recherche avancée
                                </a>
                                <a href="{{ route('opac.records.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-collection me-1"></i>
                                    Parcourir les documents
                                </a>
                                <button class="btn btn-outline-danger" onclick="confirmClearHistory()">
                                    <i class="bi bi-trash me-1"></i>
                                    Vider l'historique
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Contenu dynamique -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="modalConfirm">Confirmer</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function repeatSearch(searchTerm, filters) {
    // Construire l'URL de recherche avec les paramètres
    let url = "{{ route('opac.search') }}";
    let params = new URLSearchParams();
    
    if (searchTerm) {
        params.append('q', searchTerm);
    }
    
    if (filters && filters !== 'null') {
        try {
            const filterObj = JSON.parse(filters);
            Object.keys(filterObj).forEach(key => {
                if (filterObj[key]) {
                    params.append(key, filterObj[key]);
                }
            });
        } catch (e) {
            console.warn('Erreur lors du parsing des filtres:', e);
        }
    }
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    window.location.href = url;
}

function deleteSearch(searchId) {
    $('#modalBody').html(`
        <p>Êtes-vous sûr de vouloir supprimer cette recherche de votre historique ?</p>
        <p class="text-muted small">Cette action est irréversible.</p>
    `);
    
    $('#modalConfirm').off('click').on('click', function() {
        // Ici on ferait un appel AJAX pour supprimer la recherche
        fetch(`/opac/search/history/${searchId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression');
        });
        
        $('#confirmModal').modal('hide');
    });
    
    $('#confirmModal').modal('show');
}

function confirmClearHistory() {
    $('#modalBody').html(`
        <p><strong>Attention !</strong> Vous êtes sur le point de supprimer tout votre historique de recherche.</p>
        <p class="text-muted">Cette action est irréversible. Toutes vos recherches précédentes seront définitivement supprimées.</p>
    `);
    
    $('#modalConfirm').off('click').on('click', function() {
        fetch('/opac/search/history/clear', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression de l\'historique');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression de l\'historique');
        });
        
        $('#confirmModal').modal('hide');
    });
    
    $('#confirmModal').modal('show');
}
</script>
@endpush

@push('styles')
<style>
.card {
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.display-6 {
    font-size: 3rem;
}

@media (max-width: 768px) {
    .col-md-3.text-end {
        text-align: left !important;
        margin-top: 1rem;
    }
    
    .col-md-3.text-center {
        text-align: left !important;
        margin-top: 0.5rem;
    }
}
</style>
@endpush