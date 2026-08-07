{{-- Modal Template for Element Lists --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="{{ $icon ?? 'bi bi-list-ul' }}"></i> {{ $title }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Search and Filter Section -->
                <div class="p-3 bg-light border-bottom">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="{{ $modalId }}_search" 
                                       placeholder="{{ $searchPlaceholder ?? 'Rechercher...' }}" autocomplete="off">
                            </div>
                        </div>
                        @if(isset($filters) && count($filters) > 0)
                            <div class="col-md-4">
                                <select class="form-select" id="{{ $modalId }}_filter">
                                    <option value="">{{ __('Tous les types') }}</option>
                                    @foreach($filters as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    @if(isset($quickActions) && count($quickActions) > 0)
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @foreach($quickActions as $action)
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="{{ $action['onclick'] ?? '' }}">
                                    <i class="{{ $action['icon'] }}"></i> {{ $action['label'] }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Content Area -->
                <div class="list-content" style="max-height: 60vh; overflow-y: auto;">
                    @if(isset($useCards) && $useCards)
                        <!-- Card Layout -->
                        <div class="row g-3 p-3" id="{{ $modalId }}_content">
                            @foreach($items as $item)
                                <div class="col-lg-4 col-md-6 list-item" 
                                     data-search="{{ strtolower($item['search_text'] ?? '') }}"
                                     data-filter="{{ $item['filter_value'] ?? '' }}">
                                    <div class="card h-100 shadow-sm">
                                        @if(isset($item['image']))
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                                 style="height: 120px;">
                                                @if(is_string($item['image']))
                                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" 
                                                         class="img-fluid" style="max-height: 100px;">
                                                @else
                                                    <i class="{{ $item['image']['icon'] }} display-4 {{ $item['image']['class'] ?? 'text-muted' }}"></i>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $item['title'] }}</h6>
                                            @if(isset($item['subtitle']))
                                                <small class="text-muted d-block mb-2">{{ $item['subtitle'] }}</small>
                                            @endif
                                            @if(isset($item['description']))
                                                <p class="card-text small">{{ Str::limit($item['description'], 100) }}</p>
                                            @endif
                                            @if(isset($item['badges']))
                                                <div class="mb-2">
                                                    @foreach($item['badges'] as $badge)
                                                        <span class="badge bg-{{ $badge['type'] ?? 'secondary' }} me-1">
                                                            @if(isset($badge['icon']))
                                                                <i class="{{ $badge['icon'] }}"></i>
                                                            @endif
                                                            {{ $badge['text'] }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @if(isset($item['actions']))
                                            <div class="card-footer bg-transparent">
                                                <div class="btn-group w-100" role="group">
                                                    @foreach($item['actions'] as $action)
                                                        <a href="{{ $action['url'] ?? '#' }}" 
                                                           class="btn btn-sm btn-{{ $action['type'] ?? 'outline-primary' }}"
                                                           @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                                           @if(isset($action['target'])) target="{{ $action['target'] }}" @endif>
                                                            <i class="{{ $action['icon'] }}"></i>
                                                            @if(isset($action['label']))
                                                                {{ $action['label'] }}
                                                            @endif
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- List Layout -->
                        <div class="list-group list-group-flush" id="{{ $modalId }}_content">
                            @foreach($items as $item)
                                <div class="list-group-item list-group-item-action list-item" 
                                     data-search="{{ strtolower($item['search_text'] ?? '') }}"
                                     data-filter="{{ $item['filter_value'] ?? '' }}">
                                    <div class="d-flex align-items-center">
                                        @if(isset($item['icon']))
                                            <div class="me-3">
                                                <i class="{{ $item['icon']['name'] }} {{ $item['icon']['class'] ?? 'text-primary' }}" 
                                                   style="font-size: 1.5rem;"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $item['title'] }}</h6>
                                                    @if(isset($item['subtitle']))
                                                        <small class="text-muted">{{ $item['subtitle'] }}</small>
                                                    @endif
                                                    @if(isset($item['description']))
                                                        <p class="mb-1 small">{{ $item['description'] }}</p>
                                                    @endif
                                                    @if(isset($item['meta']))
                                                        <div class="mt-2">
                                                            @foreach($item['meta'] as $meta)
                                                                <small class="text-muted me-3">
                                                                    <i class="{{ $meta['icon'] }}"></i> {{ $meta['text'] }}
                                                                </small>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ms-3">
                                                    @if(isset($item['badges']))
                                                        @foreach($item['badges'] as $badge)
                                                            <span class="badge bg-{{ $badge['type'] ?? 'secondary' }} me-1">
                                                                @if(isset($badge['icon']))
                                                                    <i class="{{ $badge['icon'] }}"></i>
                                                                @endif
                                                                {{ $badge['text'] }}
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                    @if(isset($item['actions']))
                                                        <div class="btn-group ms-2" role="group">
                                                            @foreach($item['actions'] as $action)
                                                                <a href="{{ $action['url'] ?? '#' }}" 
                                                                   class="btn btn-sm btn-{{ $action['type'] ?? 'outline-primary' }}"
                                                                   @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                                                   @if(isset($action['target'])) target="{{ $action['target'] }}" @endif
                                                                   title="{{ $action['title'] ?? '' }}">
                                                                    <i class="{{ $action['icon'] }}"></i>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Empty State -->
                <div id="{{ $modalId }}_empty" class="text-center py-5 d-none">
                    <i class="{{ $emptyIcon ?? 'bi bi-inbox' }} display-1 text-muted mb-3"></i>
                    <h5 class="text-muted">{{ $emptyTitle ?? 'Aucun élément trouvé' }}</h5>
                    <p class="text-muted">{{ $emptyMessage ?? 'Aucun élément ne correspond à vos critères de recherche.' }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <div>
                        <span class="text-muted" id="{{ $modalId }}_count">{{ count($items) }} élément(s)</span>
                    </div>
                    <div>
                        @if(isset($footerActions))
                            @foreach($footerActions as $action)
                                <button type="button" class="btn btn-{{ $action['type'] ?? 'secondary' }}" 
                                        @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                        @if(isset($action['dismiss'])) data-bs-dismiss="modal" @endif>
                                    <i class="{{ $action['icon'] }}"></i> {{ $action['label'] }}
                                </button>
                            @endforeach
                        @else
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> {{ __('Fermer') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('{{ $modalId }}');
    const searchInput = document.getElementById('{{ $modalId }}_search');
    const filterSelect = document.getElementById('{{ $modalId }}_filter');
    const contentArea = document.getElementById('{{ $modalId }}_content');
    const emptyState = document.getElementById('{{ $modalId }}_empty');
    const counter = document.getElementById('{{ $modalId }}_count');
    const allItems = contentArea.querySelectorAll('.list-item');

    let searchTimeout;

    function filterItems() {
        const searchQuery = searchInput.value.toLowerCase().trim();
        const filterValue = filterSelect ? filterSelect.value : '';
        let visibleCount = 0;

        allItems.forEach(item => {
            const searchText = item.getAttribute('data-search') || '';
            const filterText = item.getAttribute('data-filter') || '';
            
            const matchesSearch = searchQuery === '' || searchText.includes(searchQuery);
            const matchesFilter = filterValue === '' || filterText === filterValue;
            
            if (matchesSearch && matchesFilter) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Update counter
        counter.textContent = `${visibleCount} élément(s)`;

        // Show/hide empty state
        if (visibleCount === 0) {
            contentArea.style.display = 'none';
            emptyState.classList.remove('d-none');
        } else {
            contentArea.style.display = '';
            emptyState.classList.add('d-none');
        }
    }

    // Search functionality with debounce
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterItems, 300);
    });

    // Filter functionality
    if (filterSelect) {
        filterSelect.addEventListener('change', filterItems);
    }

    // Reset on modal show
    modal.addEventListener('show.bs.modal', function() {
        searchInput.value = '';
        if (filterSelect) filterSelect.value = '';
        filterItems();
    });
});
</script>

<style>
#{{ $modalId }} .list-content {
    background: #fafafa;
}

#{{ $modalId }} .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    transition: all 0.3s ease;
}

#{{ $modalId }} .list-group-item:hover {
    background-color: #f8f9fa;
}

#{{ $modalId }} .modal-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

#{{ $modalId }} .btn-group .btn {
    border-radius: 0;
}

#{{ $modalId }} .btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

#{{ $modalId }} .btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}
</style>