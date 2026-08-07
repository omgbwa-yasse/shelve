{{-- Selection Modal for Choosing Elements --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="{{ $icon ?? 'bi bi-check-circle' }}"></i> {{ $title }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Search Section -->
                <div class="p-3 bg-light border-bottom">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="{{ $modalId }}_search" 
                                       placeholder="{{ $searchPlaceholder ?? 'Rechercher...' }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-2">Sélectionnés:</span>
                                <span class="badge bg-success" id="{{ $modalId }}_selected_count">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selection Area -->
                <div class="selection-content" style="max-height: 50vh; overflow-y: auto;">
                    <div class="list-group list-group-flush" id="{{ $modalId }}_content">
                        @foreach($items as $key => $item)
                            <div class="list-group-item list-group-item-action selection-item" 
                                 data-search="{{ strtolower($item['search_text'] ?? '') }}"
                                 data-value="{{ $item['value'] ?? $key }}">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <input type="{{ $multiple ?? true ? 'checkbox' : 'radio' }}" 
                                               class="form-check-input selection-checkbox" 
                                               name="{{ $modalId }}_selection" 
                                               value="{{ $item['value'] ?? $key }}"
                                               id="{{ $modalId }}_item_{{ $key }}">
                                    </div>
                                    @if(isset($item['icon']))
                                        <div class="me-3">
                                            <i class="{{ $item['icon']['name'] }} {{ $item['icon']['class'] ?? 'text-primary' }}" 
                                               style="font-size: 1.2rem;"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <label class="form-check-label w-100" for="{{ $modalId }}_item_{{ $key }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $item['title'] }}</h6>
                                                    @if(isset($item['subtitle']))
                                                        <small class="text-muted d-block">{{ $item['subtitle'] }}</small>
                                                    @endif
                                                    @if(isset($item['description']))
                                                        <p class="mb-1 small text-muted">{{ Str::limit($item['description'], 120) }}</p>
                                                    @endif
                                                    @if(isset($item['meta']))
                                                        <div class="mt-1">
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
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Empty State -->
                <div id="{{ $modalId }}_empty" class="text-center py-5 d-none">
                    <i class="bi bi-search display-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun résultat</h5>
                    <p class="text-muted">Aucun élément ne correspond à votre recherche.</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <div>
                        <span class="text-muted" id="{{ $modalId }}_total_count">{{ count($items) }} élément(s) disponible(s)</span>
                    </div>
                    <div>
                        @if($multiple ?? true)
                            <button type="button" class="btn btn-outline-secondary me-2" id="{{ $modalId }}_select_all">
                                <i class="bi bi-check-all"></i> Tout sélectionner
                            </button>
                            <button type="button" class="btn btn-outline-secondary me-2" id="{{ $modalId }}_clear_all">
                                <i class="bi bi-x-circle"></i> Tout désélectionner
                            </button>
                        @endif
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> {{ __('Annuler') }}
                        </button>
                        <button type="button" class="btn btn-success" id="{{ $modalId }}_confirm" 
                                @if(isset($onConfirm)) onclick="{{ $onConfirm }}" @endif>
                            <i class="bi bi-check-lg"></i> {{ $confirmLabel ?? 'Confirmer' }}
                        </button>
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
    const contentArea = document.getElementById('{{ $modalId }}_content');
    const emptyState = document.getElementById('{{ $modalId }}_empty');
    const selectedCounter = document.getElementById('{{ $modalId }}_selected_count');
    const totalCounter = document.getElementById('{{ $modalId }}_total_count');
    const confirmBtn = document.getElementById('{{ $modalId }}_confirm');
    const selectAllBtn = document.getElementById('{{ $modalId }}_select_all');
    const clearAllBtn = document.getElementById('{{ $modalId }}_clear_all');
    
    const allItems = contentArea.querySelectorAll('.selection-item');
    const allCheckboxes = contentArea.querySelectorAll('.selection-checkbox');
    
    let searchTimeout;
    let selectedValues = new Set();

    function updateCounters() {
        const visibleItems = Array.from(allItems).filter(item => item.style.display !== 'none');
        const visibleCheckboxes = visibleItems.map(item => item.querySelector('.selection-checkbox'));
        const selectedCheckboxes = visibleCheckboxes.filter(cb => cb.checked);
        
        selectedCounter.textContent = selectedValues.size;
        totalCounter.textContent = `${visibleItems.length} élément(s) disponible(s)`;
        
        // Enable/disable confirm button
        confirmBtn.disabled = selectedValues.size === 0;
        
        // Update select all button state
        if (selectAllBtn) {
            const allVisible = visibleCheckboxes.every(cb => cb.checked);
            selectAllBtn.textContent = allVisible ? 'Tout désélectionner' : 'Tout sélectionner';
        }
    }

    function filterItems() {
        const searchQuery = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        allItems.forEach(item => {
            const searchText = item.getAttribute('data-search') || '';
            const matchesSearch = searchQuery === '' || searchText.includes(searchQuery);
            
            if (matchesSearch) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide empty state
        if (visibleCount === 0) {
            contentArea.style.display = 'none';
            emptyState.classList.remove('d-none');
        } else {
            contentArea.style.display = '';
            emptyState.classList.add('d-none');
        }

        updateCounters();
    }

    // Search functionality
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterItems, 300);
    });

    // Selection handling
    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const value = this.value;
            if (this.checked) {
                selectedValues.add(value);
            } else {
                selectedValues.delete(value);
            }
            updateCounters();
            
            // Store selected values for external access
            modal.selectedValues = Array.from(selectedValues);
        });
    });

    // Select all functionality
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const visibleCheckboxes = Array.from(allCheckboxes).filter(cb => {
                return cb.closest('.selection-item').style.display !== 'none';
            });
            
            const allSelected = visibleCheckboxes.every(cb => cb.checked);
            
            visibleCheckboxes.forEach(cb => {
                cb.checked = !allSelected;
                const value = cb.value;
                if (!allSelected) {
                    selectedValues.add(value);
                } else {
                    selectedValues.delete(value);
                }
            });
            
            modal.selectedValues = Array.from(selectedValues);
            updateCounters();
        });
    }

    // Clear all functionality
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            allCheckboxes.forEach(cb => {
                cb.checked = false;
                selectedValues.delete(cb.value);
            });
            modal.selectedValues = [];
            updateCounters();
        });
    }

    // Reset on modal show
    modal.addEventListener('show.bs.modal', function() {
        searchInput.value = '';
        allCheckboxes.forEach(cb => cb.checked = false);
        selectedValues.clear();
        modal.selectedValues = [];
        filterItems();
    });

    // Initial setup
    modal.selectedValues = [];
    updateCounters();
    
    // Public method to get selected values
    modal.getSelectedValues = function() {
        return Array.from(selectedValues);
    };
    
    // Public method to set selected values
    modal.setSelectedValues = function(values) {
        selectedValues.clear();
        allCheckboxes.forEach(cb => {
            cb.checked = values.includes(cb.value);
            if (cb.checked) {
                selectedValues.add(cb.value);
            }
        });
        modal.selectedValues = Array.from(selectedValues);
        updateCounters();
    };
});
</script>

<style>
#{{ $modalId }} .selection-item:hover {
    background-color: #f8f9fa;
}

#{{ $modalId }} .selection-item .form-check-input:checked + .me-3 + .flex-grow-1 {
    opacity: 0.8;
}

#{{ $modalId }} .modal-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

#{{ $modalId }} .selection-content {
    background: #fafafa;
}

#{{ $modalId }} .form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

#{{ $modalId }} .form-check-input:focus {
    box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
}

#{{ $modalId }} .badge {
    font-size: 0.7em;
}

#{{ $modalId }} .list-group-item {
    border-left: 4px solid transparent;
    transition: all 0.2s ease;
}

#{{ $modalId }} .list-group-item:has(.form-check-input:checked) {
    border-left-color: #28a745;
    background-color: #f8fff9;
}
</style>