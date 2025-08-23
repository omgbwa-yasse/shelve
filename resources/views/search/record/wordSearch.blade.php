@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('records.index') }}">{{ __('my_archives') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('keywords') }}</li>
            </ol>
        </nav>

        <!-- Header with Search -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="bi bi-tags me-2 text-primary"></i>
                {{ __('keywords') }}
                <span class="badge bg-secondary ms-2">{{ $terms->total() }}</span>
                <small class="text-muted ms-2">{{ __('with_archives') }}</small>
            </h1>
            
            <!-- Search Control -->
            <div class="position-relative" style="min-width: 300px;">
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="keywordSearch" 
                       placeholder="{{ __('search_keywords_placeholder') }}..."
                       style="padding-left: 35px;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
            </div>
        </div>

        <!-- Compact List View -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div id="termsList">
                    @if($terms->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($terms as $term)
                                <div class="list-group-item border-0 py-3 term-item" 
                                     data-label="{{ strtolower($term->preferred_label ?? '') }}"
                                     data-notation="{{ strtolower($term->notation ?? '') }}"
                                     data-uri="{{ strtolower($term->uri ?? '') }}"
                                     style="transition: all 0.2s ease;">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-tag text-primary me-3"></i>
                                                <div>
                                                    <h6 class="mb-1 fw-bold">{{ $term->preferred_label ?? $term->uri }}</h6>
                                                    @if($term->notation)
                                                        <small class="text-muted">
                                                            <i class="bi bi-hash"></i> {{ $term->notation }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3 text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="bi bi-files text-success me-2"></i>
                                                <div>
                                                    <div class="fw-bold text-success">{{ number_format($term->records_count) }}</div>
                                                    <small class="text-muted">{{ __('records') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3 text-end">
                                            <a href="{{ route('records.sort')}}?categ=term&id={{ $term->id }}" 
                                               class="btn btn-outline-primary btn-sm"
                                               style="transition: all 0.2s ease;">
                                                <i class="bi bi-search me-1"></i>
                                                {{ __('search_records') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="px-4 py-3 border-top bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ __('showing') }} {{ $terms->firstItem() }}-{{ $terms->lastItem() }} {{ __('of') }} {{ $terms->total() }} {{ __('keywords') }}
                                </small>
                                {{ $terms->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-tags display-4 text-muted"></i>
                            <h5 class="mt-3 text-muted">{{ __('no_keywords_with_archives') }}</h5>
                            <p class="text-muted">{{ __('no_keywords_available_with_records') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Search Empty State -->
                <div class="text-center py-5 d-none" id="searchEmptyState">
                    <i class="bi bi-search display-4 text-muted"></i>
                    <h5 class="mt-3 text-muted">{{ __('no_results_found') }}</h5>
                    <p class="text-muted">{{ __('try_different_search_terms') }}</p>
                    <button class="btn btn-outline-primary" onclick="clearSearch()">{{ __('clear_search') }}</button>
                </div>
            </div>
        </div>
    </div>

    <style>
    .term-item:hover {
        background-color: #f8f9fa !important;
        transform: translateX(5px);
    }
    
    .term-item:hover .btn-outline-primary {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: white;
    }
    
    .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15) !important;
    }
    
    .term-item.filtered-out {
        display: none;
    }
    
    .list-group-item:not(:last-child) {
        border-bottom: 1px solid #f0f0f0 !important;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('keywordSearch');
        const termItems = document.querySelectorAll('.term-item');
        const searchEmptyState = document.getElementById('searchEmptyState');
        const termsList = document.getElementById('termsList');
        
        // Search functionality with debouncing
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterTerms();
            }, 200);
        });
        
        function filterTerms() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;
            
            termItems.forEach(function(item) {
                const label = item.dataset.label || '';
                const notation = item.dataset.notation || '';
                const uri = item.dataset.uri || '';
                const searchContent = label + ' ' + notation + ' ' + uri;
                
                if (searchContent.includes(searchTerm)) {
                    item.classList.remove('filtered-out');
                    visibleCount++;
                } else {
                    item.classList.add('filtered-out');
                }
            });
            
            // Show/hide empty state
            if (visibleCount === 0 && searchTerm !== '') {
                termsList.classList.add('d-none');
                searchEmptyState.classList.remove('d-none');
            } else {
                termsList.classList.remove('d-none');
                searchEmptyState.classList.add('d-none');
            }
        }
        
        // Focus effect for search input
        searchInput.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
    
    function clearSearch() {
        document.getElementById('keywordSearch').value = '';
        document.querySelectorAll('.term-item').forEach(item => item.classList.remove('filtered-out'));
        document.getElementById('termsList').classList.remove('d-none');
        document.getElementById('searchEmptyState').classList.add('d-none');
    }
    </script>
@endsection
