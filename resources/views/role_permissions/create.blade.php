@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('create_permission') }}</h4>
                    <a href="{{ route('role_permissions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> {{ __('Back') }}
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('role_permissions.store') }}" method="POST">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role_id" class="form-label fw-bold">
                                        <i class="bi bi-person-badge"></i> {{ __('roles') }}
                                    </label>
                                    <select class="form-select" id="role_id" name="role_id" required>
                                        <option value="">{{ __('Select') }} {{ __('roles') }}</option>
                                        @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-end h-100">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="selectAll">
                                            <i class="bi bi-check-all"></i> {{ __('Select All') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAll">
                                            <i class="bi bi-x-circle"></i> {{ __('Deselect All') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="permissions-container">
                            <h5 class="mb-3">
                                <i class="bi bi-key"></i> {{ __('assign_permissions') }}
                                <span class="badge bg-secondary ms-2">{{ $permissionsByCategory->sum(function($permissions) { return $permissions->count(); }) }} total</span>
                            </h5>

                            @if($permissionsByCategory->count() > 0)
                                <div class="permissions-tabs-container">
                                    <!-- Navigation tabs -->
                                    <ul class="nav nav-tabs mb-3" id="permissionsTabs" role="tablist">
                                        @foreach($permissionsByCategory as $category => $permissions)
                                            @if($category)
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                            id="tab-{{ $category }}"
                                                            data-bs-toggle="tab"
                                                            data-bs-target="#tabpane-{{ $category }}"
                                                            type="button"
                                                            role="tab"
                                                            aria-controls="tabpane-{{ $category }}"
                                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                                        <i class="bi bi-folder me-1"></i>
                                                        {{ $categoryLabels[$category] ?? ucfirst($category) }}
                                                        <span class="badge bg-primary ms-1">{{ $permissions->count() }}</span>
                                                    </button>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>

                                    <!-- Tab content -->
                                    <div class="tab-content permissions-scroll-area" id="permissionsTabContent">
                                        @foreach($permissionsByCategory as $category => $permissions)
                                            @if($category)
                                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                                     id="tabpane-{{ $category }}"
                                                     role="tabpanel"
                                                     aria-labelledby="tab-{{ $category }}">

                                                    <!-- Category actions -->
                                                    <div class="category-header mb-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0">
                                                                <i class="bi bi-collection me-1"></i>
                                                                {{ $categoryLabels[$category] ?? ucfirst($category) }}
                                                                <small class="text-muted">({{ $permissions->count() }} permissions)</small>
                                                            </h6>
                                                            <div class="btn-group btn-group-sm">
                                                                <button type="button" class="btn btn-outline-success category-select-all" data-category="{{ $category }}">
                                                                    <i class="bi bi-check-all"></i> {{ __('Select All') }}
                                                                </button>
                                                                <button type="button" class="btn btn-outline-warning category-deselect-all" data-category="{{ $category }}">
                                                                    <i class="bi bi-x-circle"></i> {{ __('Deselect All') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Permissions grid -->
                                                    <div class="permissions-grid">
                                                        <div class="row g-2">
                                                            @foreach ($permissions as $permission)
                                                                <div class="col-md-6 col-lg-4">
                                                                    <div class="permission-card">
                                                                        <div class="form-check">
                                                                            <input type="checkbox"
                                                                                   class="form-check-input permission-checkbox"
                                                                                   id="permission_{{ $permission->id }}"
                                                                                   name="permissions[]"
                                                                                   value="{{ $permission->id }}"
                                                                                   data-category="{{ $category }}">
                                                                            <label class="form-check-label w-100" for="permission_{{ $permission->id }}">
                                                                                <div class="permission-name">
                                                                                    {{ str_replace($category.'.', '', $permission->name) }}
                                                                                </div>
                                                                                @if($permission->description)
                                                                                    <div class="permission-description">
                                                                                        {{ $permission->description }}
                                                                                    </div>
                                                                                @endif
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    {{ __('No permissions found. Please run the permission seeders.') }}
                                </div>
                            @endif
                        </div>

                        <!-- Form action buttons integrated in the form -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="bi bi-save"></i> {{ __('Save') }}
                                            </button>
                                            <a href="{{ route('role_permissions.index') }}" class="btn btn-secondary btn-lg ms-3">
                                                <i class="bi bi-x-circle"></i> {{ __('Cancel') }}
                                            </a>
                                        </div>
                                        <div>
                                            <span class="badge bg-info fs-6">
                                                <i class="bi bi-check-circle"></i>
                                                <span id="selectedCount">0</span> <span id="selectedText">{{ __('permissions selected') }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Tabs styling */
.nav-tabs {
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 1rem;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    padding: 0.75rem 1rem;
    margin-right: 0.25rem;
    border-radius: 0.5rem 0.5rem 0 0;
    transition: all 0.3s ease;
    position: relative;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    background-color: #f8f9fa;
    color: #0d6efd;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
    border-width: 2px 2px 0 2px;
    border-style: solid;
    font-weight: 600;
}

.nav-tabs .nav-link .badge {
    font-size: 0.7em;
    padding: 0.25rem 0.5rem;
}

/* Permissions container */
.permissions-container {
    position: relative;
    margin-bottom: 2rem;
}

.permissions-tabs-container {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.permissions-scroll-area {
    max-height: 65vh;
    overflow-y: auto;
    padding: 1rem;
}

/* Form actions styling */
.form-actions {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-top: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-actions .btn-lg {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}

.form-actions .badge {
    font-size: 1rem;
    padding: 0.75rem 1rem;
}

/* Category header */
.category-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.25rem;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
    margin-bottom: 1.5rem;
}

.category-header h6 {
    font-size: 1.1rem;
    font-weight: 600;
}

.category-header .btn-group-sm .btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
}

/* Permission cards */
.permission-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 1rem;
    transition: all 0.2s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
    min-height: 80px;
}

.permission-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.15);
    transform: translateY(-1px);
}

.permission-card .form-check {
    margin: 0;
    padding: 0;
    border: none;
    background: none;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.permission-card .form-check-input {
    margin-top: 0.125rem;
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
    border: 2px solid #dee2e6;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

.permission-card .form-check-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.permission-card .form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.permission-card .form-check-label {
    flex: 1;
    cursor: pointer;
    user-select: none;
    line-height: 1.4;
}

.permission-card .form-check-input:checked + .form-check-label {
    color: #0d6efd;
}

.permission-card .form-check-input:checked + .form-check-label .permission-name {
    font-weight: 600;
    color: #0d6efd;
}

.permission-name {
    font-weight: 500;
    color: #212529;
    margin-bottom: 0.5rem;
    line-height: 1.3;
    font-size: 0.95rem;
}

.permission-description {
    font-size: 0.85em;
    color: #6c757d;
    line-height: 1.4;
    margin-top: 0.25rem;
}

/* Permissions grid */
.permissions-grid {
    margin-top: 1.5rem;
}

.permissions-grid .row {
    margin: 0 -0.5rem;
}

.permissions-grid .row > [class*="col-"] {
    padding: 0 0.5rem;
    margin-bottom: 1rem;
}

/* Main content spacing */
.container-fluid {
    margin-bottom: 2rem;
}

/* Category action buttons */
.category-header .btn-group .btn {
    font-size: 0.85em;
    padding: 0.5rem 0.75rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .nav-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: thin;
    }

    .nav-tabs .nav-link {
        white-space: nowrap;
        min-width: auto;
        margin-right: 0.1rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.9em;
    }

    .permission-card {
        margin-bottom: 1rem;
        padding: 1.25rem;
        min-height: 85px;
    }

    .permission-card .form-check {
        gap: 1rem;
    }

    .permission-card .form-check-input {
        width: 1.35rem;
        height: 1.35rem;
    }

    .permission-name {
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }

    .category-header {
        padding: 0.75rem;
    }

    .category-header .d-flex {
        flex-direction: column;
        gap: 0.75rem;
    }

    .category-header h6 {
        margin-bottom: 0;
    }

    .category-header .btn-group {
        justify-content: stretch;
    }

    .category-header .btn-group .btn {
        flex: 1;
        font-size: 0.8em;
        padding: 0.4rem 0.5rem;
    }

    .form-actions .d-flex {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .form-actions .btn-lg {
        width: 100%;
        margin: 0 0 0.5rem 0 !important;
    }

    .permissions-scroll-area {
        max-height: 55vh;
        padding: 0.75rem;
    }

    .form-actions {
        padding: 1rem;
    }

    .form-actions .badge {
        font-size: 0.9rem;
    }
}

/* Tab content animations */
.tab-pane {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Scrollbar styling */
.permissions-scroll-area::-webkit-scrollbar,
.nav-tabs::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.permissions-scroll-area::-webkit-scrollbar-track,
.nav-tabs::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.permissions-scroll-area::-webkit-scrollbar-thumb,
.nav-tabs::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.permissions-scroll-area::-webkit-scrollbar-thumb:hover,
.nav-tabs::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Permission counter badge */
.badge.fs-6 {
    font-weight: 500;
    padding: 0.5rem 1rem;
}

/* Enhanced visual feedback */
.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:active {
    transform: scale(0.98);
}

/* Form actions hover effects */
.form-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Enhanced checkbox visibility */
.permission-card:hover .form-check-input {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.125rem rgba(13, 110, 253, 0.15);
}

.permission-card .form-check-input:hover {
    border-color: #0d6efd;
    transform: scale(1.05);
}

.permission-card .form-check-label:hover .permission-name {
    color: #0d6efd;
    transition: color 0.2s ease;
}

/* Visual feedback for selected state */
.permission-card.selected {
    border-color: #28a745 !important;
    background-color: #f8fff9 !important;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
}

.permission-card.selected .form-check-input {
    border-color: #28a745;
    background-color: #28a745;
}
</style>

<script>
// Translations for JavaScript
const translations = {
    'permissionsFor': '{{ app()->getLocale() === "fr" ? "Permissions pour" : "Permissions for" }}',
    'loaded': '{{ app()->getLocale() === "fr" ? "chargées" : "loaded" }}',
    'connectionError': '{{ app()->getLocale() === "fr" ? "Erreur de connexion lors du chargement des permissions" : "Connection error while loading permissions" }}',
    'selectAllCategorySelected': '{{ app()->getLocale() === "fr" ? "Sélectionnées !" : "Selected!" }}',
    'deselectAllCategoryDeselected': '{{ app()->getLocale() === "fr" ? "Désélectionnées !" : "Deselected!" }}',
    'allSelected': '{{ app()->getLocale() === "fr" ? "Toutes sélectionnées !" : "All selected!" }}',
    'allDeselected': '{{ app()->getLocale() === "fr" ? "Toutes désélectionnées !" : "All deselected!" }}'
};

document.addEventListener('DOMContentLoaded', function() {
    const selectAllBtn = document.getElementById('selectAll');
    const deselectAllBtn = document.getElementById('deselectAll');
    const selectedCountSpan = document.getElementById('selectedCount');
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    const roleSelect = document.getElementById('role_id');

    // AJAX function to load role permissions
    function loadRolePermissions(roleId) {
        if (!roleId) {
            // Clear selections if no role selected
            checkboxes.forEach(checkbox => checkbox.checked = false);
            updateSelectedCount();
            return;
        }

        // Show loading state
        const loadingHtml = '<i class="bi bi-hourglass-split me-1"></i> {{ __("Loading permissions...") }}';
        const originalBadgeContent = document.querySelector('.permissions-container h5 .badge').innerHTML;
        document.querySelector('.permissions-container h5 .badge').innerHTML = loadingHtml;

        // Disable form elements during loading
        roleSelect.disabled = true;
        checkboxes.forEach(checkbox => checkbox.disabled = true);

        fetch(`{{ url('settings/role_permissions') }}/${roleId}/permissions`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear all checkboxes first
                checkboxes.forEach(checkbox => checkbox.checked = false);

                // Check the permissions assigned to this role
                data.permissions.forEach(permissionId => {
                    const checkbox = document.querySelector(`input[value="${permissionId}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        // Add visual feedback
                        const card = checkbox.closest('.permission-card');
                        if (card) {
                            card.classList.add('selected');
                        }
                    }
                });

                updateSelectedCount();

                // Show success message briefly
                const successBadge = document.querySelector('.permissions-container h5 .badge');
                const successMessage = `<i class="bi bi-check-circle me-1"></i> ${translations.permissionsFor} "${data.role_name}" ${translations.loaded}`;
                successBadge.innerHTML = successMessage;
                successBadge.classList.remove('bg-secondary');
                successBadge.classList.add('bg-success');

                setTimeout(() => {
                    successBadge.innerHTML = originalBadgeContent;
                    successBadge.classList.remove('bg-success');
                    successBadge.classList.add('bg-secondary');
                }, 2000);
            } else {
                console.error('{{ __("Error loading permissions") }}:', data.message);
                alert('{{ __("Error loading permissions") }}: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            alert(translations.connectionError);
        })
        .finally(() => {
            // Re-enable form elements
            roleSelect.disabled = false;
            checkboxes.forEach(checkbox => checkbox.disabled = false);
        });
    }

    // Listen for role selection changes
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            const roleId = this.value;
            if (roleId) {
                loadRolePermissions(roleId);
            } else {
                // Clear selections if no role selected
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    const card = checkbox.closest('.permission-card');
                    if (card) {
                        card.classList.remove('selected');
                    }
                });
                updateSelectedCount();
            }
        });
    }

    // Update selected count with enhanced visual feedback
    function updateSelectedCount() {
        const selected = document.querySelectorAll('.permission-checkbox:checked').length;
        const total = checkboxes.length;
        const selectedTextSpan = document.getElementById('selectedText');

        selectedCountSpan.textContent = selected;

        // Update text based on count (singular/plural)
        if (selectedTextSpan) {
            if (selected === 1) {
                selectedTextSpan.textContent = '{{ __("permission selected") }}';
            } else {
                selectedTextSpan.textContent = '{{ __("permissions selected") }}';
            }
        }

        // Update badge color based on selection
        const badge = selectedCountSpan.closest('.badge');
        if (badge) {
            badge.classList.remove('bg-info', 'bg-success', 'bg-warning');
            if (selected === 0) {
                badge.classList.add('bg-info');
            } else if (selected === total) {
                badge.classList.add('bg-success');
            } else {
                badge.classList.add('bg-warning');
            }
        }

        // Update tab badges to show selected count per category
        updateTabBadges();
    }

    // Update individual tab badges with selected count
    function updateTabBadges() {
        document.querySelectorAll('.nav-link').forEach(tabLink => {
            const category = tabLink.id.replace('tab-', '');
            const categoryCheckboxes = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]`);
            const categorySelected = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]:checked`).length;
            const categoryTotal = categoryCheckboxes.length;

            const badge = tabLink.querySelector('.badge');
            if (badge) {
                badge.textContent = `${categorySelected}/${categoryTotal}`;
                badge.classList.remove('bg-primary', 'bg-success', 'bg-warning', 'bg-secondary');

                if (categorySelected === 0) {
                    badge.classList.add('bg-secondary');
                } else if (categorySelected === categoryTotal) {
                    badge.classList.add('bg-success');
                } else {
                    badge.classList.add('bg-warning');
                }
            }
        });
    }

    // Select all permissions globally
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                // Add visual feedback to card
                const card = checkbox.closest('.permission-card');
                if (card) {
                    card.style.transform = 'scale(1.02)';
                    card.style.borderColor = '#28a745';
                    card.style.backgroundColor = '#f8fff9';
                    setTimeout(() => card.style.transform = '', 200);
                }
            });
            updateSelectedCount();

            // Visual feedback for button
            this.classList.add('btn-success');
            this.innerHTML = '<i class="bi bi-check-all"></i> ' + translations.allSelected;
            setTimeout(() => {
                this.classList.remove('btn-success');
                this.innerHTML = '<i class="bi bi-check-all"></i> {{ __("Select All") }}';
            }, 1500);
        });
    }

    // Deselect all permissions globally
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                // Add visual feedback to card
                const card = checkbox.closest('.permission-card');
                if (card) {
                    card.style.transform = 'scale(0.98)';
                    card.style.borderColor = '#e9ecef';
                    card.style.backgroundColor = '#fff';
                    setTimeout(() => card.style.transform = '', 200);
                }
            });
            updateSelectedCount();

            // Visual feedback for button
            this.classList.add('btn-warning');
            this.innerHTML = '<i class="bi bi-x-circle"></i> ' + translations.allDeselected;
            setTimeout(() => {
                this.classList.remove('btn-warning');
                this.innerHTML = '<i class="bi bi-x-circle"></i> {{ __("Deselect All") }}';
            }, 1500);
        });
    }

    // Category select all buttons
    document.querySelectorAll('.category-select-all').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            const categoryCheckboxes = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]`);

            categoryCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
                // Add visual effect
                const card = checkbox.closest('.permission-card');
                if (card) {
                    card.style.backgroundColor = '#d4edda';
                    card.style.borderColor = '#28a745';
                    setTimeout(() => {
                        card.style.backgroundColor = '#f8fff9';
                    }, 500);
                }
            });

            updateSelectedCount();

            // Visual feedback
            this.classList.add('btn-success');
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check-all"></i> ' + translations.selectAllCategorySelected;
            setTimeout(() => {
                this.classList.remove('btn-success');
                this.innerHTML = originalText;
            }, 1000);
        });
    });

    // Category deselect all buttons
    document.querySelectorAll('.category-deselect-all').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            const categoryCheckboxes = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]`);

            categoryCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                // Add visual effect
                const card = checkbox.closest('.permission-card');
                if (card) {
                    card.style.backgroundColor = '#f8d7da';
                    card.style.borderColor = '#dc3545';
                    setTimeout(() => {
                        card.style.backgroundColor = '#fff';
                        card.style.borderColor = '#e9ecef';
                    }, 500);
                }
            });

            updateSelectedCount();

            // Visual feedback
            this.classList.add('btn-warning');
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-x-circle"></i> ' + translations.deselectAllCategoryDeselected;
            setTimeout(() => {
                this.classList.remove('btn-warning');
                this.innerHTML = originalText;
            }, 1000);
        });
    });

    // Individual checkbox change events
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();

            // Visual feedback for individual selection
            const card = this.closest('.permission-card');
            if (card) {
                if (this.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            }
        });
    });

    // Form submission validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const selected = document.querySelectorAll('.permission-checkbox:checked').length;
            const selectedRole = roleSelect.value;

            if (!selectedRole) {
                e.preventDefault();
                alert('{{ __("Please select a role before continuing.") }}');
                roleSelect.focus();
                return;
            }

            if (selected === 0) {
                e.preventDefault();
                if (confirm('{{ __("No permissions are selected. Do you want to continue?") }}')) {
                    this.submit();
                }
            } else {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> {{ __("Saving...") }}';
                    submitBtn.disabled = true;
                }
            }
        });
    }

    // Tab switching enhancement
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tabButton => {
        tabButton.addEventListener('shown.bs.tab', function(e) {
            // Update URL hash without page jump
            const targetId = this.getAttribute('data-bs-target').substring(1);
            window.history.replaceState(null, null, '#' + targetId);

            // Focus management for accessibility
            const targetPanel = document.querySelector(this.getAttribute('data-bs-target'));
            if (targetPanel) {
                targetPanel.focus();
            }
        });
    });

    // Initialize on page load
    updateSelectedCount();

    // Handle URL hash for direct tab access
    if (window.location.hash) {
        const targetTab = document.querySelector(`[data-bs-target="${window.location.hash}"]`);
        if (targetTab) {
            const tab = new bootstrap.Tab(targetTab);
            tab.show();
        }
    }

    // Auto-save draft selections to localStorage
    let autoSaveTimeout;
    function autoSaveDraft() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            const selectedRole = roleSelect.value;
            const selectedIds = Array.from(document.querySelectorAll('.permission-checkbox:checked')).map(cb => cb.value);
            if (selectedRole) {
                localStorage.setItem(`role_permissions_draft_${selectedRole}`, JSON.stringify(selectedIds));
            }
        }, 1000);
    }

    // Listen for changes to auto-save
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', autoSaveDraft);
    });

    // Load draft on role change
    roleSelect.addEventListener('change', function() {
        const roleId = this.value;
        if (roleId) {
            const draft = localStorage.getItem(`role_permissions_draft_${roleId}`);
            if (draft && !confirm('{{ __("This role has saved permissions. Do you want to load the official role permissions? (Cancel = load draft)") }}')) {
                try {
                    const selectedIds = JSON.parse(draft);
                    checkboxes.forEach(checkbox => checkbox.checked = false);
                    selectedIds.forEach(id => {
                        const checkbox = document.querySelector(`input[value="${id}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                            const card = checkbox.closest('.permission-card');
                            if (card) {
                                card.style.borderColor = '#28a745';
                                card.style.backgroundColor = '#f8fff9';
                            }
                        }
                    });
                    updateSelectedCount();
                } catch (e) {
                    console.warn('Could not restore draft selections');
                }
            }
        }
    });
});
</script>

@endsection
