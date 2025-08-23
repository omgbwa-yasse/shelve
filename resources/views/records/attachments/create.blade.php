@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Ajouter une pièce jointe à l'enregistrement #{{ $record->id }}</h1>
        
        <!-- Messages d'erreur et de succès -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6><i class="bi bi-exclamation-triangle"></i> Erreurs de validation :</h6>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                @if(str_contains(session('error'), 'PostTooLargeException') || str_contains(session('error'), 'POST data is too large'))
                    <br><br>
                    <strong>Problème de configuration serveur :</strong><br>
                    La taille maximum d'upload est trop petite sur ce serveur.<br>
                    <a href="{{ route('upload.diagnostics') }}" target="_blank" class="btn btn-sm btn-outline-light mt-2">
                        <i class="bi bi-gear"></i> Voir diagnostics
                    </a>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <!-- Alert pour les messages d'erreur AJAX -->
        <div id="ajaxAlert" class="alert alert-danger alert-dismissible fade d-none" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <span id="ajaxErrorMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form id="attachmentForm" action="{{ route('records.attachments.store', $record->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">Fichier <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept="application/pdf,image/*,video/*" required>
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> 
                            Formats acceptés : PDF, images (JPG, PNG, GIF), vidéos (MP4, AVI, MOV). Taille max : 100 MB
                        </div>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div id="file-preview" class="mb-3">
                        <!-- Preview will be inserted here -->
                    </div>
                    <input type="hidden" name="thumbnail" id="thumbnailInput">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('records.attachments.index', $record->id) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="bi bi-plus-circle"></i> Ajouter la pièce jointe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('file');
            const nameInput = document.getElementById('name');
            const submitBtn = document.getElementById('submitBtn');
            const filePreview = document.getElementById('file-preview');

            function updateSubmitButton() {
                submitBtn.disabled = !(fileInput.files.length > 0 && nameInput.value.trim() !== '');
            }

            fileInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const fileType = file.type.split('/')[0];
                    const fileReader = new FileReader();

                    fileReader.onload = function(e) {
                        switch(fileType) {
                            case 'application':
                                if (file.type === 'application/pdf') {
                                    renderPdfPreview(new Uint8Array(e.target.result));
                                } else {
                                    filePreview.innerHTML = '<p>Aperçu non disponible pour ce type de fichier.</p>';
                                }
                                break;
                            case 'image':
                                filePreview.innerHTML = `<img src="${e.target.result}" alt="Image preview" style="max-width: 100%; max-height: 300px;">`;
                                generateImageThumbnail(e.target.result);
                                break;
                            case 'video':
                                filePreview.innerHTML = `<video src="${e.target.result}" controls style="max-width: 100%; max-height: 300px;"></video>`;
                                generateVideoThumbnail(file);
                                break;
                            default:
                                filePreview.innerHTML = '<p>Aperçu non disponible pour ce type de fichier.</p>';
                        }
                    };

                    if (fileType === 'application' && file.type === 'application/pdf') {
                        fileReader.readAsArrayBuffer(file);
                    } else {
                        fileReader.readAsDataURL(file);
                    }
                } else {
                    filePreview.innerHTML = '';
                }
                updateSubmitButton();
            });

            nameInput.addEventListener('input', updateSubmitButton);

            function renderPdfPreview(pdfData) {
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
                            filePreview.innerHTML = '';
                            filePreview.appendChild(canvas);
                            generatePdfThumbnail(canvas);
                        });
                    });
                });
            }

            function generatePdfThumbnail(canvas) {
                const thumbnailDataUrl = canvas.toDataURL('image/jpeg');
                document.getElementById('thumbnailInput').value = thumbnailDataUrl;
            }

            function generateImageThumbnail(imageDataUrl) {
                document.getElementById('thumbnailInput').value = imageDataUrl;
            }

            function generateVideoThumbnail(videoFile) {
                const video = document.createElement('video');
                video.preload = 'metadata';
                video.onloadedmetadata = function() {
                    video.currentTime = 1;
                };
                video.onseeked = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                    const thumbnailDataUrl = canvas.toDataURL('image/jpeg');
                    document.getElementById('thumbnailInput').value = thumbnailDataUrl;
                };
                video.src = URL.createObjectURL(videoFile);
            }

            document.getElementById('attachmentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validation côté client
                const file = fileInput.files[0];
                const name = nameInput.value.trim();
                
                if (!file) {
                    showError('Veuillez sélectionner un fichier.');
                    return;
                }
                
                if (!name) {
                    showError('Veuillez saisir un nom pour la pièce jointe.');
                    return;
                }
                
                // Vérification de la taille (100 MB max)
                if (file.size > 100 * 1024 * 1024) {
                    showError('Le fichier ne peut pas dépasser 100 MB.');
                    return;
                }
                
                // Désactiver le bouton et changer le texte
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi en cours...';
                
                // Soumettre le formulaire
                this.submit();
            });
            
            function showError(message) {
                const ajaxAlert = document.getElementById('ajaxAlert');
                const ajaxErrorMessage = document.getElementById('ajaxErrorMessage');
                ajaxErrorMessage.textContent = message;
                ajaxAlert.classList.remove('d-none');
                ajaxAlert.classList.add('show');
                
                // Scroll vers le haut pour voir l'erreur
                ajaxAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
@endsection
