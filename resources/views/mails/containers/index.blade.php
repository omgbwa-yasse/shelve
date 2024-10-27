@extends('layouts.app')
<style>
    .hover-shadow {
        transition: box-shadow 0.3s ease-in-out, transform 0.2s ease;
    }

    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .list-group-item {
        transition: all 0.3s ease;
    }

    .container-checkbox {
        width: 1.2rem;
        height: 1.2rem;
    }

    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    .btn-group .btn {
        padding: 0.5rem 1rem;
    }

    .modal-header {
        background-color: var(--bs-light);
    }

    .modal-footer {
        background-color: var(--bs-light);
    }

    /* Loading animation */
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
@section('content')
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Contenants pour archivage</h1>
                <p class="text-muted mb-0">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Transfert en cours
                </p>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#transferModal">
                                <i class="bi bi-arrow-repeat me-2"></i>Transférer
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="cartBtn">
                                <i class="bi bi-cart me-2"></i>Panier
                                <span class="badge bg-primary ms-1">0</span>
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="exportBtn">
                                <i class="bi bi-download me-2"></i>Exporter
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="printBtn">
                                <i class="bi bi-printer me-2"></i>Imprimer
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="d-flex justify-content-md-end align-items-center">
                            <div class="me-3">
                                <select class="form-select form-select-sm" id="filterType">
                                    <option value="">Tous les types</option>
                                    <option value="box">Boîtes</option>
                                    <option value="folder">Dossiers</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-outline-secondary" id="checkAllBtn">
                                <i class="bi bi-check-square me-2"></i>Tout sélectionner
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Containers List -->
        <div class="card shadow-sm">
            <div class="card-body" id="mailList">
                @if($mailContainers->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-archive display-4 text-muted"></i>
                        <p class="mt-3 text-muted">Aucun contenant d'archivage disponible</p>
                    </div>
                @else
                    <div class="list-group">
                        @foreach ($mailContainers as $mailContainer)
                            <div class="list-group-item border rounded-3 mb-2 hover-shadow">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input container-checkbox"
                                                   type="checkbox"
                                                   value="{{ $mailContainer->id }}"
                                                   id="container-{{ $mailContainer->id }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="mb-1">{{ $mailContainer->code }}</h5>
                                        <p class="mb-0 text-muted">{{ $mailContainer->name }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-building me-2 text-muted"></i>
                                            <span class="text-truncate">{{ $mailContainer->creatorOrganisation->name }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                                        {{ $mailContainer->containerType->name }}
                                    </span>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('mail-container.show', $mailContainer->id) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-gear me-1"></i>
                                                Paramètres
                                            </a>
                                            <a href="{{ route('mails.sort') }}?categ=container&id={{ $mailContainer->id }}"
                                               class="btn btn-success btn-sm">
                                            <span class="badge bg-white text-success me-1">
                                                {{ $mailContainer->mailArchivings->count() }}
                                            </span>
                                                Courriers
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination if needed -->
                    @if($mailContainers instanceof \Illuminate\Pagination\LengthAwarePaginator && $mailContainers->hasPages())
                        <div class="d-flex justify-content-end mt-4">
                            {{ $mailContainers->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div class="modal fade" id="transferModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        Transférer les contenants
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="transferForm" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label class="form-label">Service destinataire</label>
                            <select class="form-select" name="service_id" required>
                                <option value="">Sélectionner un service...</option>
                                @foreach($services ?? [] as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner un service</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description du transfert</label>
                            <textarea class="form-control"
                                      name="description"
                                      rows="3"
                                      required
                                      placeholder="Décrivez le motif du transfert..."></textarea>
                            <div class="invalid-feedback">Veuillez fournir une description</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Contenants sélectionnés</label>
                            <div id="selectedContainers" class="border rounded-3 p-3 bg-light">
                                <div class="text-muted text-center">
                                    Aucun contenant sélectionné
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="submitTransfer()">
                        <i class="bi bi-send me-2"></i>
                        Confirmer le transfert
                    </button>
                </div>
            </div>
        </div>
    </div>




    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // State management
                let selectedContainers = new Set();

                // Elements
                const checkAllBtn = document.getElementById('checkAllBtn');
                const containerCheckboxes = document.querySelectorAll('.container-checkbox');
                const selectedContainersDiv = document.getElementById('selectedContainers');
                const transferForm = document.getElementById('transferForm');
                const cartBtn = document.getElementById('cartBtn');
                const exportBtn = document.getElementById('exportBtn');
                const printBtn = document.getElementById('printBtn');
                const cartBadge = cartBtn.querySelector('.badge');

                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Check All functionality
                checkAllBtn.addEventListener('click', function() {
                    const isChecking = !this.classList.contains('active');
                    this.classList.toggle('active');

                    containerCheckboxes.forEach(checkbox => {
                        checkbox.checked = isChecking;
                        const containerId = checkbox.value;
                        if (isChecking) {
                            selectedContainers.add(containerId);
                        } else {
                            selectedContainers.delete(containerId);
                        }
                    });

                    updateSelectedContainers();
                    updateActionButtons();
                });

                // Individual checkbox handling
                containerCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const containerId = this.value;
                        if (this.checked) {
                            selectedContainers.add(containerId);
                        } else {
                            selectedContainers.delete(containerId);
                            checkAllBtn.classList.remove('active');
                        }

                        updateSelectedContainers();
                        updateActionButtons();
                    });
                });

                // Update selected containers display
                function updateSelectedContainers() {
                    if (selectedContainers.size === 0) {
                        selectedContainersDiv.innerHTML = `
                <div class="text-muted text-center">
                    Aucun contenant sélectionné
                </div>
            `;
                        return;
                    }

                    const containersList = Array.from(selectedContainers).map(id => {
                        const container = document.querySelector(`#container-${id}`).closest('.list-group-item');
                        const name = container.querySelector('h5').textContent;
                        const type = container.querySelector('.badge').textContent;

                        return `
                <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
                    <div>
                        <strong>${name}</strong>
                        <span class="badge bg-info bg-opacity-10 text-info ms-2">${type}</span>
                    </div>
                    <button type="button" class="btn btn-link text-danger p-0"
                            onclick="removeContainer('${id}')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `;
                    }).join('');

                    selectedContainersDiv.innerHTML = containersList;
                }

                // Update action buttons state
                function updateActionButtons() {
                    const hasSelection = selectedContainers.size > 0;
                    cartBadge.textContent = selectedContainers.size;

                    [cartBtn, exportBtn, printBtn].forEach(btn => {
                        btn.disabled = !hasSelection;
                        if (!hasSelection) {
                            btn.classList.add('disabled');
                        } else {
                            btn.classList.remove('disabled');
                        }
                    });
                }

                // Remove container from selection
                window.removeContainer = function(id) {
                    const checkbox = document.querySelector(`#container-${id}`);
                    checkbox.checked = false;
                    selectedContainers.delete(id);
                    updateSelectedContainers();
                    updateActionButtons();
                };

                // Transfer form submission
                window.submitTransfer = function() {
                    if (!validateForm()) return;

                    const formData = new FormData(transferForm);
                    formData.append('containers', Array.from(selectedContainers));

                    showLoadingSpinner();

                    // Simulated API call - replace with actual endpoint
                    fetch('/api/containers/transfer', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            hideLoadingSpinner();
                            if (data.success) {
                                showNotification('success', 'Transfert effectué avec succès');
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                showNotification('error', data.message || 'Une erreur est survenue');
                            }
                        })
                        .catch(error => {
                            hideLoadingSpinner();
                            showNotification('error', 'Une erreur est survenue lors du transfert');
                            console.error('Error:', error);
                        });
                };

                // Form validation
                function validateForm() {
                    if (!transferForm.checkValidity()) {
                        transferForm.classList.add('was-validated');
                        return false;
                    }

                    if (selectedContainers.size === 0) {
                        showNotification('error', 'Veuillez sélectionner au moins un contenant');
                        return false;
                    }

                    return true;
                }

                // Export functionality
                exportBtn.addEventListener('click', function() {
                    if (selectedContainers.size === 0) return;

                    showLoadingSpinner();
                    const selectedIds = Array.from(selectedContainers);

                    // Replace with actual export endpoint
                    window.location.href = `/api/containers/export?ids=${selectedIds.join(',')}`;
                    setTimeout(hideLoadingSpinner, 1000);
                });

                // Print functionality
                printBtn.addEventListener('click', function() {
                    if (selectedContainers.size === 0) return;

                    const printContent = Array.from(selectedContainers).map(id => {
                        const container = document.querySelector(`#container-${id}`).closest('.list-group-item');
                        return container.outerHTML;
                    }).join('');

                    const printWindow = window.open('', '', 'height=600,width=800');
                    printWindow.document.write(`
            <html>
                <head>
                    <title>Contenants d'archivage</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { padding: 20px; }
                        @media print {
                            .btn { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <h3 class="mb-4">Liste des contenants sélectionnés</h3>
                    <div class="list-group">
                        ${printContent}
                    </div>
                </body>
            </html>
        `);
                    printWindow.document.close();

                    setTimeout(() => {
                        printWindow.print();
                        printWindow.close();
                    }, 250);
                });

                // Notification system
                function showNotification(type, message) {
                    const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3"
                 role="alert" style="z-index: 9999">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

                    document.body.insertAdjacentHTML('beforeend', alertHtml);
                    setTimeout(() => {
                        document.querySelector('.alert').remove();
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
                    if (spinner) spinner.remove();
                }

                // Filter type handling
                const filterType = document.getElementById('filterType');
                filterType?.addEventListener('change', function() {
                    const selectedType = this.value;
                    const containers = document.querySelectorAll('.list-group-item');

                    containers.forEach(container => {
                        const containerType = container.querySelector('.badge').textContent.toLowerCase();
                        if (!selectedType || containerType.includes(selectedType)) {
                            container.style.display = '';
                        } else {
                            container.style.display = 'none';
                        }
                    });
                });

                // Initialize action buttons state
                updateActionButtons();
            });
        </script>
    @endpush

@endsection
