@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Ajouter une pièce jointe à l'enregistrement #{{ $record->id }}</h1>
        <div class="card">
            <div class="card-body">
                <form id="attachmentForm" action="{{ route('records.attachments.store', $record->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">Fichier (PDF, Image, ou Vidéo)</label>
                        <input type="file" class="form-control" id="file" name="file" accept="application/pdf,image/*,video/*" required>
                    </div>
                    <div id="file-preview" class="mb-3">
                        <!-- Preview will be inserted here -->
                    </div>
                    <input type="hidden" name="thumbnail" id="thumbnailInput">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="bi bi-plus-circle"></i> Ajouter la pièce jointe
                    </button>
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
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi en cours...';
                this.submit();
            });
        });
    </script>
@endsection
