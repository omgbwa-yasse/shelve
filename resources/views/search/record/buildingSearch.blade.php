@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('records.index') }}">{{ __('my_archives') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('premises') }}</li>
            </ol>
        </nav>

        <!-- Header with Search -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="bi bi-building me-2 text-primary"></i>
                {{ __('premises') }}
                <span class="badge bg-secondary ms-2">{{ $buildings->count() }}</span>
            </h1>
            
            <!-- Search and Filter Controls -->
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" 
                           class="form-control form-control-sm" 
                           id="searchBuildings" 
                           placeholder="{{ __('search') }}..."
                           style="padding-left: 35px; min-width: 250px;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown">
                        <i class="bi bi-sort-down me-1"></i> {{ __('sort') }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item sort-option" href="#" data-sort="name">{{ __('name') }}</a></li>
                        <li><a class="dropdown-item sort-option" href="#" data-sort="floors">{{ __('floors') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Buildings Grid -->
        <div class="row g-4" id="buildingList">
            @foreach ($buildings as $building)
                <div class="col-lg-6 col-xl-4 building-item" data-name="{{ strtolower($building->name ?? '') }}" data-description="{{ strtolower($building->description ?? '') }}">
                    <div class="card h-100 border-0" style="transition: all 0.3s ease; border: 1px solid #e9ecef !important;">
                        <div class="card-body p-4">
                            <!-- Building Header -->
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-2 d-flex align-items-center">
                                        <i class="bi bi-building me-2 text-primary"></i>
                                        <span class="fw-bold text-dark">{{ $building->name ?? __('N/A') }}</span>
                                    </h5>
                                    <span class="text-muted small">ID: {{ $building->id ?? __('N/A') }}</span>
                                </div>
                                @if($building->floors && $building->floors->count() > 0)
                                    <span class="badge bg-primary rounded-pill">{{ $building->floors->count() }} {{ $building->floors->count() > 1 ? __('floors') : __('floor') }}</span>
                                @endif
                            </div>

                            <!-- Building Details -->
                            <div class="mb-4">
                                @if($building->description)
                                    <p class="card-text text-muted mb-2">
                                        <i class="bi bi-file-text me-2"></i>
                                        <span>{{ Str::limit($building->description, 80) }}</span>
                                    </p>
                                @endif
                                
                                @if($building->floors && $building->floors->count() > 0)
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="bi bi-layers me-2"></i>
                                        <span>{{ $building->floors->count() }} {{ $building->floors->count() > 1 ? __('levels') : __('level') }} {{ __('available') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Action Button -->
                            <div class="d-grid">
                                <a href="{{ route('record-select-floor')}}?categ=floor&id={{ $building->id }}" 
                                   class="btn btn-primary btn-sm d-flex align-items-center justify-content-center"
                                   style="transition: all 0.2s ease;">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    {{ __('explore_floors') }}
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
    .building-item .card:hover {
        transform: translateY(-5px);
        border-color: var(--bs-primary) !important;
    }
    
    .building-item .card:hover .card-hover-overlay {
        opacity: 1;
    }
    
    .building-item .card:hover .btn-primary {
        background-color: var(--bs-success);
        border-color: var(--bs-success);
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        color: var(--bs-secondary);
    }
    
    .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15) !important;
    }
    
    .building-item {
        transition: opacity 0.3s ease;
    }
    
    .building-item.filtered-out {
        opacity: 0.3;
        pointer-events: none;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchBuildings');
        const buildingItems = document.querySelectorAll('.building-item');
        const emptyState = document.getElementById('emptyState');
        const sortOptions = document.querySelectorAll('.sort-option');
        
        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            buildingItems.forEach(function(item) {
                const name = item.dataset.name || '';
                const description = item.dataset.description || '';
                const searchContent = name + ' ' + description;
                
                if (searchContent.includes(searchTerm)) {
                    item.classList.remove('d-none', 'filtered-out');
                    visibleCount++;
                } else {
                    item.classList.add('filtered-out');
                }
            });
            
            // Show/hide empty state
            if (visibleCount === 0 && searchTerm !== '') {
                emptyState.classList.remove('d-none');
            } else {
                emptyState.classList.add('d-none');
            }
        });
        
        // Sort functionality
        sortOptions.forEach(function(option) {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const sortBy = this.dataset.sort;
                sortBuildings(sortBy);
                
                // Update active sort option
                sortOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        function sortBuildings(sortBy) {
            const container = document.getElementById('buildingList');
            const items = Array.from(container.querySelectorAll('.building-item'));
            
            items.sort(function(a, b) {
                let aValue, bValue;
                
                if (sortBy === 'name') {
                    aValue = a.dataset.name;
                    bValue = b.dataset.name;
                } else if (sortBy === 'floors') {
                    aValue = parseInt(a.querySelector('.badge')?.textContent || '0');
                    bValue = parseInt(b.querySelector('.badge')?.textContent || '0');
                    return bValue - aValue; // Descending for floors
                }
                
                return aValue.localeCompare(bValue);
            });
            
            // Re-append items in sorted order
            items.forEach(item => container.appendChild(item));
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
