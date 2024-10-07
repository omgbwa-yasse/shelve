@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="">
                <div class="">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">{{ $mail->name }}</h2>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="mailTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab">Details</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="attachments-tab" data-toggle="tab" href="#attachments" role="tab">Attachments</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="transactions-tab" data-toggle="tab" href="#transactions" role="tab">Transaction History</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-3" id="mailTabsContent">
                            <div class="tab-pane fade show active" id="details" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                                        <p><strong>ID:</strong> {{ $mail->id }}</p>
                                        <p><strong>Code:</strong> {{ $mail->code }}</p>
                                        <p><strong>Date:</strong> {{ $mail->date }}</p>
                                        <p><strong>Author(s):</strong>
                                            @foreach($mail->authors as $author)
                                                <span class="badge badge-info">{{ $author->name }}</span>
                                            @endforeach
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2 mb-3">Classification</h5>
                                        <p><strong>Priority:</strong> <span class="badge badge-{{ $mail->priority ? 'warning' : 'secondary' }}">{{ $mail->priority ? $mail->priority->name : 'N/A' }}</span></p>
                                        <p><strong>Mail Type:</strong> {{ $mail->type ? $mail->type->name : 'N/A' }}</p>
                                        <p><strong>Business Type:</strong> {{ $mail->typology ? $mail->typology->name : 'N/A' }}</p>
                                        <p><strong>Nature:</strong> {{ $mail->documentType ? $mail->documentType->name : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h5 class="border-bottom pb-2 mb-3">Description</h5>
                                    <p>{{ $mail->description }}</p>
                                </div>
                                <div class="mt-4">
                                    <button class="btn btn-secondary" onclick="window.history.back()">
                                        <i class="bi bi-arrow-left"></i> Back
                                    </button>
                                    <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button class="btn btn-danger" onclick="confirmDelete()">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                    <a href="{{ route('mail-attachment.create', ['file' => $mail]) }}" class="btn btn-info">
                                        <i class="bi bi-paperclip"></i> Add Attachment
                                    </a>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="attachments" role="tabpanel">
                                <h5 class="border-bottom pb-2 mb-3">Pièces jointes</h5>
                                @if($mail->attachments->isNotEmpty())
                                    <div class="row" id="file-list">
                                        @foreach($mail->attachments as $attachment)
                                            <div class="col-md-4 col-lg-3 mb-4">
                                                <div class="card h-100 shadow-sm">
                                                    <div class="card-img-top" style="height: 200px; overflow: hidden;">
                                                        @if ($attachment->thumbnail_path)
                                                            <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}" alt="Vignette" class="img-fluid" style="width: 100%; object-fit: cover; object-position: top;">
                                                        @else
                                                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                                <i class="bi bi-file-earmark-pdf fs-1 text-secondary"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="card-body p-3">
                                                        <h6 class="card-title text-truncate mb-1" title="{{ $attachment->name }}">{{ $attachment->name }}</h6>
                                                        <p class="card-text small text-muted mb-2">
                                                            {{ number_format($attachment->size / 1024, 2) }} KB
                                                        </p>
                                                        <p class="card-text small text-muted mb-3">
                                                            Ajouté par: {{ $attachment->creator->name ?? 'N/A' }}
                                                        </p>
                                                        <a href="{{ route('mail-attachment.show', [$mail->id, $attachment->id]) }}" class="btn btn-outline-primary btn-sm w-100" target="_blank">
                                                            <i class="bi bi-download me-1"></i> Télécharger
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">Aucune pièce jointe trouvée.</div>
                                @endif

                                <div class="mt-4">
                                    <h5 class="border-bottom pb-2 mb-3">Ajouter une pièce jointe</h5>
                                    <form action="{{ route('mail-attachment.store', $mail->id) }}" method="POST" enctype="multipart/form-data" id="attachmentForm">
                                        @csrf
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="name" class="form-label">Nom du fichier</label>
                                                <input type="text" class="form-control" id="name" name="name" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="file" class="form-label">Fichier</label>
                                                <input class="form-control" type="file" id="file" name="file" accept="application/pdf" required>
                                            </div>
                                        </div>
                                        <div id="pdf-preview" class="mb-3" style="display: none;">
                                            <canvas id="pdf-canvas" style="max-width: 100%; height: auto;"></canvas>
                                        </div>
                                        <input type="hidden" name="thumbnail" id="thumbnailInput">
                                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                            <i class="bi bi-plus-circle me-1"></i> Ajouter la pièce jointe
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="transactions" role="tabpanel">
                                <h5 class="border-bottom pb-2 mb-3">Transaction History</h5>
                                @if($mail->transactions->isNotEmpty())
                                    <div class="timeline">
                                        @foreach($mail->transactions as $transaction)
                                            <div class="timeline-item">
                                                <div class="timeline-badge bg-primary"></div>
                                                <div class="timeline-panel card">
                                                    <div class="card-body">
                                                        <h6 class="card-title">{{ $transaction->code }}</h6>
                                                        <p class="card-text">
                                                            <strong>Created:</strong> {{ $transaction->date_creation }}<br>
                                                            <strong>Sender:</strong> {{ $transaction->organisationSend ? $transaction->organisationSend->name : 'N/A' }} ({{ $transaction->userSend ? $transaction->userSend->name : 'N/A' }})<br>
                                                            <strong>Recipient:</strong> {{ $transaction->organisationReceived ? $transaction->organisationReceived->name : 'N/A' }} ({{ $transaction->userReceived ? $transaction->userReceived->name : 'N/A' }})<br>
                                                            <strong>Mail Type:</strong> {{ $transaction->type ? $transaction->type->name : 'N/A' }}<br>
                                                            <strong>Document Type:</strong> {{ $transaction->documentType ? $transaction->documentType->name : 'N/A' }}
                                                        </p>
                                                        <small class="text-muted">
                                                            Created: {{ $transaction->created_at->format('M d, Y H:i') }}<br>
                                                            Updated: {{ $transaction->updated_at->format('M d, Y H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">No transactions found.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('file');
            const nameInput = document.getElementById('name');
            const submitBtn = document.getElementById('submitBtn');
            const pdfPreview = document.getElementById('pdf-preview');
            const pdfCanvas = document.getElementById('pdf-canvas');
            const ctx = pdfCanvas.getContext('2d');

            function updateSubmitButton() {
                submitBtn.disabled = !(fileInput.files.length > 0 && nameInput.value.trim() !== '');
            }

            fileInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file && file.type === 'application/pdf') {
                    const fileReader = new FileReader();
                    fileReader.onload = function() {
                        const pdfData = new Uint8Array(this.result);
                        renderPdfPreview(pdfData);
                        generatePdfThumbnail(pdfData);
                    };
                    fileReader.readAsArrayBuffer(file);
                    pdfPreview.style.display = 'block';
                } else {
                    pdfPreview.style.display = 'none';
                    alert('Veuillez sélectionner un fichier PDF.');
                }
                updateSubmitButton();
            });

            nameInput.addEventListener('input', updateSubmitButton);

            function renderPdfPreview(pdfData) {
                pdfjsLib.getDocument({data: pdfData}).promise.then(function(pdf) {
                    pdf.getPage(1).then(function(page) {
                        const viewport = page.getViewport({scale: 1});
                        const scale = pdfCanvas.offsetWidth / viewport.width;
                        const scaledViewport = page.getViewport({scale: scale});

                        pdfCanvas.height = scaledViewport.height;
                        pdfCanvas.width = scaledViewport.width;

                        page.render({
                            canvasContext: ctx,
                            viewport: scaledViewport
                        });
                    });
                });
            }

            function generatePdfThumbnail(pdfData) {
                pdfjsLib.getDocument({data: pdfData}).promise.then(function(pdf) {
                    pdf.getPage(1).then(function(page) {
                        const scale = 1.5;
                        const viewport = page.getViewport({scale: scale});
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };
                        page.render(renderContext).promise.then(() => {
                            const thumbnailDataUrl = canvas.toDataURL('image/jpeg');
                            document.getElementById('thumbnailInput').value = thumbnailDataUrl;
                        });
                    });
                });
            }

            document.getElementById('attachmentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi en cours...';
                this.submit();
            });
        });
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this mail?')) {
                document.getElementById('delete-form').submit();
            }
        }

        $(document).ready(function() {
            $('#mailTabs a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
        });
    </script>
@endpush
