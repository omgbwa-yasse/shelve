@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header avec titre et bouton d'action -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-primary">
                <i class="bi bi-gear-wide-connected me-2"></i>{{ __('Parameters') }}
            </h1>
            <p class="text-muted mb-0">{{ __('Parameter List') }}</p>
        </div>
        <a href="{{ route('settings.definitions.create') }}" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-circle me-2"></i>{{ __('New Parameter') }}
        </a>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $settings->count() }}</h4>
                            <small>{{ __('Total Parameters') }}</small>
                        </div>
                        <i class="bi bi-gear fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $settings->where('is_system', false)->count() }}</h4>
                            <small>{{ __('User Parameters') }}</small>
                        </div>
                        <i class="bi bi-person-gear fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $settings->where('is_system', true)->count() }}</h4>
                            <small>{{ __('System Parameters') }}</small>
                        </div>
                        <i class="bi bi-cpu fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $settings->whereNotNull('category_id')->count() }}</h4>
                            <small>{{ __('Categorized') }}</small>
                        </div>
                        <i class="bi bi-folder fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="{{ __('Search parameters...') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="categoryFilter">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($settings->pluck('category.name')->unique()->filter() as $categoryName)
                            <option value="{{ $categoryName }}">{{ $categoryName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="typeFilter">
                        <option value="">{{ __('All Types') }}</option>
                        @foreach($settings->pluck('type')->unique() as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                        <i class="bi bi-x-circle me-1"></i>{{ __('Clear') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des paramètres -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>{{ __('Parameter List') }}
            </h5>
        </div>
        <div class="card-body p-0">
            @if($settings->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-gear-wide fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('No Parameters Found') }}</h5>
                    <p class="text-muted mb-4">{{ __('Get started by creating your first parameter') }}</p>
                    <a href="{{ route('settings.definitions.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>{{ __('Create First Parameter') }}
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="parametersTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">
                                    <i class="bi bi-tag me-1"></i>{{ __('Name') }}
                                </th>
                                <th class="border-0">
                                    <i class="bi bi-folder me-1"></i>{{ __('Category') }}
                                </th>
                                <th class="border-0">
                                    <i class="bi bi-info-circle me-1"></i>{{ __('Description') }}
                                </th>
                                <th class="border-0">
                                    <i class="bi bi-code-slash me-1"></i>{{ __('Type') }}
                                </th>
                                <th class="border-0">
                                    <i class="bi bi-shield me-1"></i>{{ __('Status') }}
                                </th>
                                <th class="border-0 text-end">
                                    <i class="bi bi-gear me-1"></i>{{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $setting)
                                <tr class="parameter-row" 
                                    data-name="{{ strtolower($setting->name) }}"
                                    data-category="{{ strtolower($setting->category->name ?? '') }}"
                                    data-type="{{ strtolower($setting->type) }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="parameter-icon me-3">
                                                <i class="bi bi-gear-fill text-primary"></i>
                                            </div>
                                            <div>
                                                <strong class="d-block">{{ $setting->name }}</strong>
                                                <small class="text-muted">ID: {{ $setting->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($setting->category)
                                            <a href="{{ route('settings.categories.show', $setting->category) }}" 
                                               class="badge bg-secondary text-decoration-none">
                                                <i class="bi bi-folder me-1"></i>{{ $setting->category->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">
                                                <i class="bi bi-folder-x me-1"></i>{{ __('No Category') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                              title="{{ $setting->description }}">
                                            {{ Str::limit($setting->description, 80) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <i class="bi bi-code me-1"></i>{{ $setting->type }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($setting->is_system)
                                            <span class="badge bg-warning">
                                                <i class="bi bi-cpu me-1"></i>{{ __('System') }}
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="bi bi-person me-1"></i>{{ __('User Parameter') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('settings.definitions.show', $setting) }}"
                                               class="btn btn-sm btn-outline-info" 
                                               title="{{ __('View') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('settings.definitions.edit', $setting) }}"
                                               class="btn btn-sm btn-outline-warning" 
                                               title="{{ __('Edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('settings.definitions.destroy', $setting) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="{{ __('Delete') }}"
                                                        onclick="return confirm('{{ __('Are you sure you want to delete this parameter?') }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.parameter-icon {
    width: 40px;
    height: 40px;
    background: rgba(13, 110, 253, 0.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.parameter-row:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 12px 12px 0 0 !important;
}

.table th {
    font-weight: 600;
    color: #6c757d;
    border-bottom: 2px solid #dee2e6;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}

.btn-group .btn {
    border-radius: 6px;
    margin: 0 2px;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-lg {
        width: 100%;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const typeFilter = document.getElementById('typeFilter');
    const parameterRows = document.querySelectorAll('.parameter-row');

    function filterParameters() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryValue = categoryFilter.value.toLowerCase();
        const typeValue = typeFilter.value.toLowerCase();

        parameterRows.forEach(row => {
            const name = row.dataset.name;
            const category = row.dataset.category;
            const type = row.dataset.type;

            const matchesSearch = name.includes(searchTerm);
            const matchesCategory = !categoryValue || category.includes(categoryValue);
            const matchesType = !typeValue || type.includes(typeValue);

            if (matchesSearch && matchesCategory && matchesType) {
                row.style.display = '';
                row.style.animation = 'fadeIn 0.3s ease-in';
            } else {
                row.style.display = 'none';
            }
        });

        updateEmptyState();
    }

    function updateEmptyState() {
        const visibleRows = document.querySelectorAll('.parameter-row:not([style*="display: none"])');
        const emptyState = document.querySelector('.text-center.py-5');
        
        if (visibleRows.length === 0 && emptyState) {
            emptyState.style.display = 'block';
            emptyState.innerHTML = `
                <i class="bi bi-search fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('No parameters match your search') }}</h5>
                <p class="text-muted mb-4">{{ __('Try adjusting your filters') }}</p>
                <button class="btn btn-outline-primary" onclick="clearFilters()">
                    <i class="bi bi-x-circle me-2"></i>{{ __('Clear Filters') }}
                </button>
            `;
        } else if (emptyState) {
            emptyState.style.display = 'none';
        }
    }

    function clearFilters() {
        searchInput.value = '';
        categoryFilter.value = '';
        typeFilter.value = '';
        filterParameters();
    }

    // Event listeners
    searchInput.addEventListener('input', filterParameters);
    categoryFilter.addEventListener('change', filterParameters);
    typeFilter.addEventListener('change', filterParameters);

    // Animation pour les lignes
    parameterRows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.05}s`;
    });
});

// Animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .parameter-row {
        animation: fadeIn 0.3s ease-out;
    }
`;
document.head.appendChild(style);
</script>
@endsection
