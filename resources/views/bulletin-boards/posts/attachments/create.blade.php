@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Ajouter une pièce jointe</h1>
            <p class="text-muted">
                Publication :
                <a href="{{ route('bulletin-boards.posts.show', [$bulletinBoard, $post]) }}">{{ $post->name }}</a>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('bulletin-boards.posts.attachments.index', [$bulletinBoard, $post]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Formulaire d'ajout</div>
                <div class="card-body">
                    <form action="{{ route('bulletin-boards.posts.attachments.store', [$bulletinBoard, $post]) }}" method="POST" enctype="multipart/form-data" id="attachment-form">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de la pièce jointe <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Fichier <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required>
                            <div class="form-text">
                                Formats acceptés: PDF, JPG, JPEG, PNG, GIF, MP4, AVI, MOV, DOC, DOCX, XLS, XLSX.<br>
                                Taille maximale: 20 MB.
                            </div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="preview-container" style="display: none;">
                            <label class="form-label">Aperçu</label>
                            <div class="border p-2 bg-light text-center">
                                <img id="image-preview" src="#" alt="Aperçu de l'image" style="max-width: 100%; max-height: 300px;">
                                <input type="hidden" name="thumbnail" id="thumbnail-data">
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Ajouter la pièce jointe</button>
                            <a href="{{ route('bulletin-boards.posts.attachments.index', [$bulletinBoard, $post]) }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Aide</div>
                <div class="card-body">
                    <h5>Types de fichiers acceptés</h5>
                    <ul>
                        <li><strong>Images:</strong> JPG, JPEG, PNG, GIF</li>
                        <li><strong>Documents:</strong> PDF, DOC, DOCX, XLS, XLSX</li>
                        <li><strong>Vidéos:</strong> MP4, AVI, MOV</li>
                    </ul>
                    <hr>
                    <h5>Conseils</h5>
                    <ul>
                        <li>Donnez un nom descriptif à votre pièce jointe pour faciliter l'identification</li>
                        <li>Vérifiez la taille du fichier avant de l'uploader (max 20MB)</li>
                        <li>Pour les images, une vignette sera automatiquement générée</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('file');
        const previewContainer = document.getElementById('preview-container');
        const imagePreview = document.getElementById('image-preview');
        const thumbnailData = document.getElementById('thumbnail-data');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) {
                previewContainer.style.display = 'none';
                return;
            }

            if (file.type.match('image.*')) {
                previewContainer.style.display = 'block';

                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;

                    // Création d'une vignette pour les images
                    const img = new Image();
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');

                        // Dimensions pour la vignette
                        const MAX_WIDTH = 300;
                        const MAX_HEIGHT = 300;

                        let width = img.width;
                        let height = img.height;

                        if (width > height) {
                            if (width > MAX_WIDTH) {
                                height *= MAX_WIDTH / width;
                                width = MAX_WIDTH;
                            }
                        } else {
                            if (height > MAX_HEIGHT) {
                                width *= MAX_HEIGHT / height;
                                height = MAX_HEIGHT;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;

                        ctx.drawImage(img, 0, 0, width, height);

                        // Convertir en base64 pour l'envoi
                        const dataUrl = canvas.toDataURL('image/jpeg', 0.7);
                        thumbnailData.value = dataUrl;
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });
    });
</script>
@endsection
