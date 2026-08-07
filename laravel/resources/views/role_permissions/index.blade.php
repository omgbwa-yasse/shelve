@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-shield-check mr-2"></i>
                        {{ __('Role Permissions Matrix') }}
                    </h4>
                    <div class="btn-group" role="group">
                        <a href="{{ route('role_permissions.create') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-plus mr-1"></i>
                            {{ __('Assign Permissions') }}
                        </a>
                        <button type="button" class="btn btn-outline-light btn-sm" id="toggleSelectAll">
                            <i class="bi bi-check-square mr-1"></i>
                            {{ __('Select All') }}
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                            <i class="bi bi-check-circle mr-2"></i>
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                            <i class="bi bi-exclamation-circle mr-2"></i>
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Matrix Form -->
                    <form id="permissionMatrixForm" method="POST" action="{{ route('role_permissions.update_matrix') }}">
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table table-hover mb-0 permission-matrix-table">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th class="permission-column border-right">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-key mr-2"></i>
                                                {{ __('Permissions') }}
                                            </div>
                                        </th>
                                        @foreach($roles as $role)
                                            <th class="text-center role-column" data-role-id="{{ $role->id }}">
                                                <div class="role-header">
                                                    <div class="role-name">{{ $role->display_name ?? $role->name }}</div>
                                                    <div class="role-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary role-select-all"
                                                                data-role-id="{{ $role->id }}" title="{{ __('Select All for :role', ['role' => $role->display_name ?? $role->name]) }}">
                                                            <i class="bi bi-check-square"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary role-deselect-all"
                                                                data-role-id="{{ $role->id }}" title="{{ __('Deselect All for :role', ['role' => $role->display_name ?? $role->name]) }}">
                                                            <i class="bi bi-square"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissionsByCategory as $category => $permissions)
                                        <!-- Category Header -->
                                        <tr class="category-header">
                                            <td colspan="{{ count($roles) + 1 }}" class="bg-light border-bottom">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <strong class="text-primary">
                                                        <i class="bi bi-folder-open mr-2"></i>
                                                        {{ __($categoryLabels[$category] ?? ucfirst($category)) }}
                                                    </strong>
                                                    <div class="category-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary category-select-all"
                                                                data-category="{{ $category }}" title="{{ __('Select All in Category') }}">
                                                            <i class="bi bi-check-square mr-1"></i>
                                                            {{ __('All') }}
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary category-deselect-all"
                                                                data-category="{{ $category }}" title="{{ __('Deselect All in Category') }}">
                                                            <i class="bi bi-square mr-1"></i>
                                                            {{ __('None') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Permissions in Category -->
                                        @foreach($permissions as $permission)
                                            <tr class="permission-row" data-category="{{ $category }}">
                                                <td class="permission-name border-right">
                                                    <div class="permission-info">
                                                        <div class="permission-title">
                                                            {{ __('permissions.' . $permission->name, [], 'fr') }}
                                                        </div>
                                                        @if($permission->description)
                                                            <small class="text-muted permission-description">
                                                                {{ __('permissions.' . $permission->name . '.description', [], 'fr') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </td>
                                                @foreach($roles as $role)
                                                    <td class="text-center permission-checkbox-cell" data-role-id="{{ $role->id }}" data-permission-id="{{ $permission->id }}">
                                                        <div class="form-check form-check-inline">
                                                            <input type="checkbox"
                                                                   class="form-check-input permission-checkbox"
                                                                   name="permissions[{{ $role->id }}][]"
                                                                   value="{{ $permission->id }}"
                                                                   id="permission_{{ $role->id }}_{{ $permission->id }}"
                                                                   data-role-id="{{ $role->id }}"
                                                                   data-permission-id="{{ $permission->id }}"
                                                                   data-category="{{ $category }}"
                                                                   {{ in_array($permission->id, $rolePermissions[$role->id] ?? []) ? 'checked' : '' }}>
                                                            <label class="form-check-label sr-only"
                                                                   for="permission_{{ $role->id }}_{{ $permission->id }}">
                                                                {{ __('Assign :permission to :role', ['permission' => $permission->name, 'role' => $role->name]) }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                            <div class="selected-info">
                                <span class="badge bg-info" id="selectedCount">0</span>
                                <span class="text-muted">{{ __('permissions selected') }}</span>
                            </div>
                            <div class="action-buttons">
                                <button type="button" class="btn btn-outline-secondary mr-2" id="resetForm">
                                    <i class="bi bi-arrow-clockwise mr-1"></i>
                                    {{ __('Reset') }}
                                </button>
                                <button type="submit" class="btn btn-success" id="saveChanges">
                                    <i class="bi bi-check-lg mr-1"></i>
                                    {{ __('Save Changes') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ __('Loading...') }}</span>
        </div>
        <div class="mt-2">{{ __('Saving changes...') }}</div>
    </div>
</div>

@endsection

@section('styles')
<style>
    .permission-matrix-table {
        font-size: 0.9rem;
    }

    .permission-column {
        min-width: 300px;
        max-width: 400px;
        width: 35%;
        position: sticky;
        left: 0;
        background: white;
        z-index: 10;
    }

    .role-column {
        min-width: 120px;
        width: auto;
    }

    .role-header {
        padding: 0.5rem;
    }

    .role-name {
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
    }

    .role-actions {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }

    .permission-info {
        padding: 0.5rem;
    }

    .permission-title {
        font-weight: 500;
        color: #2c3e50;
        line-height: 1.3;
    }

    .permission-description {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.8rem;
        line-height: 1.2;
    }

    .permission-checkbox-cell {
        vertical-align: middle;
        padding: 0.75rem 0.5rem;
    }

    .permission-checkbox {
        transform: scale(1.2);
        cursor: pointer;
    }

    .permission-checkbox:hover {
        transform: scale(1.3);
    }

    .category-header td {
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
    }

    .category-actions {
        display: flex;
        gap: 0.5rem;
    }

    .permission-row:hover {
        background-color: #f8f9fa !important;
    }

    .permission-row:hover .permission-name {
        background-color: #f8f9fa !important;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-content {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Sticky header styling */
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 20;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .permission-column {
            min-width: 250px;
        }

        .role-column {
            min-width: 100px;
        }

        .role-name {
            font-size: 0.8rem;
        }

        .permission-title {
            font-size: 0.85rem;
        }

        .permission-description {
            font-size: 0.75rem;
        }

        .category-actions,
        .role-actions {
            flex-direction: column;
            gap: 0.25rem;
        }
    }

    /* Print styles */
    @media print {
        .card-header,
        .card-footer,
        .category-actions,
        .role-actions {
            display: none !important;
        }

        .table {
            font-size: 0.8rem;
        }
    }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let originalFormData = new FormData($('#permissionMatrixForm')[0]);

    // Update selected count
    function updateSelectedCount() {
        const selectedCount = $('.permission-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);
    }

    // Initial count
    updateSelectedCount();

    // Update count on checkbox change
    $(document).on('change', '.permission-checkbox', function() {
        updateSelectedCount();
    });

    // Global select/deselect all
    $('#toggleSelectAll').on('click', function() {
        const $button = $(this);
        const $icon = $button.find('i');
        const isSelectAll = $icon.hasClass('bi-check-square');

        if (isSelectAll) {
            $('.permission-checkbox').prop('checked', false);
            $icon.removeClass('bi-check-square').addClass('bi-square');
            $button.html('<i class="bi bi-square mr-1"></i> {{ __("Select All") }}');
        } else {
            $('.permission-checkbox').prop('checked', true);
            $icon.removeClass('bi-square').addClass('bi-check-square');
            $button.html('<i class="bi bi-check-square mr-1"></i> {{ __("Deselect All") }}');
        }
        updateSelectedCount();
    });

    // Role select/deselect all
    $('.role-select-all').on('click', function() {
        const roleId = $(this).data('role-id');
        $(`.permission-checkbox[data-role-id="${roleId}"]`).prop('checked', true);
        updateSelectedCount();
    });

    $('.role-deselect-all').on('click', function() {
        const roleId = $(this).data('role-id');
        $(`.permission-checkbox[data-role-id="${roleId}"]`).prop('checked', false);
        updateSelectedCount();
    });

    // Category select/deselect all
    $('.category-select-all').on('click', function() {
        const category = $(this).data('category');
        $(`.permission-checkbox[data-category="${category}"]`).prop('checked', true);
        updateSelectedCount();
    });

    $('.category-deselect-all').on('click', function() {
        const category = $(this).data('category');
        $(`.permission-checkbox[data-category="${category}"]`).prop('checked', false);
        updateSelectedCount();
    });

    // Reset form
    $('#resetForm').on('click', function() {
        if (confirm('{{ __("Are you sure you want to reset all changes?") }}')) {
            // Reset checkboxes to original state
            $('.permission-checkbox').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                const originalInput = originalFormData.getAll(name);
                $(this).prop('checked', originalInput.includes(value));
            });
            updateSelectedCount();
        }
    });

    // Form submission with loading
    $('#permissionMatrixForm').on('submit', function(e) {
        e.preventDefault();

        // Show loading overlay
        $('#loadingOverlay').show();

        // Disable form elements
        $(this).find('input, button').prop('disabled', true);

        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#loadingOverlay').hide();

                // Show success message
                if (response.success) {                $('<div class="alert alert-success alert-dismissible fade show m-3" role="alert">' +
                  '<i class="bi bi-check-circle mr-2"></i>' + response.message +
                  '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                  '<span aria-hidden="true">&times;</span></button>' +
                  '</div>').prependTo('.card-body');

                    // Update original form data
                    originalFormData = new FormData($('#permissionMatrixForm')[0]);
                }
            },
            error: function(xhr) {
                $('#loadingOverlay').hide();

                let errorMessage = '{{ __("An error occurred while saving changes.") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                $('<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">' +
                  '<i class="bi bi-exclamation-circle mr-2"></i>' + errorMessage +
                  '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                  '<span aria-hidden="true">&times;</span></button>' +
                  '</div>').prependTo('.card-body');
            },
            complete: function() {
                // Re-enable form elements
                $('#permissionMatrixForm').find('input, button').prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
