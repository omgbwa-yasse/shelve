@if($document->attachment)
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Vignette du document</h5>
    </div>
    <div class="card-body">
        <div class="text-center">
            <img src="{{ $document->attachment->getThumbnailUrl() }}"
                 alt="{{ $document->name }}"
                 class="img-fluid rounded"
                 style="max-height: 300px; object-fit: contain;">
            <p class="mt-2 mb-0">
                <small class="text-muted">
                    {{ $document->attachment->name }}
                    ({{ $document->attachment->file_size_human }})
                </small>
            </p>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Fichier</h5>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-6">Type</dt>
            <dd class="col-sm-6">
                <code>{{ $document->attachment->file_extension ?? 'N/A' }}</code>
            </dd>

            <dt class="col-sm-6">Format</dt>
            <dd class="col-sm-6">
                <small>{{ $document->attachment->mime_type ?? 'N/A' }}</small>
            </dd>

            <dt class="col-sm-6">Taille</dt>
            <dd class="col-sm-6">
                <strong>{{ $document->attachment->file_size_human }}</strong>
            </dd>

            <dt class="col-sm-6">Uploadé par</dt>
            <dd class="col-sm-6">
                {{ $document->attachment->creator->name ?? 'N/A' }}
            </dd>

            <dt class="col-sm-6">Date upload</dt>
            <dd class="col-sm-6">
                <small>{{ $document->attachment->created_at->format('d/m/Y H:i') }}</small>
            </dd>
        </dl>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <a href="{{ route('attachments.download', $document->attachment) }}" class="btn btn-primary w-100">
            <i class="bi bi-download"></i> Télécharger
        </a>
    </div>
</div>
@else
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Fichier</h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">Aucun fichier attaché à ce document.</p>
    </div>
</div>
@endif
