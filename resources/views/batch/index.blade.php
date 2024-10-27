@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Mes Parapheurs</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
{{--                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>--}}
                        <li class="breadcrumb-item active">Parapheurs</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="bi bi-funnel me-1"></i> Filtrer
                </button>
                @can('create', App\Models\MailBatch::class)
                    <a href="{{ route('batch.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Nouveau Parapheur
                    </a>
                @endcan
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-folder text-primary h4 mb-0"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Total Parapheurs</h6>
                                <h3 class="mb-0">{{ $mailBatches->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-inbox text-success h4 mb-0"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Présents</h6>
                                <h3 class="mb-0">{{ $mailBatches->filter(fn($batch) => $batch->transactions->last()->organisationReceived->id == Auth()->user()->current_organisation_id)->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-warning bg-opacity-10 p-3 rounded">
                                <i class="bi bi-send text-warning h4 mb-0"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">En Transit</h6>
                                <h3 class="mb-0">{{ $mailBatches->filter(fn($batch) => $batch->transactions->last()->organisationReceived->id != Auth()->user()->current_organisation_id)->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded">
                                <i class="bi bi-file-text text-info h4 mb-0"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Total Courriers</h6>
                                <h3 class="mb-0">{{ $mailBatches->sum(fn($batch) => $batch->mails->count()) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th class="border-0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th class="border-0">Code</th>
                            <th class="border-0" style="min-width: 250px;">Intitulé</th>
                            <th class="border-0 text-center">Courriers</th>
                            <th class="border-0">Localisation</th>
                            <th class="border-0 text-center">Statut</th>
                            <th class="border-0">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($mailBatches as $batch)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="{{ $batch->id }}">
                                    </div>
                                </td>
                                <td><span class="small text-muted">#{{ $batch->code }}</span></td>
                                <td>
                                    <a href="{{ route('batch.show', $batch) }}" class="text-decoration-none">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle bg-light p-2">
                                                    <i class="bi bi-folder2 text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0">{{ $batch->name }}</h6>
                                                <small class="text-muted">
                                                    Créé le {{ $batch->created_at }}
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill">
                                        {{ $batch->mails->count() }}
                                    </span>
                                </td>
                                <td>
                                    @if($batch->transactions->last()->organisationReceived->id == Auth()->user()->current_organisation_id)
                                        <div class="d-flex align-items-center text-success">
                                            <i class="bi bi-geo-alt me-1"></i> Présent
                                        </div>
                                    @else
                                        <div>
                                            <div class="small fw-medium">
                                                {{ $batch->transactions->last()->organisationReceived->name }}
                                            </div>
                                            <small class="text-muted">
                                                Depuis: {{ $batch->transactions->last()->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $status = $batch->transactions->last()->organisationReceived->id == Auth()->user()->current_organisation_id
                                            ? ['class' => 'success', 'text' => 'Disponible']
                                            : ['class' => 'warning', 'text' => 'En transit'];
                                    @endphp
                                    <span class="badge bg-{{ $status['class'] }}-subtle text-{{ $status['class'] }} px-3 py-2">
                                        {{ $status['text'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('mails.sort') }}?categ=batch&id={{$batch->id}}">
                                                    <i class="bi bi-eye me-2"></i> Voir le contenu
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('batch.show', $batch) }}">
                                                    <i class="bi bi-info-circle me-2"></i> Détails
                                                </a>
                                            </li>
                                            @if($batch->transactions->last()->organisationReceived->id == Auth()->user()->current_organisation_id)
                                                <li>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#transferModal" data-batch-id="{{ $batch->id }}">
                                                        <i class="bi bi-send me-2"></i> Transférer
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4"></i>
                                        <p class="mt-2">Aucun parapheur disponible</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($mailBatches instanceof \Illuminate\Pagination\LengthAwarePaginator && $mailBatches->total() > $mailBatches->perPage())
                    <div class="d-flex justify-content-end mt-4">
                        {{ $mailBatches->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filtrer les parapheurs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="filterForm">
                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous</option>
                                <option value="present">Présent</option>
                                <option value="transit">En transit</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Période</label>
                            <select class="form-select" name="period">
                                <option value="">Toutes les périodes</option>
                                <option value="today">Aujourd'hui</option>
                                <option value="week">Cette semaine</option>
                                <option value="month">Ce mois</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">Appliquer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div class="modal fade" id="transferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transférer le parapheur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="transferForm">
                        <div class="mb-3">
                            <label class="form-label">Organisation destinataire</label>
                            <select class="form-select" name="organisation_id" required>
                                <option value="">Sélectionner...</option>
                                <!-- Add your organizations here -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Commentaire</label>
                            <textarea class="form-control" name="comment" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="transferBatch()">Transférer</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Card hover effects */
            .card {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
            }

            /* Stats cards icons */
            .stats-icon {
                width: 45px;
                height: 45px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
            }

            /* Table enhancements */
            .table > :not(caption) > * > * {
                padding: 1rem 1rem;
            }

            .table tbody tr {
                transition: background-color 0.15s ease-in-out;
            }

            .table tbody tr:hover {
                background-color: rgba(var(--bs-primary-rgb), 0.02);
            }

            /* Status badges */
            .badge {
                font-weight: 500;
                letter-spacing: 0.3px;
            }

            .bg-success-subtle {
                background-color: rgba(25, 135, 84, 0.1);
            }

            .bg-warning-subtle {
                background-color: rgba(255, 193, 7, 0.1);
            }

            /* Custom checkbox style */
            .form-check-input {
                cursor: pointer;
                border-width: 2px;
            }

            .form-check-input:checked {
                background-color: var(--bs-primary);
                border-color: var(--bs-primary);
            }

            /* Dropdown menu enhancements */
            .dropdown-item {
                padding: 0.5rem 1rem;
                transition: background-color 0.15s ease-in-out;
            }

            .dropdown-item:hover {
                background-color: rgba(var(--bs-primary-rgb), 0.08);
            }

            /* Modal enhancements */
            .modal-header {
                background-color: var(--bs-light);
            }

            .modal-footer {
                background-color: var(--bs-light);
                border-top: 1px solid var(--bs-border-color);
            }

            /* Pagination styling */
            .pagination {
                margin-bottom: 0;
            }

            .page-link {
                padding: 0.375rem 0.75rem;
                border-radius: 0.25rem;
                margin: 0 0.125rem;
            }

            /* Animation for alerts */
            .alert {
                animation: slideDown 0.3s ease-out;
            }

            @keyframes slideDown {
                from {
                    transform: translateY(-100%);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            /* Loading spinner */
            .spinner-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Handle select all checkbox
                const selectAllCheckbox = document.getElementById('selectAll');
                const itemCheckboxes = document.querySelectorAll('tbody .form-check-input');

                selectAllCheckbox?.addEventListener('change', function() {
                    itemCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateBulkActionsVisibility();
                });

                itemCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateSelectAllCheckbox();
                        updateBulkActionsVisibility();
                    });
                });

                function updateSelectAllCheckbox() {
                    const checkedCount = document.querySelectorAll('tbody .form-check-input:checked').length;
                    selectAllCheckbox.checked = checkedCount === itemCheckboxes.length;
                    selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < itemCheckboxes.length;
                }

                // Handle bulk actions visibility
                function updateBulkActionsVisibility() {
                    const checkedCount = document.querySelectorAll('tbody .form-check-input:checked').length;
                    const bulkActionsContainer = document.getElementById('bulkActionsContainer');
                    if (bulkActionsContainer) {
                        if (checkedCount > 0) {
                            bulkActionsContainer.classList.remove('d-none');
                        } else {
                            bulkActionsContainer.classList.add('d-none');
                        }
                    }
                }

                // Handle batch transfer
                window.transferBatch = function() {
                    const form = document.getElementById('transferForm');
                    const formData = new FormData(form);
                    const batchId = document.querySelector('[data-batch-id]').dataset.batchId;

                    // Show loading spinner
                    showLoadingSpinner();

                    fetch(`/batch/${batchId}/transfer`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification('success', 'Parapheur transféré avec succès');
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                showNotification('error', data.message || 'Une erreur est survenue');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('error', 'Une erreur est survenue lors du transfert');
                        })
                        .finally(() => {
                            hideLoadingSpinner();
                            bootstrap.Modal.getInstance(document.getElementById('transferModal')).hide();
                        });
                };

                // Handle filters
                window.applyFilters = function() {
                    const form = document.getElementById('filterForm');
                    const formData = new URLSearchParams(new FormData(form));

                    showLoadingSpinner();
                    window.location.href = `${window.location.pathname}?${formData.toString()}`;
                };

                // Notification system
                function showNotification(type, message) {
                    const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

                    const alertContainer = document.createElement('div');
                    alertContainer.style.position = 'fixed';
                    alertContainer.style.top = '1rem';
                    alertContainer.style.right = '1rem';
                    alertContainer.style.zIndex = '9999';
                    alertContainer.innerHTML = alertHtml;

                    document.body.appendChild(alertContainer);

                    setTimeout(() => {
                        alertContainer.remove();
                    }, 5000);
                }

                // Loading spinner
                function showLoadingSpinner() {
                    const spinner = document.createElement('div');
                    spinner.className = 'spinner-overlay';
                    spinner.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        `;
                    document.body.appendChild(spinner);
                }

                function hideLoadingSpinner() {
                    const spinner = document.querySelector('.spinner-overlay');
                    if (spinner) {
                        spinner.remove();
                    }
                }

                // Handle auto-dismiss alerts
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        bootstrap.Alert.getOrCreateInstance(alert).close();
                    }, 5000);
                });

                // Initialize any date pickers if needed
                const dateInputs = document.querySelectorAll('input[type="date"]');
                dateInputs.forEach(input => {
                    // Add your date picker initialization here if needed
                });

                // Handle organization select dynamic loading
                const organizationSelect = document.querySelector('select[name="organisation_id"]');
                if (organizationSelect) {
                    fetch('/api/organizations')
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(org => {
                                const option = new Option(org.name, org.id);
                                organizationSelect.add(option);
                            });
                        })
                        .catch(error => console.error('Error loading organizations:', error));
                }

                // Reset form on modal close
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    modal.addEventListener('hidden.bs.modal', function() {
                        const form = this.querySelector('form');
                        if (form) form.reset();
                    });
                });

                // Handle responsive table
                const table = document.querySelector('.table-responsive');
                if (table) {
                    let isScrolling = false;
                    table.addEventListener('scroll', function() {
                        if (!isScrolling) {
                            table.classList.add('scrolling');
                            isScrolling = true;
                        }
                        clearTimeout(window.scrollTimeout);
                        window.scrollTimeout = setTimeout(function() {
                            table.classList.remove('scrolling');
                            isScrolling = false;
                        }, 150);
                    });
                }
            });
        </script>
    @endpush
@endsection
