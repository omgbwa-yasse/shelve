@if($attachments->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Fichier</th>
                    <th>Type</th>
                    <th>Taille</th>
                    <th>Ajouté par</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attachments as $attachment)
                    <tr id="attachment-{{ $attachment->id }}" class="attachment-row">
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    {{ $attachment->name }}
                                </div>
                            </div>
                        </td>
                        <td>{{ Str::upper(pathinfo($attachment->name, PATHINFO_EXTENSION)) }}</td>
                        <td>{{ number_format($attachment->size / 1024, 2) }} KB</td>
                        <td>{{ $attachment->creator->name }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-outline-primary" target="_blank">
                                    <i class="fas fa-download"></i> Télécharger
                                </a>
                                <a href="{{ route('attachments.preview', $attachment->id) }}" class="btn btn-outline-info" target="_blank">
                                    <i class="fas fa-eye"></i> Lire
                                </a>
                                @if($event->canBeEditedBy(Auth::user()))
                                    <button type="button" class="btn btn-outline-danger delete-attachment" data-attachment-id="{{ $attachment->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i> Aucune pièce jointe n'est associée à cet événement.
        @if($event->canBeEditedBy(Auth::user()))
            <button type="button" class="btn btn-link p-0 alert-link" data-bs-toggle="modal" data-bs-target="#addAttachmentModal">
                Ajouter des pièces jointes
            </button>
        @endif
    </div>
@endif
