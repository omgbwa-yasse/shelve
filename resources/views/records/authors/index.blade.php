@extends('layouts.app')
<style>
                /* Grille des auteurs - 2 par ligne */
            .author-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
                padding: 1.5rem;
            }
    
    /* Carte d'auteur */
    .author-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .author-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border-color: #0d6efd;
    }
    
    /* En-tête de la carte */
    .author-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .author-title h6 {
        color: #212529;
        margin-bottom: 0.5rem;
    }
    
    /* Corps de la carte */
    .author-card-body {
        padding: 1rem;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .author-card-body:hover {
        background-color: #f8f9fa;
    }
    
    /* Informations de base */
    .info-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .info-item:last-child {
        margin-bottom: 0;
    }
    
    /* Section des archives */
    .archive-section {
        border-top: 1px solid #f1f3f4;
        padding-top: 0.75rem;
    }
    
    .archive-header {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .archive-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .archive-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border: 1px solid #dee2e6;
        background-color: #f8f9fa;
        color: #495057;
        transition: all 0.2s ease;
    }
    
    .archive-badge:hover {
        background-color: #e9ecef;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Compteur de documents */
    .records-count .badge {
        font-size: 0.75rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
    }
    
    /* Pied de la carte */
    .author-card-footer {
        padding: 1rem;
        border-top: 1px solid #f1f3f4;
        background-color: #f8f9fa;
    }
    
    /* Badges et boutons */
    .badge {
        font-weight: 500;
        font-size: 0.75rem;
    }
    
    .btn-light:hover {
        background-color: #e9ecef;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    /* Alertes */
    .alert {
        border: none;
        border-radius: 0;
    }
    
                /* Responsive design */
            @media (max-width: 768px) {
                .author-grid {
                    grid-template-columns: 1fr;
                    padding: 1rem;
                    gap: 1rem;
                }
                
                .archive-badges {
                    flex-direction: column;
                }
                
                .archive-badge {
                    margin-bottom: 0.25rem;
                }
                
                .col-md-6 {
                    margin-bottom: 1rem;
                }
            }
            
            @media (max-width: 1200px) {
                .author-grid {
                    grid-template-columns: 1fr;
                }
            }
    
    @media (max-width: 576px) {
        .author-grid {
            padding: 0.5rem;
        }
        
        .author-card-header,
        .author-card-body,
        .author-card-footer {
            padding: 0.75rem;
        }
    }
</style>
@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people-fill me-2"></i>
                        {{ __('physical_legal_persons_list') }}
                    </h5>
                    <a href="{{ route('record-author.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>
                        {{ __('add_author') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="p-3 border-bottom">
                    <div class="row g-3">
                        <!-- Barre de recherche -->
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control border-start-0"
                                       id="searchInput"
                                       placeholder="{{ __('search_authors') }}">
                            </div>
                        </div>
                        
                        <!-- Options de tri -->
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <select class="form-select" id="sortBy" style="max-width: 150px;">
                                    <option value="records_count">{{ __('records_count') }}</option>
                                    <option value="name">{{ __('name') }}</option>
                                    <option value="type">{{ __('type') }}</option>
                                    <option value="lifespan">{{ __('lifespan') }}</option>
                                </select>
                                
                                <select class="form-select" id="sortOrder" style="max-width: 120px;">
                                    <option value="desc">{{ __('descending') }}</option>
                                    <option value="asc">{{ __('ascending') }}</option>
                                </select>
                                
                                <button class="btn btn-outline-secondary btn-sm" id="resetSort">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="author-grid">
                    @forelse ($authors as $author)
                        <div class="author-card" data-author-name="{{ strtolower($author->name) }}" 
                             data-author-type="{{ strtolower($author->authorType->name ?? '') }}"
                             data-records-count="{{ $author->records ? $author->records->count() : 0 }}"
                             data-lifespan="{{ $author->lifespan ?? '' }}">
                            <div class="author-card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="author-title">
                                        <h6 class="mb-1 fw-bold text-truncate">{{ $author->name }}</h6>
                                        <span class="badge bg-secondary text-white">
                                            {{ $author->authorType->name ?? '' }}
                                        </span>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('record-author.show', $author->id) }}">
                                                    <i class="bi bi-eye me-2"></i>
                                                    {{ __('view_details') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('record-author.edit', $author->id) }}">
                                                    <i class="bi bi-pencil me-2"></i>
                                                    {{ __('edit') }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="author-card-body" onclick="window.location='{{ route('record-author.show', $author->id) }}'">
                                <!-- Informations de base -->
                                <div class="author-details mb-3">
                                    @if($author->parent)
                                        <div class="info-item">
                                            <i class="bi bi-diagram-2 me-1 text-muted"></i>
                                            <small class="text-muted">{{ __('parent') }}: {{ Str::limit($author->parent->name, 20) }}</small>
                                        </div>
                                    @endif
                                    @if($author->lifespan)
                                        <div class="info-item">
                                            <i class="bi bi-calendar-event me-1 text-muted"></i>
                                            <small class="text-muted">{{ $author->lifespan }}</small>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Noms d'archive -->
                                @if($author->records && $author->records->count() > 0)
                                    <div class="archive-section mb-3">
                                        <div class="archive-header">
                                            <i class="bi bi-archive me-1 text-primary"></i>
                                            <small class="text-primary fw-medium">{{ __('archive_names') }}</small>
                                        </div>
                                        <div class="archive-badges">
                                            @foreach($author->records->take(2) as $record)
                                                <span class="badge bg-light text-dark archive-badge">
                                                    {{ Str::limit($record->name, 20) }}
                                                </span>
                                            @endforeach
                                            @if($author->records->count() > 2)
                                                <span class="badge bg-info text-white">
                                                    +{{ $author->records->count() - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="archive-section mb-3">
                                        <div class="archive-header">
                                            <i class="bi bi-archive me-1 text-muted"></i>
                                            <small class="text-muted">{{ __('no_archive_documents') }}</small>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Compteur de documents -->
                                <div class="records-count">
                                    @if($author->records && $author->records->count() > 0)
                                        <span class="badge bg-success text-white w-100">
                                            <i class="bi bi-file-text me-1"></i>
                                            {{ $author->records->count() }} {{ __('archive_documents_count') }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted w-100">
                                            <i class="bi bi-file-text me-1"></i>
                                            {{ __('no_archive_documents_count') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="author-card-footer">
                                @if($author->records && $author->records->count() > 0)
                                    <a href="{{ route('records.sort') }}?categ=author&id={{ $author->id}}"
                                       class="btn btn-outline-primary btn-sm w-100">
                                        <i class="bi bi-archive me-1"></i>
                                        {{ __('view_archives') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 w-100">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">{{ __('no_authors_found') }}</p>
                        </div>
                    @endforelse
                </div>

                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item {{ $authors->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $authors->previousPageUrl() }}" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        @for ($i = 1; $i <= $authors->lastPage(); $i++)
                            <li class="page-item {{ $authors->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ $authors->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor
                        <li class="page-item {{ $authors->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $authors->nextPageUrl() }}" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>





            </div>
        </div>
    </div>

    @push('styles')
     
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const sortBySelect = document.getElementById('sortBy');
                const sortOrderSelect = document.getElementById('sortOrder');
                const resetSortBtn = document.getElementById('resetSort');
                const authorCards = document.querySelectorAll('.author-card');
                const authorGrid = document.querySelector('.author-grid');

                // Fonction de recherche
                searchInput?.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    filterAuthors(searchTerm);
                });

                // Fonction de tri
                sortBySelect?.addEventListener('change', function() {
                    sortAuthors();
                });

                sortOrderSelect?.addEventListener('change', function() {
                    sortAuthors();
                });

                // Bouton de réinitialisation
                resetSortBtn?.addEventListener('click', function() {
                    sortBySelect.value = 'records_count';
                    sortOrderSelect.value = 'desc';
                    sortAuthors();
                });

                // Fonction de filtrage
                function filterAuthors(searchTerm) {
                    authorCards.forEach(card => {
                        const authorName = card.dataset.authorName;
                        const authorType = card.dataset.authorType;
                        const isVisible = authorName.includes(searchTerm) || authorType.includes(searchTerm);
                        card.style.display = isVisible ? '' : 'none';
                    });
                }

                // Fonction de tri
                function sortAuthors() {
                    const sortBy = sortBySelect.value;
                    const sortOrder = sortOrderSelect.value;
                    
                    if (sortBy === 'sort_by') return; // Option par défaut

                    const cardsArray = Array.from(authorCards);
                    
                    cardsArray.sort((a, b) => {
                        let aValue, bValue;
                        
                        switch(sortBy) {
                            case 'name':
                                aValue = a.dataset.authorName;
                                bValue = b.dataset.authorName;
                                break;
                            case 'type':
                                aValue = a.dataset.authorType;
                                bValue = b.dataset.authorType;
                                break;
                            case 'records_count':
                                aValue = parseInt(a.dataset.recordsCount) || 0;
                                bValue = parseInt(b.dataset.recordsCount) || 0;
                                break;
                            case 'lifespan':
                                aValue = a.dataset.lifespan || '';
                                bValue = b.dataset.lifespan || '';
                                break;
                            default:
                                return 0;
                        }
                        
                        if (sortOrder === 'asc') {
                            return aValue > bValue ? 1 : -1;
                        } else {
                            return aValue < bValue ? 1 : -1;
                        }
                    });
                    
                    // Réorganiser les cartes dans la grille
                    cardsArray.forEach(card => {
                        authorGrid.appendChild(card);
                    });
                }

                // Tri automatique au chargement de la page
                sortAuthors();
                
                // Fermeture automatique des alertes après 5 secondes
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    }, 5000);
                });
            });
        </script>
    @endpush
@endsection
