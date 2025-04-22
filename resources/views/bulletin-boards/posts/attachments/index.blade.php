@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Pièces jointes</h1>
            <p class="text-muted">
                Publication :
                <a href="{{ route('bulletin-boards.posts.show', [$bulletinBoard, $post]) }}">{{ $post->name }}</a>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('bulletin-boards.posts.attachments.create', [$bulletinBoard, $post]) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une pièce jointe
            </a>
            <a href="{{ route('bulletin-boards.posts.show', [$bulletinBoard, $post]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Liste des pièces jointes ({{ $attachments->count() }})</div>
        <div class="card-body">
            @if($attachments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 80px">Aperçu</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Taille</th>
                                <th>Ajouté par</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attachments as $attachment)
                                <tr>
                                    <td>
                                        @if($attachment->thumbnail_path)
                                            <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}" alt="Miniature" class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                                        @else
                                            <i class="fas fa-file fa-2x text-secondary"></i>
                                        @endif
                                    </td>
                                    <td>{{ $attachment->name }}</td>
                                    <td>{{ $attachment->mime_type ?? 'Non défini' }}</td>
                                    <td>{{ human_filesize($attachment->size) }}</td>
                                    <td>{{ $attachment->creator->name ?? 'Inconnu' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('attachments.preview', $attachment) }}" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $attachment->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center p-4">
                    <p>Aucune pièce jointe trouvée.</p>
                    <a href="{{ route('bulletin-boards.posts.attachments.create', [$bulletinBoard, $post]) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter une pièce jointe
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($attachments as $attachment)
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal{{ $attachment->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $attachment->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel{{ $attachment->id }}">Confirmer la suppression</h5>
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
@endforeach
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
