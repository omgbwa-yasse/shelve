@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('records.index') }}">{{ __('my_archives') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('activities') }}</li>
            </ol>
        </nav>

        <!-- Header with Search -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="bi bi-briefcase me-2 text-primary"></i>
                {{ __('activities') }}
                <span class="badge bg-secondary ms-2">{{ $activities->count() }}</span>
            </h1>
            
            <!-- Search and Filter Controls -->
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" 
                           class="form-control form-control-sm" 
                           id="searchActivities" 
                           placeholder="{{ __('search') }}..."
                           style="padding-left: 35px; min-width: 250px;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="typeFilter" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel me-1"></i> {{ __('filter') }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item type-filter" href="#" data-type="all">{{ __('all_activities') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item type-filter" href="#" data-type="mission">{{ __('mission_level') }}</a></li>
                        <li><a class="dropdown-item type-filter" href="#" data-type="activity">{{ __('activity_level') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Modern Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="activitiesTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-code-square me-2 text-primary"></i>
                                        {{ __('code') }}
                                    </div>
                                </th>
                                <th class="px-4 py-3 border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-tag me-2 text-primary"></i>
                                        {{ __('name') }}
                                    </div>
                                </th>
                                <th class="px-4 py-3 border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-text me-2 text-primary"></i>
                                        {{ __('observation') }}
                                    </div>
                                </th>
                                <th class="px-4 py-3 border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-diagram-3 me-2 text-primary"></i>
                                        {{ __('hierarchy') }}
                                    </div>
                                </th>
                                <th class="px-4 py-3 border-0 text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="bi bi-files me-2 text-success"></i>
                                        {{ __('records') }}
                                    </div>
                                </th>
                                <th class="px-4 py-3 border-0 text-center">{{ __('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activities as $activity)
                                <tr class="activity-row" 
                                    data-code="{{ strtolower($activity->code ?? '') }}" 
                                    data-name="{{ strtolower($activity->name ?? '') }}" 
                                    data-observation="{{ strtolower($activity->observation ?? '') }}"
                                    data-type="{{ $activity->parent ? 'activity' : 'mission' }}"
                                    style="transition: all 0.2s ease;">
                                    <td class="px-4 py-3 align-middle">
                                        <div class="fw-bold text-primary">{{ $activity->code }}</div>
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        <div class="fw-medium">{{ $activity->name }}</div>
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        @if($activity->observation)
                                            <div class="text-muted small">{{ Str::limit($activity->observation, 60) }}</div>
                                        @else
                                            <span class="text-muted fst-italic">{{ __('no_observation') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        @if($activity->parent)
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-info me-2">{{ __('activity') }}</span>
                                                <div class="small text-muted">
                                                    <div>{{ $activity->parent->code }}</div>
                                                    <div>{{ Str::limit($activity->parent->name, 25) }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary me-2">{{ __('mission') }}</span>
                                                @if($activity->children_count > 0)
                                                    <small class="text-muted">{{ $activity->children_count }} {{ __('sub_activities') }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-middle text-center">
                                        @php $recordsCount = $activity->records_count ?? 0; @endphp
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="bi bi-files {{ $recordsCount > 0 ? 'text-success' : 'text-muted' }} me-2"></i>
                                            <span class="fw-bold {{ $recordsCount > 0 ? 'text-success' : 'text-muted' }}">
                                                {{ number_format($recordsCount) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 align-middle text-center">
                                        <a href="{{ route('records.sort')}}?categ=activity&id={{ $activity->id }}" 
                                           class="btn btn-outline-success btn-sm" 
                                           style="transition: all 0.2s ease;">
                                            <i class="bi bi-eye me-1"></i>
                                            {{ __('view_records') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="bi bi-briefcase display-4 text-muted"></i>
                                            <h5 class="mt-3 text-muted">{{ __('no_activities_found') }}</h5>
                                            <p class="text-muted">{{ __('no_activities_available') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div class="text-center py-5 d-none" id="emptyState">
            <i class="bi bi-search display-1 text-muted"></i>
            <h4 class="mt-3 text-muted">{{ __('no_results_found') }}</h4>
            <p class="text-muted">{{ __('try_different_search_terms') }}</p>
        </div>
    </div>

    <style>
    .activity-row:hover {
        background-color: #f8f9fa !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .activity-row:hover .btn-outline-success {
        background-color: var(--bs-success);
        border-color: var(--bs-success);
        color: white;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        color: var(--bs-secondary);
    }
    
    .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15) !important;
    }
    
    .activity-row.filtered-out {
        display: none;
    }
    
    .table thead th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchActivities');
        const activityRows = document.querySelectorAll('.activity-row');
        const emptyState = document.getElementById('emptyState');
        const typeFilters = document.querySelectorAll('.type-filter');
        const tableBody = document.querySelector('#activitiesTable tbody');
        let currentTypeFilter = 'all';
        
        // Search functionality
        searchInput.addEventListener('input', function() {
            filterActivities();
        });
        
        // Type filter functionality
        typeFilters.forEach(function(filter) {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                currentTypeFilter = this.dataset.type;
                filterActivities();
                
                // Update active filter
                typeFilters.forEach(f => f.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        function filterActivities() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;
            
            activityRows.forEach(function(row) {
                const code = row.dataset.code || '';
                const name = row.dataset.name || '';
                const observation = row.dataset.observation || '';
                const type = row.dataset.type || '';
                const searchContent = code + ' ' + name + ' ' + observation;
                
                const matchesSearch = searchContent.includes(searchTerm);
                const matchesType = currentTypeFilter === 'all' || type === currentTypeFilter;
                
                if (matchesSearch && matchesType) {
                    row.classList.remove('filtered-out');
                    visibleCount++;
                } else {
                    row.classList.add('filtered-out');
                }
            });
            
            // Show/hide empty state and table
            if (visibleCount === 0) {
                document.querySelector('.card').style.display = 'none';
                emptyState.classList.remove('d-none');
            } else {
                document.querySelector('.card').style.display = 'block';
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
