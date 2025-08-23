@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 text-dark mb-1">{{ __('authors_management') }}</h1>
                    <p class="text-muted mb-0">{{ __('authors_list_description') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('mail-author.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('add_author') }}
                    </a>
                </div>
            </div>

            <!-- Filters and Sort Section -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label for="search" class="form-label small fw-medium text-muted mb-1">
                                <i class="fas fa-search me-1"></i>{{ __('search_authors') }}
                            </label>
                            <input type="text" id="search" class="form-control form-control-sm" 
                                   placeholder="{{ __('search_authors') }}" onkeyup="filterAuthors()">
                        </div>
                        <div class="col-md-2">
                            <label for="typeFilter" class="form-label small fw-medium text-muted mb-1">
                                <i class="fas fa-filter me-1"></i>{{ __('filter_by_type') }}
                            </label>
                            <select id="typeFilter" class="form-select form-select-sm" onchange="filterAuthors()">
                                <option value="">{{ __('all_types') }}</option>
                                @foreach($authorTypes ?? [] as $type)
                                    <option value="{{ $type->name }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sortBy" class="form-label small fw-medium text-muted mb-1">
                                <i class="fas fa-sort me-1"></i>{{ __('sort_by') }}
                            </label>
                            <select id="sortBy" class="form-select form-select-sm" onchange="filterAuthors()">
                                <option value="name">{{ __('name') }}</option>
                                <option value="type">{{ __('type') }}</option>
                                <option value="records_count">{{ __('associated_documents') }}</option>
                                <option value="lifespan">{{ __('lifespan') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sortOrder" class="form-label small fw-medium text-muted mb-1">
                                <i class="fas fa-sort-amount-down me-1"></i>{{ __('sort_order') }}
                            </label>
                            <select id="sortOrder" class="form-select form-select-sm" onchange="filterAuthors()">
                                <option value="asc">{{ __('ascending') }}</option>
                                <option value="desc">{{ __('descending') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="perPage" class="form-label small fw-medium text-muted mb-1">
                                <i class="fas fa-list me-1"></i>{{ __('results_per_page') }}
                            </label>
                            <select id="perPage" class="form-select form-select-sm" onchange="filterAuthors()">
                                <option value="12">12</option>
                                <option value="24">24</option>
                                <option value="48">48</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                <i class="fas fa-times me-1"></i>{{ __('clear_filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0 text-dark">
                                <i class="fas fa-users me-2 text-primary"></i>
                                {{ __('authors_list') }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-primary rounded-pill" id="authorsCount">{{ $authors->count() }} {{ __('authors_count') }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if($authors->isEmpty())
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-user-plus fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">{{ __('no_authors_found') }}</h5>
                            <p class="text-muted mb-3">{{ __('start_adding_authors') }}</p>
                            <a href="{{ route('mail-author.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>{{ __('add_first_author') }}
                            </a>
                        </div>
                    @else
                        <!-- Authors Grid - 2 per row -->
                        <div class="row" id="authorsGrid">
                            @foreach($authors as $author)
                            <div class="col-md-6 mb-4 author-card" 
                                 data-name="{{ strtolower($author->name) }}"
                                 data-type="{{ strtolower($author->authorType->name ?? '') }}"
                                 data-records-count="{{ $author->records ? $author->records->count() : 0 }}"
                                 data-lifespan="{{ strtolower($author->lifespan ?? '') }}">
                                <div class="card h-100 border-0 shadow-sm hover-lift">
                                    <div class="card-header bg-light border-0 py-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="author-type-icon me-3">
                                                        <i class="fas fa-user-tag text-info"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium text-dark">{{ $author->authorType->name ?? __('not_defined') }}</div>
                                                        <small class="text-muted">{{ __('entity_type_label') }}</small>
                                                    </div>
                                                </div>
                                                <h6 class="mb-1 text-dark fw-bold">{{ $author->name }}</h6>
                                                @if($author->parallel_name)
                                                    <small class="text-muted">{{ $author->parallel_name }}</small>
                                                @endif
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('mail-author.show', $author) }}">
                                                        <i class="fas fa-eye me-2"></i>{{ __('view') }}
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="{{ route('mail-author.edit', $author) }}">
                                                        <i class="fas fa-edit me-2"></i>{{ __('edit') }}
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" 
                                                           onclick="confirmDelete('{{ $author->id }}', '{{ $author->name }}')">
                                                        <i class="fas fa-trash me-2"></i>{{ __('delete') }}
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <!-- Archive Names Section -->
                                        <div class="mb-3">
                                            <h6 class="small fw-medium text-muted mb-2">
                                                <i class="fas fa-archive me-1"></i>{{ __('archive_name') }}
                                            </h6>
                                            @if($author->records && $author->records->count() > 0)
                                                <div class="archive-names">
                                                    @foreach($author->records->take(3) as $record)
                                                        <span class="badge bg-light text-dark me-1 mb-1">
                                                            <i class="fas fa-archive me-1"></i>
                                                            {{ Str::limit($record->name, 25) }}
                                                        </span>
                                                    @endforeach
                                                    @if($author->records->count() > 3)
                                                        <span class="badge bg-secondary">
                                                            +{{ $author->records->count() - 3 }} {{ __('other_archives') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted small">
                                                    <i class="fas fa-archive me-1"></i>
                                                    {{ __('no_archive_documents') }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Documents Count -->
                                        <div class="mb-3">
                                            <h6 class="small fw-medium text-muted mb-2">
                                                <i class="fas fa-file-alt me-1"></i>{{ __('associated_documents') }}
                                            </h6>
                                            @if($author->records && $author->records->count() > 0)
                                                <span class="badge bg-success rounded-pill">
                                                    {{ $author->records->count() }} {{ __('documents_count') }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted rounded-pill">
                                                    {{ __('no_documents') }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Additional Information -->
                                        <div class="author-info">
                                            @if($author->lifespan)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ $author->lifespan }}
                                                    </small>
                                                </div>
                                            @endif
                                            @if($author->locations)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        {{ Str::limit($author->locations, 30) }}
                                                    </small>
                                                </div>
                                            @endif
                                            @if($author->parent)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-sitemap me-1"></i>
                                                        {{ __('parent') }}: {{ Str::limit($author->parent->name, 25) }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- View Archives Button -->
                                        @if($author->records && $author->records->count() > 0)
                                            <div class="mt-3">
                                                <a href="{{ route('records.sort') }}?categ=author&id={{ $author->id}}"
                                                   class="btn btn-outline-primary btn-sm w-100">
                                                    <i class="fas fa-archive me-1"></i>
                                                    {{ __('view_archives') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer Actions -->
            @if(!$authors->isEmpty())
            <div class="mt-3 text-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ __('click_actions_manage') }}
                </small>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="card-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer l'auteur <strong id="authorName"></strong> ?</p>
                <p class="text-danger small">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@endsection

@section('scripts')
<script>
// Global variables for filtering and sorting
let allAuthors = [];
let filteredAuthors = [];
let currentPage = 1;
let authorsPerPage = 12;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize authors data
    initializeAuthors();
    
    // Set initial per page value
    document.getElementById('perPage').value = authorsPerPage;
});

function initializeAuthors() {
    const authorCards = document.querySelectorAll('.author-card');
    allAuthors = Array.from(authorCards).map(card => ({
        element: card,
        name: card.dataset.name,
        type: card.dataset.type,
        recordsCount: parseInt(card.dataset.recordsCount),
        lifespan: card.dataset.lifespan
    }));
    filteredAuthors = [...allAuthors];
    updateAuthorsCount();
}

function filterAuthors() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value.toLowerCase();
    const sortBy = document.getElementById('sortBy').value;
    const sortOrder = document.getElementById('sortOrder').value;
    const perPage = parseInt(document.getElementById('perPage').value);
    
    // Filter authors
    filteredAuthors = allAuthors.filter(author => {
        const matchesSearch = author.name.includes(searchTerm) || 
                            author.lifespan.includes(searchTerm);
        const matchesType = !typeFilter || author.type.includes(typeFilter);
        
        return matchesSearch && matchesType;
    });
    
    // Sort authors
    filteredAuthors.sort((a, b) => {
        let aValue, bValue;
        
        switch(sortBy) {
            case 'name':
                aValue = a.name;
                bValue = b.name;
                break;
            case 'type':
                aValue = a.type;
                bValue = b.type;
                break;
            case 'records_count':
                aValue = a.recordsCount;
                bValue = b.recordsCount;
                break;
            case 'lifespan':
                aValue = a.lifespan;
                bValue = b.lifespan;
                break;
            default:
                aValue = a.name;
                bValue = b.name;
        }
        
        if (sortOrder === 'desc') {
            [aValue, bValue] = [bValue, aValue];
        }
        
        if (typeof aValue === 'string') {
            return aValue.localeCompare(bValue);
        } else {
            return aValue - bValue;
        }
    });
    
    // Update display
    authorsPerPage = perPage;
    currentPage = 1;
    displayAuthors();
    updateAuthorsCount();
}

function displayAuthors() {
    const startIndex = (currentPage - 1) * authorsPerPage;
    const endIndex = startIndex + authorsPerPage;
    const authorsToShow = filteredAuthors.slice(startIndex, endIndex);
    
    // Hide all authors
    allAuthors.forEach(author => {
        author.element.style.display = 'none';
    });
    
    // Show filtered authors
    authorsToShow.forEach(author => {
        author.element.style.display = 'block';
    });
    
    // Update pagination if needed
    updatePagination();
}

function updatePagination() {
    const totalPages = Math.ceil(filteredAuthors.length / authorsPerPage);
    
    // Simple pagination display
    if (totalPages > 1) {
        const paginationContainer = document.getElementById('paginationContainer');
        if (!paginationContainer) {
            const container = document.createElement('div');
            container.id = 'paginationContainer';
            container.className = 'd-flex justify-content-center mt-4';
            document.getElementById('authorsGrid').after(container);
        }
        
        const pagination = document.getElementById('paginationContainer');
        pagination.innerHTML = `
            <nav aria-label="Authors pagination">
                <ul class="pagination pagination-sm">
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Précédent</a>
                    </li>
                    ${generatePageNumbers(totalPages)}
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Suivant</a>
                    </li>
                </ul>
            </nav>
        `;
    } else {
        const paginationContainer = document.getElementById('paginationContainer');
        if (paginationContainer) {
            paginationContainer.remove();
        }
    }
}

function generatePageNumbers(totalPages) {
    let pageNumbers = '';
    const maxVisiblePages = 5;
    
    if (totalPages <= maxVisiblePages) {
        for (let i = 1; i <= totalPages; i++) {
            pageNumbers += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>
            `;
        }
    } else {
        // Show first page, current page, and last page
        pageNumbers += `
            <li class="page-item ${1 === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(1)">1</a>
            </li>
        `;
        
        if (currentPage > 3) {
            pageNumbers += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        
        for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
            pageNumbers += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>
            `;
        }
        
        if (currentPage < totalPages - 2) {
            pageNumbers += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        
        pageNumbers += `
            <li class="page-item ${totalPages === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a>
            </li>
        `;
    }
    
    return pageNumbers;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredAuthors.length / authorsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        displayAuthors();
    }
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('sortBy').value = 'name';
    document.getElementById('sortOrder').value = 'asc';
    document.getElementById('perPage').value = '12';
    
    filterAuthors();
}

function updateAuthorsCount() {
    const countElement = document.getElementById('authorsCount');
    if (countElement) {
        countElement.textContent = `${filteredAuthors.length} {{ __('authors_count') }}`;
    }
}

function confirmDelete(authorId, authorName) {
    document.getElementById('authorName').textContent = authorName;
    document.getElementById('deleteForm').action = `{{ route('mail-author.destroy', ':id') }}`.replace(':id', authorId);
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endsection

@push('styles')
<style>
.card {
    border-radius: 0.75rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.author-type-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.archive-names .badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    margin-bottom: 0.25rem;
}

.badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15);
}

/* Filter section styles */
.form-control-sm, .form-select-sm {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.form-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6c757d;
}

/* Pagination styles */
.pagination .page-link {
    border-radius: 0.375rem;
    margin: 0 0.125rem;
    border: 1px solid #dee2e6;
    color: #495057;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .filters-row .col-md-2,
    .filters-row .col-md-3 {
        margin-bottom: 1rem;
    }
}

/* Animation for cards */
.author-card {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading state */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Empty state styling */
.text-center.py-5 {
    padding: 3rem 0;
}

.text-center.py-5 i {
    color: #dee2e6;
    margin-bottom: 1rem;
}
</style>
@endpush
