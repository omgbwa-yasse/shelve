@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary bg-gradient d-flex justify-content-between align-items-center py-3">
                <h1 class="h3 text-white mb-0">{{ $mail->name ?? 'Sans nom' }}</h1>
                <span class="badge bg-light text-primary">
                    #{{ $mail->code ?? 'N/A' }}
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="card-header bg-light border-bottom d-flex gap-2">
                <button onclick="window.history.back()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Retour
                </button>
                <div class="ms-auto">
                    @can('update', $mail)
                        <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil me-1"></i> Modifier
                        </a>
                    @endcan
                    @can('delete', $mail)
                        <button onclick="confirmDelete()" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash me-1"></i> Supprimer
                        </button>
                    @endcan
                    @can('create', [\App\Models\Attachment::class, $mail])
                        <a href="{{ route('mail-attachment.create', ['mail' => $mail]) }}"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-paperclip me-1"></i> Ajouter une pièce jointe
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="card shadow-sm">
            <div class="card-header bg-white p-0">
                <!-- Tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#details">
                            <i class="bi bi-info-circle me-1"></i> Détails
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#attachments">
                            <i class="bi bi-paperclip me-1"></i> Pièces jointes
                            @if($mail->attachments && $mail->attachments->count() > 0)
                                <span class="badge rounded-pill bg-primary ms-1">
                                    {{ $mail->attachments->count() }}
                                </span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#history">
                            <i class="bi bi-clock-history me-1"></i> Historique
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content">
                    <!-- Details Tab -->
                    <div class="tab-pane fade show active" id="details">
                        <div class="row">
                            <!-- Informations de base -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Informations de base</h5>
                                <dl class="row">
                                    <dt class="col-sm-4">ID</dt>
                                    <dd class="col-sm-8">{{ $mail->id }}</dd>

                                    <dt class="col-sm-4">Date</dt>
                                    <dd class="col-sm-8">{{ optional($mail->date)->format('d/m/Y') ?? 'N/A' }}</dd>

                                    <dt class="col-sm-4">Expéditeur</dt>
                                    <dd class="col-sm-8">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div>
                                                <span class="text-body">{{ optional($mail->sender)->name ?? 'N/A' }}</span>
                                                <small class="text-muted d-block">{{ optional($mail->senderOrganisation)->name ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </dd>

                                    <dt class="col-sm-4">Destinataire</dt>
                                    <dd class="col-sm-8">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                                <i class="bi bi-person-check text-success"></i>
                                            </div>
                                            <div>
                                                <span class="text-body">{{ optional($mail->recipient)->name ?? 'N/A' }}</span>
                                                <small class="text-muted d-block">{{ optional($mail->recipientOrganisation)->name ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </dd>

                                    <dt class="col-sm-4">Auteur(s)</dt>
                                    <dd class="col-sm-8">
                                        @forelse($mail->authors ?? [] as $author)
                                            <span class="badge bg-info">{{ $author->name }}</span>
                                        @empty
                                            <span class="text-muted">Aucun auteur</span>
                                        @endforelse
                                    </dd>
                                </dl>
                            </div>

                            <!-- Classification -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Classification</h5>
                                <dl class="row">
                                    <dt class="col-sm-4">Priorité</dt>
                                    <dd class="col-sm-8">
                                        @php
                                            $priorityClass = match(strtolower($mail->priority?->name ?? '')) {
                                                'high' => 'danger',
                                                'medium' => 'warning',
                                                'low' => 'success',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $priorityClass }}">
                                            {{ $mail->priority?->name ?? 'N/A' }}
                                        </span>
                                    </dd>

                                    <dt class="col-sm-4">Type de courrier</dt>
                                    <dd class="col-sm-8">{{ $mail->type?->name ?? 'N/A' }}</dd>

                                    <dt class="col-sm-4">Nature</dt>
                                    <dd class="col-sm-8">{{ $mail->documentType?->name ?? 'N/A' }}</dd>

                                    <dt class="col-sm-4">État</dt>
                                    <dd class="col-sm-8">
                                        @php
                                            $statusClass = match(strtolower($mail->status ?? '')) {
                                                'completed' => 'success',
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ $mail->status ?? 'N/A' }}
                                        </span>
                                    </dd>

                                    <dt class="col-sm-4">Action requise</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-primary">{{ optional($mail->action)->name ?? 'N/A' }}</span>
                                    </dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($mail->description)
                            <div class="mt-4">
                                <h5 class="border-bottom pb-2 mb-3">Description</h5>
                                <p class="text-muted">{{ $mail->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Attachments Tab -->
                    <div class="tab-pane fade" id="attachments">
                        @if($mail->attachments && $mail->attachments->isNotEmpty())
                            <div class="row g-4">
                                @foreach($mail->attachments as $attachment)
                                    <div class="col-sm-6 col-md-4 col-lg-3">
                                        <div class="card h-100">
                                            <div class="card-img-top bg-light" style="height: 160px;">
                                                @if($attachment->thumbnail_path)
                                                    <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}"
                                                         alt="Aperçu"
                                                         class="img-fluid h-100 w-100 object-fit-cover">
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center h-100">
                                                        <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title text-truncate" title="{{ $attachment->name }}">
                                                    {{ $attachment->name }}
                                                </h6>
                                                <p class="card-text small text-muted">
                                                    {{ number_format($attachment->size / 1024, 2) }} KB<br>
                                                    <i class="bi bi-person me-1"></i>{{ optional($attachment->creator)->name ?? 'N/A' }}
                                                </p>
                                                <div class="btn-group w-100">
                                                    @can('view', $attachment)
                                                        <a href="{{ route('mail-attachment.show', [$mail->id, $attachment->id]) }}"
                                                           class="btn btn-outline-primary btn-sm"
                                                           target="_blank">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    @endcan
                                                    @can('download', $attachment)
                                                        <a href="{{ route('mail-attachment.download', $attachment->id) }}"
                                                           class="btn btn-outline-primary btn-sm"
                                                           download>
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $attachment)
                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm"
                                                                onclick="confirmDeleteAttachment({{ $attachment->id }})">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-file-earmark-x display-1 text-muted"></i>
                                <p class="mt-2 text-muted">Aucune pièce jointe</p>
                            </div>
                        @endif

                        @can('create', [\App\Models\Attachment::class, $mail])
                            <!-- Add Attachment Form -->
                            <div class="mt-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Ajouter une pièce jointe</h5>
                                        <form action="{{ route('mail-attachment.store', $mail->id) }}"
                                              method="POST"
                                              enctype="multipart/form-data"
                                              id="attachmentForm">
                                            @csrf
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Nom du fichier</label>
                                                    <input type="text"
                                                           name="name"
                                                           class="form-control @error('name') is-invalid @enderror"
                                                           required>
                                                    @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Fichier (PDF, Image ou Vidéo)</label>
                                                    <input type="file"
                                                           name="file"
                                                           class="form-control @error('file') is-invalid @enderror"
                                                           accept="application/pdf,image/*,video/*"
                                                           required>
                                                    @error('file')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div id="file-preview" class="mt-3 d-none">
                                                <!-- Preview will be inserted here -->
                                            </div>

                                            <input type="hidden" name="thumbnail" id="thumbnailInput">

                                            <div class="mt-3">
                                                <button type="submit"
                                                        class="btn btn-primary"
                                                        id="submitBtn"
                                                        disabled>
                                                    <i class="bi bi-plus-circle me-1"></i>
                                                    Ajouter la pièce jointe
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endcan
                    </div>

                    <!-- History Tab -->
                    <div class="tab-pane fade" id="history">
                        @if($mail->history && $mail->history->isNotEmpty())
                            <div class="timeline">
                                @foreach($mail->history as $history)
                                    <div class="timeline-item pb-4 position-relative ms-4">
                                        <div class="timeline-marker bg-primary rounded-circle position-absolute"
                                             style="width: 12px; height: 12px; left: -6px; top: 0;"></div>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="card-title mb-0">
                                                        {{ optional($history->action)->name ?? 'Action inconnue' }}
                                                    </h6>
                                                    <span class="badge bg-light text-dark">
                                                        {{ optional($history->created_at)->format('d/m/Y H:i') ?? 'Date inconnue' }}
                                                    </span>
                                                </div>
                                                <div class="small text-muted">
                                                    <div class="mb-2">
                                                        <i class="bi bi-send me-2"></i>
                                                        De: {{ optional($history->sender)->name ?? 'N/A' }}
                                                        ({{ optional($history->senderOrganisation)->name ?? 'N/A' }})
                                                    </div>
                                                    <div class="mb-2">
                                                        <i class="bi bi-reply me-2"></i>
                                                        À: {{ optional($history->recipient)->name ?? 'N/A' }}
                                                        ({{ optional($history->recipientOrganisation)->name ?? 'N/A' }})
                                                    </div>
                                                    @if($history->comment)
                                                        <div class="mt-2 p-2 bg-light rounded">
                                                            <i class="bi bi-chat-left-text me-2"></i>
                                                            {{ $history->comment }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-clock-history display-1 text-muted"></i>
                                <p class="mt-2 text-muted">Aucun historique disponible</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Mail Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-trash display-4 text-danger"></i>
                    </div>
                    <p>Êtes-vous sûr de vouloir supprimer ce courrier ? Cette action est irréversible.</p>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-1 text-muted small">Courrier : <strong>{{ $mail->name ?? 'Sans nom' }}</strong></p>
                        <p class="mb-0 text-muted small">Code : <strong>#{{ $mail->code ?? 'N/A' }}</strong></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Annuler
                    </button>

                </div>
            </div>
        </div>
    </div>

    <!-- Delete Attachment Modal -->
    <div class="modal fade" id="deleteAttachmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-file-earmark-x display-4 text-danger"></i>
                    </div>
                    <p>Êtes-vous sûr de vouloir supprimer cette pièce jointe ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Annuler
                    </button>
                    <form id="deleteAttachmentForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Timeline Styles */
            .timeline {
                position: relative;
                padding-left: 1rem;
            }

            .timeline::before {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 2px;
                background-color: var(--bs-primary);
                opacity: 0.2;
            }

            .timeline-marker {
                border: 2px solid #fff;
                box-shadow: 0 0 0 2px var(--bs-primary);
            }

            /* Card enhancements */
            .card {
                transition: box-shadow 0.3s ease;
            }

            .card:hover {
                box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
            }

            /* Tab styling */
            .nav-tabs .nav-link {
                padding: 0.75rem 1.25rem;
                border: none;
                border-bottom: 2px solid transparent;
                color: var(--bs-gray-600);
            }

            .nav-tabs .nav-link:hover {
                border-color: transparent;
                color: var(--bs-primary);
            }

            .nav-tabs .nav-link.active {
                border: none;
                border-bottom: 2px solid var(--bs-primary);
                color: var(--bs-primary);
                background: transparent;
            }

            /* Badges */
            .badge {
                font-weight: 500;
                padding: 0.5em 0.75em;
            }

            /* Icons in circles */
            .rounded-circle {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* File preview */
            .object-fit-cover {
                object-fit: cover;
            }

            /* Loading spinner */
            .loading-spinner {
                width: 1.5rem;
                height: 1.5rem;
                border: 0.2rem solid var(--bs-light);
                border-right-color: var(--bs-primary);
                border-radius: 50%;
                animation: spinner-border .75s linear infinite;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // File handling
                const fileInput = document.querySelector('input[name="file"]');
                const nameInput = document.querySelector('input[name="name"]');
                const submitBtn = document.getElementById('submitBtn');
                const filePreview = document.getElementById('file-preview');
                const thumbnailInput = document.getElementById('thumbnailInput');

                if (fileInput && nameInput && submitBtn) {
                    const updateSubmitButton = () => {
                        submitBtn.disabled = !(fileInput.files.length > 0 && nameInput.value.trim() !== '');
                    };

                    fileInput.addEventListener('change', handleFileSelect);
                    nameInput.addEventListener('input', updateSubmitButton);
                }

                function handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) {
                        filePreview.classList.add('d-none');
                        return;
                    }

                    filePreview.classList.remove('d-none');
                    showLoadingPreview();

                    if (file.type.startsWith('image/')) {
                        handleImagePreview(file);
                    } else if (file.type === 'application/pdf') {
                        handlePDFPreview(file);
                    } else {
                        handleGenericPreview(file);
                    }
                }

                function showLoadingPreview() {
                    filePreview.innerHTML = `
                        <div class="text-center py-3">
                            <div class="loading-spinner mx-auto mb-2"></div>
                            <p class="text-muted small">Préparation de l'aperçu...</p>
                        </div>
                    `;
                }

                function handleImagePreview(file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        filePreview.innerHTML = `
                            <img src="${e.target.result}" class="img-fluid rounded" alt="Aperçu">
                        `;
                        thumbnailInput.value = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }

                function handlePDFPreview(file) {
                    filePreview.innerHTML = `
                        <div class="text-center py-3">
                            <i class="bi bi-file-pdf display-4 text-danger"></i>
                            <p class="mt-2 mb-0">${file.name}</p>
                            <small class="text-muted">${formatFileSize(file.size)}</small>
                        </div>
                    `;
                }

                function handleGenericPreview(file) {
                    filePreview.innerHTML = `
                        <div class="text-center py-3">
                            <i class="bi bi-file-earmark display-4 text-muted"></i>
                            <p class="mt-2 mb-0">${file.name}</p>
                            <small class="text-muted">${formatFileSize(file.size)}</small>
                        </div>
                    `;
                }

                function formatFileSize(bytes) {
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    if (bytes === 0) return '0 Byte';
                    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
                    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
                }

                // Delete confirmation handlers
                window.confirmDelete = function() {
                    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
                    modal.show();
                };

                window.confirmDeleteAttachment = function(attachmentId) {
                    const modal = new bootstrap.Modal(document.getElementById('deleteAttachmentModal'));
                    const form = document.getElementById('deleteAttachmentForm');
                    form.action = `/mail-attachments/${attachmentId}`;
                    modal.show();
                };

                // Initialize tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Handle tab navigation from URL hash
                const hash = window.location.hash;
                if (hash) {
                    const tab = document.querySelector(`button[data-bs-target="${hash}"]`);
                    if (tab) {
                        new bootstrap.Tab(tab).show();
                    }
                }
            });
        </script>
    @endpush
@endsection
