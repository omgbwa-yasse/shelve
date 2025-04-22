<ul class="list-group">
    @forelse($attachments as $attachment)
        <li class="list-group-item d-flex justify-content-between align-items-center attachment-item" data-attachment-id="{{ $attachment->id }}">
            <div class="d-flex align-items-center">
                @if($attachment->thumbnail_path)
                    <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}" alt="Miniature" class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                @elseif(Str::startsWith($attachment->mime_type, 'image/'))
                    <i class="fas fa-image fa-2x me-3 text-primary"></i>
                @elseif(Str::startsWith($attachment->mime_type, 'video/'))
                    <i class="fas fa-video fa-2x me-3 text-danger"></i>
                @elseif(Str::startsWith($attachment->mime_type, 'application/pdf'))
                    <i class="fas fa-file-pdf fa-2x me-3 text-danger"></i>
                @elseif(Str::contains($attachment->mime_type, 'word'))
                    <i class="fas fa-file-word fa-2x me-3 text-primary"></i>
                @elseif(Str::contains($attachment->mime_type, 'excel') || Str::contains($attachment->mime_type, 'spreadsheet'))
                    <i class="fas fa-file-excel fa-2x me-3 text-success"></i>
                @else
                    <i class="fas fa-file fa-2x me-3 text-secondary"></i>
                @endif
                <div>
                    <div class="fw-bold">{{ $attachment->name }}</div>
                    <small class="text-muted">{{ human_filesize($attachment->size) }} · Ajouté le {{ $attachment->created_at->format('d/m/Y') }}</small>
                </div>
            </div>
            <div class="btn-group">
                <a href="{{ route('attachments.preview', $attachment) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Aperçu">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-sm btn-outline-primary" title="Télécharger">
                    <i class="fas fa-download"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger delete-attachment-btn"
                        data-attachment-id="{{ $attachment->id }}"
                        data-attachment-name="{{ $attachment->name }}"
                        title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </li>
    @empty
        <li class="list-group-item text-center">
            <p class="mb-0">Aucune pièce jointe</p>
        </li>
    @endforelse
</ul>
