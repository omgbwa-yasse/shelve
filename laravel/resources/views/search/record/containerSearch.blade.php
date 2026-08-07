@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('records.index') }}">{{ __('my_archives') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('record-select-building') }}">{{ __('premises') }}</a></li>
                <li class="breadcrumb-item"><a href="javascript:history.back()">{{ __('shelves') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('containers') }}</li>
            </ol>
        </nav>

        <!-- Header with Search -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="bi bi-archive me-2 text-primary"></i>
                {{ __('archive_containers') }}
                <span class="badge bg-secondary ms-2">{{ $containers->count() }}</span>
            </h1>
            
            <!-- Search Controls -->
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" 
                           class="form-control form-control-sm" 
                           id="searchContainers" 
                           placeholder="{{ __('search') }}..."
                           style="padding-left: 35px; min-width: 250px;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="statusFilter" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel me-1"></i> {{ __('filter') }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item status-filter" href="#" data-status="all">{{ __('all_statuses') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        @php
                            $statuses = $containers->pluck('status.name')->filter()->unique();
                        @endphp
                        @foreach($statuses as $status)
                            <li><a class="dropdown-item status-filter" href="#" data-status="{{ strtolower($status) }}">{{ $status }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Containers Grid -->
        <div class="row g-4" id="containerList">
            @foreach ($containers as $container)
                <div class="col-lg-6 col-xl-4 container-item" data-code="{{ strtolower($container->code ?? '') }}" data-shelf="{{ strtolower($container->shelf->code ?? '') }}" data-status="{{ strtolower($container->status->name ?? '') }}" data-property="{{ strtolower($container->property->name ?? '') }}">
                    <div class="card h-100 border-0" style="transition: all 0.3s ease; border: 1px solid #e9ecef !important;">
                        <div class="card-body p-4">
                            <!-- Container Header -->
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-2 d-flex align-items-center">
                                        <i class="bi bi-archive me-2 text-primary"></i>
                                        <span class="fw-bold text-dark">{{ $container->code ?? __('N/A') }}</span>
                                    </h5>
                                    <div class="text-muted small mb-1">{{ __('shelf') }}: {{ $container->shelf->code ?? __('N/A') }}</div>
                                </div>
                                @if($container->status)
                                    @php
                                        $statusColor = match(strtolower($container->status->name ?? '')) {
                                            'active' => 'bg-success',
                                            'inactive' => 'bg-secondary', 
                                            'pending' => 'bg-warning',
                                            'archived' => 'bg-info',
                                            default => 'bg-primary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusColor }} rounded-pill">{{ $container->status->name }}</span>
                                @endif
                            </div>

                            <!-- Container Details -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="bi bi-flag me-2"></i>
                                    <span><strong>{{ __('status') }}:</strong> {{ $container->status->name ?? __('N/A') }}</span>
                                </div>
                                
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="bi bi-building me-2"></i>
                                    <span><strong>{{ __('property') }}:</strong> {{ $container->property->name ?? __('N/A') }}</span>
                                </div>
                                
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="bi bi-bookshelf me-2"></i>
                                    <span><strong>{{ __('shelf') }}:</strong> {{ $container->shelf->code ?? __('N/A') }}</span>
                                </div>

                                <!-- Statistics -->
                                <div class="mt-2 pt-2 border-top">
                                    <div class="d-flex align-items-center">
                                        @php $recordsCount = $container->records_count ?? 0; @endphp
                                        <i class="bi bi-files {{ $recordsCount > 0 ? 'text-success' : 'text-muted' }} me-2"></i>
                                        <div>
                                            <div class="fw-bold {{ $recordsCount > 0 ? 'text-success' : 'text-muted' }}">{{ number_format($recordsCount) }}</div>
                                            <div class="small text-muted">{{ __('records_stored') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="d-grid">
                                <a href="{{ route('records.sort') }}?categ=container&id={{ $container->id }}" 
                                   class="btn btn-success btn-sm d-flex align-items-center justify-content-center"
                                   style="transition: all 0.2s ease;">
                                    <i class="bi bi-files me-2"></i>
                                    {{ __('view_records') }}
                                </a>
                            </div>
                        </div>
                        
                        <!-- Hover Effect -->
                        <div class="card-hover-overlay position-absolute top-0 start-0 w-100 h-100" 
                             style="background: linear-gradient(45deg, rgba(13, 110, 253, 0.05), rgba(25, 135, 84, 0.05)); opacity: 0; transition: opacity 0.3s ease; pointer-events: none; border-radius: 0.375rem;"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Empty State -->
        <div class="text-center py-5 d-none" id="emptyState">
            <i class="bi bi-search display-1 text-muted"></i>
            <h4 class="mt-3 text-muted">{{ __('no_results_found') }}</h4>
            <p class="text-muted">{{ __('try_different_search_terms') }}</p>
        </div>
    </div>

    <style>
    .container-item .card:hover {
        transform: translateY(-5px);
        border-color: var(--bs-primary) !important;
    }
    
    .container-item .card:hover .card-hover-overlay {
        opacity: 1;
    }
    
    .container-item .card:hover .btn-success {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        color: var(--bs-secondary);
    }
    
    .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15) !important;
    }
    
    .container-item {
        transition: opacity 0.3s ease;
    }
    
    .container-item.filtered-out {
        opacity: 0.3;
        pointer-events: none;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchContainers');
        const containerItems = document.querySelectorAll('.container-item');
        const emptyState = document.getElementById('emptyState');
        const statusFilters = document.querySelectorAll('.status-filter');
        let currentStatusFilter = 'all';
        
        // Search functionality
        searchInput.addEventListener('input', function() {
            filterContainers();
        });
        
        // Status filter functionality
        statusFilters.forEach(function(filter) {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                currentStatusFilter = this.dataset.status;
                filterContainers();
                
                // Update active filter
                statusFilters.forEach(f => f.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        function filterContainers() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;
            
            containerItems.forEach(function(item) {
                const code = item.dataset.code || '';
                const shelf = item.dataset.shelf || '';
                const status = item.dataset.status || '';
                const property = item.dataset.property || '';
                const searchContent = code + ' ' + shelf + ' ' + status + ' ' + property;
                
                const matchesSearch = searchContent.includes(searchTerm);
                const matchesStatus = currentStatusFilter === 'all' || status === currentStatusFilter;
                
                if (matchesSearch && matchesStatus) {
                    item.classList.remove('d-none', 'filtered-out');
                    visibleCount++;
                } else {
                    item.classList.add('filtered-out');
                }
            });
            
            // Show/hide empty state
            if (visibleCount === 0) {
                emptyState.classList.remove('d-none');
            } else {
                emptyState.classList.add('d-none');
            }
        }
        
        // Add smooth focus effect to search input
        searchInput.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
    </script>
@endsection
