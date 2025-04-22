@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>{{ $event->name }}</h1>
            <p class="text-muted">
                Tableau d'affichage:
                <a href="{{ route('bulletin-boards.show', $bulletinBoard) }}">{{ $bulletinBoard->name }}</a>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <a href="{{ route('bulletin-boards.events.edit', [$bulletinBoard, $event]) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Modifier
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
                <a href="{{ route('bulletin-boards.events.index', $bulletinBoard) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        Détails de l'événement
                        @if($event->status == 'published')
                            <span class="badge bg-success ms-2">Publié</span>
                        @elseif($event->status == 'draft')
                            <span class="badge bg-warning ms-2">Brouillon</span>
                        @elseif($event->status == 'cancelled')
                            <span class="badge bg-danger ms-2">Annulé</span>
                        @endif
                    </div>
                    <div>
                        <form action="{{ route('bulletin-boards.events.update-status', [$bulletinBoard, $event]) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="{{ $event->status == 'published' ? 'draft' : 'published' }}">
                            <button type="submit" class="btn btn-sm {{ $event->status == 'published' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                {{ $event->status == 'published' ? 'Passer en brouillon' : 'Publier' }}
                            </button>
                        </form>
                        @if($event->status != 'cancelled')
                            <form action="{{ route('bulletin-boards.events.update-status', [$bulletinBoard, $event]) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Annuler</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Date de début:</strong> {{ $event->start_date->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date de fin:</strong> {{ $event->end_date ? $event->end_date->format('d/m/Y H:i') : 'Non définie' }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Lieu:</strong> {{ $event->location ?: 'Non défini' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Créé par:</strong> {{ $event->creator->name }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Description</h5>
                            <div class="border rounded p-3 bg-light">
                                {!! $event->description !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            @if($event->isUpcoming())
                                <form action="{{ route('bulletin-boards.events.register', [$bulletinBoard, $event]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-user-plus"></i> S'inscrire
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="text-muted">
                                Créé le {{ $event->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Pièces jointes ({{ $event->attachments->count() }})</span>
                    <a href="{{ route('bulletin-boards.events.attachments.create', [$bulletinBoard, $event]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Ajouter
                    </a>
                </div>
                <div class="card-body" id="attachments-container">
                    @if($event->attachments->count() > 0)
                        <ul class="list-group">
                            @foreach($event->attachments as $attachment)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($attachment->thumbnail_path)
                                            <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}" alt="Vignette" width="40" class="me-2">
                                        @else
                                            <i class="fas fa-file me-2"></i>
                                        @endif
                                        <a href="{{ route('attachments.preview', $attachment) }}" target="_blank">{{ $attachment->name }}</a>
                                        <small class="text-muted d-block">{{$attachment->size }}</small>
                                    </div>
                                    <div class="btn-group">
                                        <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-attachment" data-attachment-id="{{ $attachment->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center">Aucune pièce jointe</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Event Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer l'événement "{{ $event->name }}" ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('bulletin-boards.events.destroy', [$bulletinBoard, $event]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Attachment Modal -->
<div class="modal fade" id="deleteAttachmentModal" tabindex="-1" aria-labelledby="deleteAttachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAttachmentModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer cette pièce jointe ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="delete-attachment-form" action="" method="POST">
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
    // Fonction helper pour formater la taille de fichier
    function human_filesize(bytes, si=false, dp=1) {
        const thresh = si ? 1000 : 1024;
        if (Math.abs(bytes) < thresh) {
            return bytes + ' B';
        }
        const units = si
            ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
            : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        let u = -1;
        const r = 10**dp;
        do {
            bytes /= thresh;
            ++u;
        } while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);
        return bytes.toFixed(dp) + ' ' + units[u];
    }

    // Gestion de la suppression des pièces jointes
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-attachment').forEach(button => {
            button.addEventListener('click', function() {
                const attachmentId = this.getAttribute('data-attachment-id');
                const form = document.getElementById('delete-attachment-form');
                form.action = `{{ route('bulletin-boards.events.attachments.destroy', [$bulletinBoard, $event, ':attachment']) }}`.replace(':attachment', attachmentId);

                const modal = new bootstrap.Modal(document.getElementById('deleteAttachmentModal'));
                modal.show();
            });
        });
    });
</script>
@endsection
