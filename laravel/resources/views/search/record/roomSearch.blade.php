@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('records.index') }}">{{ __('my_archives') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('record-select-building') }}">{{ __('premises') }}</a></li>
                <li class="breadcrumb-item"><a href="javascript:history.back()">{{ __('floors') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('rooms') }}</li>
            </ol>
        </nav>

        <!-- Header with Search -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="bi bi-door-open me-2 text-primary"></i>
                {{ __('rooms') }}
                <span class="badge bg-secondary ms-2">{{ $rooms->count() }}</span>
            </h1>
            
            <!-- Search Controls -->
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" 
                           class="form-control form-control-sm" 
                           id="searchRooms" 
                           placeholder="{{ __('search') }}..."
                           style="padding-left: 35px; min-width: 250px;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                </div>
            </div>
        </div>

        <!-- Rooms Grid -->
        <div class="row g-4" id="roomList">
            @foreach ($rooms as $room)
                <div class="col-lg-6 col-xl-4 room-item" data-code="{{ strtolower($room->code ?? '') }}" data-name="{{ strtolower($room->name ?? '') }}" data-description="{{ strtolower($room->description ?? '') }}" data-floor="{{ strtolower($room->floor->name ?? '') }}" data-building="{{ strtolower($room->floor->building->name ?? '') }}">
                    <div class="card h-100 border-0" style="transition: all 0.3s ease; border: 1px solid #e9ecef !important;">
                        <div class="card-body p-4">
                            <!-- Room Header -->
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-2 d-flex align-items-center">
                                        <i class="bi bi-door-open me-2 text-primary"></i>
                                        <span class="fw-bold text-dark">{{ $room->code ?? __('N/A') }}</span>
                                    </h5>
                                    <div class="text-muted small mb-1">{{ $room->name ?? __('N/A') }}</div>
                                    <span class="text-muted small">ID: {{ $room->id ?? __('N/A') }}</span>
                                </div>
                            </div>

                            <!-- Room Details -->
                            <div class="mb-4">
                                @if($room->description)
                                    <p class="card-text text-muted mb-3">
                                        <i class="bi bi-file-text me-2"></i>
                                        <span>{{ Str::limit($room->description, 80) }}</span>
                                    </p>
                                @endif
                                
                                <div class="d-flex align-items-center text-muted small mb-1">
                                    <i class="bi bi-layers me-2"></i>
                                    <span><strong>{{ __('floor') }}:</strong> {{ $room->floor->name ?? __('N/A') }}</span>
                                </div>
                                
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="bi bi-building me-2"></i>
                                    <span><strong>{{ __('building') }}:</strong> {{ $room->floor->building->name ?? __('N/A') }}</span>
                                </div>

                                <!-- Statistics -->
                                <div class="row g-2 mt-2 pt-2 border-top">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            @php $containersCount = $room->containers_count ?? 0; @endphp
                                            <i class="bi bi-archive {{ $containersCount > 0 ? 'text-primary' : 'text-muted' }} me-2"></i>
                                            <div>
                                                <div class="fw-bold {{ $containersCount > 0 ? 'text-primary' : 'text-muted' }}">{{ number_format($containersCount) }}</div>
                                                <div class="small text-muted">{{ __('containers') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            @php $recordsCount = $room->records_count ?? 0; @endphp
                                            <i class="bi bi-files {{ $recordsCount > 0 ? 'text-success' : 'text-muted' }} me-2"></i>
                                            <div>
                                                <div class="fw-bold {{ $recordsCount > 0 ? 'text-success' : 'text-muted' }}">{{ number_format($recordsCount) }}</div>
                                                <div class="small text-muted">{{ __('records') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="d-grid">
                                <a href="{{route('record-select-shelve') }}?categ=shelve&id={{ $room->id }}" 
                                   class="btn btn-primary btn-sm d-flex align-items-center justify-content-center"
                                   style="transition: all 0.2s ease;">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    {{ __('explore_shelves') }}
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
    .room-item .card:hover {
        transform: translateY(-5px);
        border-color: var(--bs-primary) !important;
    }
    
    .room-item .card:hover .card-hover-overlay {
        opacity: 1;
    }
    
    .room-item .card:hover .btn-primary {
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
    
    .room-item {
        transition: opacity 0.3s ease;
    }
    
    .room-item.filtered-out {
        opacity: 0.3;
        pointer-events: none;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchRooms');
        const roomItems = document.querySelectorAll('.room-item');
        const emptyState = document.getElementById('emptyState');
        
        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            roomItems.forEach(function(item) {
                const code = item.dataset.code || '';
                const name = item.dataset.name || '';
                const description = item.dataset.description || '';
                const floor = item.dataset.floor || '';
                const building = item.dataset.building || '';
                const searchContent = code + ' ' + name + ' ' + description + ' ' + floor + ' ' + building;
                
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
