@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>{{ $attachment->name }}</h1>
            <p class="text-muted">
                Publication :
                <a href="{{ route('bulletin-boards.posts.show', [$bulletinBoard, $post]) }}">{{ $post->name }}</a>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-primary">
                    <i class="fas fa-download"></i> Télécharger
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
                <a href="{{ route('bulletin-boards.posts.attachments.index', [$bulletinBoard, $post]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Détails de la pièce jointe</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nom:</strong> {{ $attachment->name }}</p>
                            <p><strong>Type de fichier:</strong> {{ $attachment->mime_type ?? 'Non défini' }}</p>
                            <p><strong>Taille:</strong> {{ human_filesize($attachment->size) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Ajouté par:</strong> {{ $attachment->creator->name ?? 'Inconnu' }}</p>
                            <p><strong>Date d'ajout:</strong> {{ $attachment->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Dernière modification:</strong> {{ $attachment->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Aperçu</h5>
                        <div class="border p-3 bg-light text-center">
                            @if(Str::startsWith($attachment->mime_type, 'image/'))
                                <img src="{{ route('attachments.preview', $attachment) }}" alt="{{ $attachment->name }}" class="img-fluid" style="max-height: 400px;">
                            @elseif(Str::startsWith($attachment->mime_type, 'video/'))
                                <video controls class="w-100" style="max-height: 400px;">
                                    <source src="{{ route('attachments.preview', $attachment) }}" type="{{ $attachment->mime_type }}">
                                    Votre navigateur ne prend pas en charge la lecture de vidéos.
                                </video>
                            @elseif(Str::startsWith($attachment->mime_type, 'application/pdf'))
                                <div class="ratio ratio-16x9" style="height: 500px;">
                                    <iframe src="{{ route('attachments.preview', $attachment) }}" allowfullscreen></iframe>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    L'aperçu n'est pas disponible pour ce type de fichier.
                                    <a href="{{ route('attachments.download', $attachment) }}" class="alert-link">Téléchargez le fichier</a> pour le consulter.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i> Télécharger
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-2"></i> Supprimer
                        </button>
                        <a href="{{ route('bulletin-boards.posts.show', [$bulletinBoard, $post]) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-eye me-2"></i> Voir la publication
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer la pièce jointe "{{ $attachment->name }}" ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('bulletin-boards.posts.attachments.destroy', [$bulletinBoard, $post, $attachment]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function human_filesize(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
</script>
@endsection
