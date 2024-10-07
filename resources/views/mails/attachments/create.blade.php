@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Ajouter une pièce jointe au mail #{{ $mail->id }}</h1>
        <div class="card">
            <div class="card-body">
                <form id="mailAttachmentForm" action="{{ route('mail-attachment.store', $mail->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">Fichier PDF</label>
                        <input type="file" class="form-control" id="file" name="file" accept="application/pdf" required>
                    </div>
                    <div id="pdf-preview" class="mb-3">
                        <canvas id="pdf-canvas" style="max-width: 100%; height: auto;"></canvas>
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
                } else {
                    pdfCanvas.style.display = 'none';
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
                        pdfCanvas.style.display = 'block';

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

            document.getElementById('mailAttachmentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi en cours...';
                this.submit();
            });
        });
    </script>
@endsection
