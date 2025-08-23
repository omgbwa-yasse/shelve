@extends('layouts.app')

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
                    <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                        <input type="text" class="form-control border-start-0"
                               id="searchInput"
                               placeholder="{{ __('search_authors') }}">
                    </div>
                </div>

                <div class="author-list">
                    @forelse ($authors as $author)
                        <div class="author-item border-bottom" data-author-name="{{ strtolower($author->name) }}">
                            <div class="d-flex align-items-center p-3">
                                <div class="author-info flex-grow-1" style="cursor: pointer;"
                                     onclick="window.location='{{ route('record-author.show', $author->id) }}'">
                                    <div class="d-flex align-items-center">
                                        <span class="fw-medium">{{ $author->name }}</span>
                                        <span class="badge bg-secondary ms-2 text-white">
                                        {{ $author->authorType->name ?? '' }}
                                    </span>
                                    </div>
                                    
                                    <!-- Informations de base -->
                                    <div class="author-details mt-2">
                                        @if($author->parent)
                                            <small class="text-muted d-block mb-1">
                                                <i class="bi bi-diagram-2 me-1"></i>
                                                {{ __('parent') }}: {{ $author->parent->name }}
                                            </small>
                                        @endif
                                        @if($author->lifespan)
                                            <small class="text-muted d-block mb-1">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                {{ $author->lifespan }}
                                            </small>
                                        @endif
                                        
                                        <!-- NOUVEAU: Noms d'archive -->
                                        @if($author->records && $author->records->count() > 0)
                                            <div class="archive-names mt-2">
                                                <small class="text-muted d-block mb-1">
                                                    <i class="bi bi-archive me-1"></i>
                                                    <strong>{{ __('archive_names') }} :</strong>
                                                </small>
                                                <div class="archive-badges">
                                                    @foreach($author->records->take(3) as $record)
                                                        <span class="badge bg-light text-dark me-1 mb-1 archive-badge">
                                                            {{ Str::limit($record->name, 25) }}
                                                        </span>
                                                    @endforeach
                                                    @if($author->records->count() > 3)
                                                        <span class="badge bg-info text-white">
                                                            +{{ $author->records->count() - 3 }} autres
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <small class="text-muted d-block mt-2">
                                                <i class="bi bi-archive me-1"></i>
                                                {{ __('no_archive_documents') }}
                                            </small>
                                        @endif
                                        
                                        <!-- Compteur de documents -->
                                        <div class="records-count mt-2">
                                            @if($author->records && $author->records->count() > 0)
                                                <span class="badge bg-success text-white">
                                                    <i class="bi bi-file-text me-1"></i>
                                                    {{ $author->records->count() }} {{ __('archive_documents_count') }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted">
                                                    <i class="bi bi-file-text me-1"></i>
                                                    {{ __('no_archive_documents_count') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="ms-auto d-flex gap-2 align-items-center">
                                    @if($author->records && $author->records->count() > 0)
                                        <a href="{{ route('records.sort') }}?categ=author&id={{ $author->id}}"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-archive me-1"></i>
                                            {{ __('view_archives') }}
                                        </a>
                                    @endif
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
                        </div>
                    @empty
                        <div class="text-center py-5">
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
        <style>
            .author-item:hover {
                background-color: #f8f9fa;
                transition: background-color 0.2s ease;
            }
            .author-info:hover {
                color: #0d6efd;
            }
            .badge {
                font-weight: 500;
                font-size: 0.75rem;
            }
            .author-list {
                max-height: calc(100vh - 250px);
                overflow-y: auto;
            }
            .dropdown-item:hover {
                background-color: #f8f9fa;
            }
            .alert {
                border: none;
                border-radius: 0;
            }
            .btn-light:hover {
                background-color: #f8f9fa;
            }
            
            /* Nouveaux styles pour les archives */
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
            
            .records-count .badge {
                font-size: 0.75rem;
                padding: 0.375rem 0.75rem;
            }
            
            .author-details {
                line-height: 1.4;
            }
            
            .author-details small {
                font-size: 0.8rem;
            }
            
            /* Amélioration de l'espacement */
            .author-item {
                transition: all 0.2s ease;
            }
            
            .author-item:hover {
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            /* Responsive design */
            @media (max-width: 768px) {
                .archive-badges {
                    flex-direction: column;
                }
                
                .archive-badge {
                    margin-bottom: 0.25rem;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const authorItems = document.querySelectorAll('.author-item');

                searchInput?.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();

                    authorItems.forEach(item => {
                        const authorName = item.dataset.authorName;
                        item.style.display = authorName.includes(searchTerm) ? '' : 'none';
                    });
                });

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
