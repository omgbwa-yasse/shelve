{{-- Gestion Signature Électronique --}}
<div class="card mb-3">
    <div class="card-header bg-success text-white">
        <i class="bi bi-patch-check"></i> {{ __('electronic_signature') }}
    </div>
    <div class="card-body">
        @if(!$document->is_current_version)
            <div class="alert alert-warning mb-0">
                <small><i class="bi bi-info-circle"></i> {{ __('only_current_version_can_be_signed') }}</small>
            </div>
        @elseif($document->isCheckedOut())
            <div class="alert alert-warning mb-0">
                <small><i class="bi bi-exclamation-triangle"></i> {{ __('cannot_sign_checked_out_document') }}</small>
            </div>
        @elseif($document->signature_status === 'unsigned')
            {{-- État 1: Non signé --}}
            <span class="badge bg-secondary mb-2">
                <i class="bi bi-file-earmark"></i> {{ __('unsigned') }}
            </span>
            <button type="button" class="btn btn-success btn-sm w-100"
                    data-bs-toggle="modal" data-bs-target="#signModal">
                <i class="bi bi-patch-check"></i> {{ __('sign_electronically') }}
            </button>
            <small class="text-muted mt-2 d-block">
                {{ __('signature_guarantee_integrity') }}
            </small>
        @elseif($document->signature_status === 'signed')
            {{-- État 2: Signé --}}
            <span class="badge bg-success mb-2">
                <i class="bi bi-check-circle"></i> {{ __('signed') }}
            </span>
            <p class="small mb-2">
                <strong>{{ __('signed_by') }}:</strong> {{ $document->signer->name ?? 'N/A' }}<br>
                <strong>{{ __('signed_at_date') }}:</strong> {{ $document->signed_at ? $document->signed_at->format('d/m/Y à H:i') : 'N/A' }}<br>
                @if($document->signature_data)
                    <strong>{{ __('reason') }}:</strong> {{ $document->signature_data }}<br>
                @endif
                <strong>{{ __('hash') }}:</strong> <code class="small">{{ Str::limit($document->signature_hash, 20) }}...</code>
            </p>

            {{-- Bouton Verify --}}
            <form action="{{ route('documents.verify-signature', $document) }}" method="POST" class="mb-2">
                @csrf
                <button type="submit" class="btn btn-info btn-sm w-100">
                    <i class="bi bi-shield-check"></i> {{ __('verify_signature') }}
                </button>
            </form>

            {{-- Bouton Revoke (si signataire) --}}
            @if($document->signed_by === Auth::id())
                <button type="button" class="btn btn-outline-danger btn-sm w-100"
                        data-bs-toggle="modal" data-bs-target="#revokeModal">
                    <i class="bi bi-x-circle"></i> {{ __('revoke_my_signature') }}
                </button>
            @endif
        @elseif($document->signature_status === 'revoked')
            {{-- État 3: Révoquée --}}
            <span class="badge bg-danger mb-2">
                <i class="bi bi-exclamation-triangle"></i> {{ __('revoked') }}
            </span>
            <p class="small mb-0">
                <strong>{{ __('signed_at_date') }}:</strong> {{ $document->signature_revoked_at ? $document->signature_revoked_at->format('d/m/Y à H:i') : 'N/A' }}<br>
                <strong>{{ __('reason') }}:</strong> {{ $document->signature_revocation_reason }}
            </p>
        @endif
    </div>
</div>

{{-- Inclure les modales si applicable --}}
@if($document->signature_status === 'unsigned' && $document->is_current_version && !$document->isCheckedOut())
    @include('repositories.documents.modals.sign-modal')
@endif

@if($document->signature_status === 'signed' && $document->signed_by === Auth::id())
    {{-- TODO: Create revoke-modal if needed --}}
@endif

@if($document->signature_status === 'signed' && $document->signed_by === Auth::id())
    @include('repositories.documents.modals.revoke-modal')
@endif
