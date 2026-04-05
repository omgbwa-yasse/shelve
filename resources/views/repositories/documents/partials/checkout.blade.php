{{-- Gestion Réservation Document --}}
<div class="card mb-3">
    <div class="card-header bg-info text-white">
        <i class="bi bi-lock"></i> Réservation Document
    </div>
    <div class="card-body">
        @if(!$document->is_current_version)
            <div class="alert alert-warning mb-0">
                <small><i class="bi bi-info-circle"></i> Seule la version courante peut être réservée.</small>
            </div>
        @elseif(!$document->isCheckedOut())
            {{-- État 1: Document libre --}}
            <span class="badge bg-success mb-2">
                <i class="bi bi-unlock"></i> Disponible
            </span>
            <form action="{{ route('documents.checkout', $document) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-lock-fill"></i> Réserver le document
                </button>
            </form>
            <small class="text-muted mt-2 d-block">
                La réservation empêche les autres utilisateurs de modifier ce document.
            </small>
        @elseif($document->isCheckedOutBy(Auth::user()))
            {{-- État 2: Réservé par moi --}}
            <span class="badge bg-warning text-dark mb-2">
                <i class="bi bi-person-lock"></i> Réservé par vous
            </span>
            <p class="small text-muted mb-2">
                Depuis le {{ $document->checked_out_at->format('d/m/Y à H:i') }}
            </p>

            {{-- Bouton Checkin --}}
            <button type="button" class="btn btn-success btn-sm w-100 mb-2"
                    data-bs-toggle="modal" data-bs-target="#checkinModal">
                <i class="bi bi-upload"></i> Déposer une nouvelle version
            </button>

            {{-- Bouton Cancel --}}
            <form action="{{ route('documents.cancel-checkout', $document) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                        onclick="return confirm('Annuler la réservation sans déposer de version ?')">
                    <i class="bi bi-x-circle"></i> Annuler la réservation
                </button>
            </form>
        @else
            {{-- État 3: Réservé par autre utilisateur --}}
            <span class="badge bg-danger mb-2">
                <i class="bi bi-person-lock"></i> Réservé
            </span>
            <p class="small mb-0">
                Par <strong>{{ $document->checkedOutUser->name ?? 'N/A' }}</strong><br>
                Depuis le {{ $document->checked_out_at->format('d/m/Y à H:i') }}
            </p>
            <div class="alert alert-info mt-2 mb-0 p-2">
                <small>Ce document n'est pas disponible pour modification.</small>
            </div>
        @endif
    </div>
</div>

{{-- Inclure la modale checkin si document réservé par utilisateur --}}
@if($document->isCheckedOutBy(Auth::user()))
    @include('repositories.documents.modals.checkin-modal')
@endif
