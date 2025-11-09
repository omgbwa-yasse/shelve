{{-- Gestion Réservation Document --}}
<div class="card mb-3">
    <div class="card-header bg-info text-white">
        <i class="fas fa-lock"></i> Réservation Document
    </div>
    <div class="card-body">
        @if(!$document->is_current_version)
            <div class="alert alert-warning mb-0">
                <small><i class="fas fa-info-circle"></i> Seule la version courante peut être réservée.</small>
            </div>
        @elseif(!$document->isCheckedOut())
            {{-- État 1: Document libre --}}
            <span class="badge badge-success mb-2">
                <i class="fas fa-unlock"></i> Disponible
            </span>
            <form action="{{ route('documents.checkout', $document) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-lock"></i> Réserver le document
                </button>
            </form>
            <small class="text-muted mt-2 d-block">
                La réservation empêche les autres utilisateurs de modifier ce document.
            </small>
        @elseif($document->isCheckedOutBy(Auth::user()))
            {{-- État 2: Réservé par moi --}}
            <span class="badge badge-warning mb-2">
                <i class="fas fa-user-lock"></i> Réservé par vous
            </span>
            <p class="small text-muted mb-2">
                Depuis le {{ $document->checked_out_at->format('d/m/Y à H:i') }}
            </p>

            {{-- Bouton Checkin --}}
            <button type="button" class="btn btn-success btn-sm btn-block mb-2"
                    data-toggle="modal" data-target="#checkinModal">
                <i class="fas fa-upload"></i> Déposer une nouvelle version
            </button>

            {{-- Bouton Cancel --}}
            <form action="{{ route('documents.cancel-checkout', $document) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm btn-block"
                        onclick="return confirm('Annuler la réservation sans déposer de version ?')">
                    <i class="fas fa-times"></i> Annuler la réservation
                </button>
            </form>
        @else
            {{-- État 3: Réservé par autre utilisateur --}}
            <span class="badge badge-danger mb-2">
                <i class="fas fa-user-lock"></i> Réservé
            </span>
            <p class="small mb-0">
                Par <strong>{{ $document->checkedOutUser->name }}</strong><br>
                Depuis le {{ $document->checked_out_at->format('d/m/Y à H:i') }}
            </p>
            <div class="alert alert-info mt-2 mb-0">
                <small>Ce document n'est pas disponible pour modification.</small>
            </div>
        @endif
    </div>
</div>

{{-- Inclure la modale checkin si document réservé par utilisateur --}}
@if($document->isCheckedOutBy(Auth::user()))
    @include('repositories.documents.modals.checkin-modal')
@endif
