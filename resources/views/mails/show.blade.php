@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary bg-gradient d-flex justify-content-between align-items-center py-3">
                <h1 class="h3 text-white mb-0">{{ $mail->name }}</h1>
                <span class="badge bg-light text-primary">
                #{{ $mail->code }}
            </span>
            </div>

            <!-- Action Buttons -->
            <div class="card-header bg-light border-bottom d-flex gap-2">
                <button onclick="window.history.back()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </button>
                <div class="ms-auto">
                    <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <button onclick="confirmDelete()" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                    <a href="{{ route('mail-attachment.create', ['file' => $mail]) }}"
                       class="btn btn-primary btn-sm">
                        <i class="bi bi-paperclip me-1"></i> Add Attachment
                    </a>
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
                            <i class="bi bi-info-circle me-1"></i> Details
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#attachments">
                            <i class="bi bi-paperclip me-1"></i> Attachments
                            @if($mail->attachments->count() > 0)
                                <span class="badge rounded-pill bg-primary ms-1">
                                {{ $mail->attachments->count() }}
                            </span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#transactions">
                            <i class="bi bi-clock-history me-1"></i> History
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content">
                    <!-- Details Tab -->
                    <div class="tab-pane fade show active" id="details">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                                <dl class="row">
                                    <dt class="col-sm-4">ID</dt>
                                    <dd class="col-sm-8">{{ $mail->id }}</dd>

                                    <dt class="col-sm-4">Date</dt>
                                    <dd class="col-sm-8">{{ $mail->date}}</dd>

                                    <dt class="col-sm-4">Author(s)</dt>
                                    <dd class="col-sm-8">
                                        @foreach($mail->authors as $author)
                                            <span class="badge bg-info">{{ $author->name }}</span>
                                        @endforeach
                                    </dd>
                                </dl>
                            </div>

                            <!-- Classification -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Classification</h5>
                                <dl class="row">
                                    <dt class="col-sm-4">Priority</dt>
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

                                    <dt class="col-sm-4">Mail Type</dt>
                                    <dd class="col-sm-8">{{ $mail->type?->name ?? 'N/A' }}</dd>

                                    <dt class="col-sm-4">Nature</dt>
                                    <dd class="col-sm-8">{{ $mail->documentType?->name ?? 'N/A' }}</dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <h5 class="border-bottom pb-2 mb-3">Description</h5>
                            <p class="text-muted">{{ $mail->description }}</p>
                        </div>
                    </div>

                    <!-- Attachments Tab -->
                    <div class="tab-pane fade" id="attachments">
                        @if($mail->attachments->isNotEmpty())
                            <div class="row g-4">
                                @foreach($mail->attachments as $attachment)
                                    <div class="col-sm-6 col-md-4 col-lg-3">
                                        <div class="card h-100">
                                            <div class="card-img-top bg-light" style="height: 160px;">
                                                @if($attachment->thumbnail_path)
                                                    <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}"
                                                         alt="Preview"
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
                                                    <i class="bi bi-person me-1"></i>{{ $attachment->creator->name ?? 'N/A' }}
                                                </p>
                                                <a href="{{ route('mail-attachment.show', [$mail->id, $attachment->id]) }}"
                                                   class="btn btn-outline-primary btn-sm w-100"
                                                   target="_blank">
                                                    <i class="bi bi-eye me-1"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-file-earmark-x display-1 text-muted"></i>
                                <p class="mt-2 text-muted">No attachments found</p>
                            </div>
                        @endif

                        <!-- Add Attachment Form -->
                        <div class="mt-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Add New Attachment</h5>
                                    <form action="{{ route('mail-attachment.store', $mail->id) }}"
                                          method="POST"
                                          enctype="multipart/form-data"
                                          id="attachmentForm">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">File Name</label>
                                                <input type="text"
                                                       name="name"
                                                       class="form-control"
                                                       required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">File (PDF, Image, or Video)</label>
                                                <input type="file"
                                                       name="file"
                                                       class="form-control"
                                                       accept="application/pdf,image/*,video/*"
                                                       required>
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
                                                Add Attachment
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Tab -->
                    <div class="tab-pane fade" id="transactions">
                        @if($mail->transactions->isNotEmpty())
                            <div class="timeline">
                                @foreach($mail->transactions as $transaction)
                                    <div class="timeline-item pb-4 position-relative ms-4">
                                        <div class="timeline-marker bg-primary rounded-circle position-absolute"
                                             style="width: 12px; height: 12px; left: -6px; top: 0;"></div>
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">{{ $transaction->code }}</h5>
                                                <div class="small text-muted">
                                                    <div class="mb-1">
                                                        <i class="bi bi-calendar me-2"></i>
                                                        {{ $transaction->date_creation }}
                                                    </div>
                                                    <div class="mb-1">
                                                        <i class="bi bi-send me-2"></i>
                                                        From: {{ $transaction->organisationSend?->name }}
                                                        ({{ $transaction->userSend?->name }})
                                                    </div>
                                                    <div class="mb-1">
                                                        <i class="bi bi-reply me-2"></i>
                                                        To: {{ $transaction->organisationReceived?->name }}
                                                        ({{ $transaction->userReceived?->name }})
                                                    </div>
                                                    <div class="border-top mt-2 pt-2 d-flex gap-3">
                                                <span>
                                                    Created: {{ $transaction->created_at->format('M d, Y H:i') }}
                                                </span>
                                                        <span>
                                                    Updated: {{ $transaction->updated_at->format('M d, Y H:i') }}
                                                </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-clock-history display-1 text-muted"></i>
                                <p class="mt-2 text-muted">No transaction history available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="delete-form" action="{{ route('mails.destroy', $mail->id) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
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
                background-color: #dee2e6;
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

            /* File preview container */
            #file-preview {
                background-color: #f8f9fa;
                border-radius: 0.375rem;
                padding: 1rem;
            }

            #file-preview img,
            #file-preview video {
                max-height: 200px;
                margin: 0 auto;
                display: block;
                border-radius: 0.375rem;
            }

            /* Custom scrollbar for better UX */
            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }

            ::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }

            /* Object fit utility */
            .object-fit-cover {
                object-fit: cover;
            }

            /* Loading animation */
            .loading-spinner {
                border: 3px solid #f3f3f3;
                border-top: 3px solid var(--bs-primary);
                border-radius: 50%;
                width: 24px;
                height: 24px;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            /* Badge enhancements */
            .badge {
                font-weight: 500;
                padding: 0.5em 0.75em;
            }

            /* Navigation tabs enhancement */
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

            /* Description text enhancement */
            .text-muted {
                white-space: pre-line;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // File Upload Preview Handling
                const fileInput = document.getElementById('file');
                const nameInput = document.getElementById('name');
                const submitBtn = document.getElementById('submitBtn');
                const filePreview = document.getElementById('file-preview');
                const thumbnailInput = document.getElementById('thumbnailInput');
                const attachmentForm = document.getElementById('attachmentForm');

                // Form validation and submit button state
                function updateSubmitButton() {
                    const isValid = fileInput.files.length > 0 && nameInput.value.trim() !== '';
                    submitBtn.disabled = !isValid;
                }

                // File input change handler
                fileInput?.addEventListener('change', async function(e) {
                    const file = e.target.files[0];
                    if (!file) {
                        filePreview.classList.add('d-none');
                        return;
                    }

                    // Show preview container with loading state
                    filePreview.classList.remove('d-none');
                    filePreview.innerHTML = `
            <div class="text-center py-3">
                <div class="loading-spinner mx-auto mb-2"></div>
                <p class="text-muted small">Preparing preview...</p>
            </div>
        `;

                    try {
                        if (file.type.startsWith('image/')) {
                            await handleImagePreview(file);
                        } else if (file.type === 'application/pdf') {
                            await handlePDFPreview(file);
                        } else if (file.type.startsWith('video/')) {
                            await handleVideoPreview(file);
                        } else {
                            handleGenericFilePreview(file);
                        }
                    } catch (error) {
                        console.error('Preview generation failed:', error);
                        handleGenericFilePreview(file);
                    }

                    updateSubmitButton();
                });

                // Handle Image Preview
                async function handleImagePreview(file) {
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = new Image();
                            img.src = e.target.result;
                            img.className = 'img-fluid';

                            img.onload = function() {
                                filePreview.innerHTML = '';
                                filePreview.appendChild(img);
                                thumbnailInput.value = e.target.result;
                                resolve();
                            };
                        };
                        reader.readAsDataURL(file);
                    });
                }

                // Handle PDF Preview
                async function handlePDFPreview(file) {
                    const canvas = document.createElement('canvas');
                    canvas.className = 'img-fluid';
                    filePreview.innerHTML = '';
                    filePreview.appendChild(canvas);

                    const pdf = await pdfjsLib.getDocument(URL.createObjectURL(file)).promise;
                    const page = await pdf.getPage(1);
                    const viewport = page.getViewport({ scale: 1.5 });

                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    await page.render({
                        canvasContext: canvas.getContext('2d'),
                        viewport: viewport
                    }).promise;

                    thumbnailInput.value = canvas.toDataURL('image/jpeg');
                }

                // Handle Video Preview
                async function handleVideoPreview(file) {
                    return new Promise((resolve) => {
                        const video = document.createElement('video');
                        video.src = URL.createObjectURL(file);
                        video.controls = true;
                        video.className = 'img-fluid';

                        video.onloadeddata = function() {
                            filePreview.innerHTML = '';
                            filePreview.appendChild(video);

                            // Generate thumbnail from first frame
                            video.currentTime = 1;
                            video.addEventListener('seeked', function() {
                                const canvas = document.createElement('canvas');
                                canvas.width = video.videoWidth;
                                canvas.height = video.videoHeight;
                                canvas.getContext('2d').drawImage(video, 0, 0);
                                thumbnailInput.value = canvas.toDataURL('image/jpeg');
                                resolve();
                            }, { once: true });
                        };
                    });
                }

                // Handle Generic File Preview
                function handleGenericFilePreview(file) {
                    const icon = file.type.includes('pdf') ? 'bi-file-pdf' : 'bi-file-earmark';
                    filePreview.innerHTML = `
            <div class="text-center py-3">
                <i class="bi ${icon} display-4 text-muted"></i>
                <p class="mt-2 mb-0 font-weight-medium">${file.name}</p>
                <p class="small text-muted">${formatFileSize(file.size)}</p>
            </div>
        `;
                }

                // Utility function to format file size
                function formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }

                // Name input change handler
                nameInput?.addEventListener('input', updateSubmitButton);

                // Form submission handling
                attachmentForm?.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Disable submit button and show loading state
                    submitBtn.disabled = true;
                    const originalContent = submitBtn.innerHTML;
                    submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Uploading...
        `;

                    // Submit the form
                    this.submit();
                });

                // Delete confirmation
                window.confirmDelete = function() {
                    return new bootstrap.Modal(document.createElement('div'), {
                        backdrop: 'static',
                        keyboard: false
                    }).show();
                };

                // Bootstrap Tooltip Initialization
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Handle hash navigation
                const hash = window.location.hash;
                if (hash) {
                    const triggerEl = document.querySelector(`button[data-bs-target="${hash}"]`);
                    if (triggerEl) {
                        new bootstrap.Tab(triggerEl).show();
                    }
                }
            });
        </script>
@endsection

