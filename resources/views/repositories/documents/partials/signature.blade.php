{{-- Gestion Signature Électronique --}}
<div class="card mb-3">
    <div class="card-header bg-success text-white">
        <i class="fas fa-signature"></i> Signature Électronique
    </div>
    <div class="card-body">
        @if(!$document->is_current_version)
            <div class="alert alert-warning mb-0">
                <small><i class="fas fa-info-circle"></i> Seule la version courante peut être signée.</small>
            </div>
        @elseif($document->isCheckedOut())
            <div class="alert alert-warning mb-0">
                <small><i class="fas fa-exclamation-triangle"></i> Impossible de signer un document réservé.</small>
            </div>
        @elseif($document->signature_status === 'unsigned')
            {{-- État 1: Non signé --}}
            <span class="badge badge-secondary mb-2">
                <i class="fas fa-file"></i> Non signé
            </span>
            <button type="button" class="btn btn-success btn-sm btn-block"
                    data-toggle="modal" data-target="#signModal">
                <i class="fas fa-signature"></i> Signer électroniquement
            </button>
            <small class="text-muted mt-2 d-block">
                La signature garantit l'authenticité et l'intégrité du document.
            </small>
        @elseif($document->signature_status === 'signed')
            {{-- État 2: Signé --}}
            <span class="badge badge-success mb-2">
                <i class="fas fa-check-circle"></i> Document signé
            </span>
            <p class="small mb-2">
                <strong>Par:</strong> {{ $document->signer->name }}<br>
                <strong>Le:</strong> {{ $document->signed_at->format('d/m/Y à H:i') }}<br>
                @if($document->signature_data)
                    <strong>Raison:</strong> {{ $document->signature_data }}<br>
                @endif
                <strong>Hash:</strong> <code class="small">{{ Str::limit($document->signature_hash, 20) }}...</code>
            </p>

            {{-- Bouton Verify --}}
            <form action="{{ route('documents.verify-signature', $document) }}" method="POST" class="mb-2">
                @csrf
                <button type="submit" class="btn btn-info btn-sm btn-block">
                    <i class="fas fa-shield-alt"></i> Vérifier la signature
                </button>
            </form>

            {{-- Bouton Revoke (si signataire) --}}
            @if($document->signed_by === Auth::id())
                <button type="button" class="btn btn-outline-danger btn-sm btn-block"
                        data-toggle="modal" data-target="#revokeModal">
                    <i class="fas fa-ban"></i> Révoquer ma signature
                </button>
            @endif
        @elseif($document->signature_status === 'revoked')
            {{-- État 3: Révoquée --}}
            <span class="badge badge-danger mb-2">
                <i class="fas fa-exclamation-triangle"></i> Signature révoquée
            </span>
            <p class="small mb-0">
                <strong>Le:</strong> {{ $document->signature_revoked_at->format('d/m/Y à H:i') }}<br>
                <strong>Raison:</strong> {{ $document->signature_revocation_reason }}
            </p>
        @endif
    </div>
</div>

{{-- Inclure les modales si applicable --}}
@if($document->signature_status === 'unsigned' && $document->is_current_version && !$document->isCheckedOut())
    @include('repositories.documents.modals.sign-modal')
@endif

@if($document->signature_status === 'signed' && $document->signed_by === Auth::id())
    @include('repositories.documents.modals.revoke-modal')
@endif
