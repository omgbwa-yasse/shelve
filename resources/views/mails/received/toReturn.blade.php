@extends('layouts.app')

@section('content')
<div id="mailList">

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Courriers à retourner</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Bandeau d'actions -->
    <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3 rounded">
        <div class="d-flex align-items-center gap-2">
            <a href="#" id="cartBtn" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#cartModal">
                <i class="bi bi-cart me-1"></i>
                Chariot
            </a>
            <a href="#" id="exportBtn" class="btn btn-light btn-sm">
                <i class="bi bi-download me-1"></i>
                Exporter
            </a>
            <a href="#" id="printBtn" class="btn btn-light btn-sm" data-route="{{ route('mail-transaction.print') }}">
                <i class="bi bi-printer me-1"></i>
                Imprimer
            </a>
            <a href="#" id="archiveBtn" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#archiveModal">
                <i class="bi bi-archive me-1"></i>
                Conserver
            </a>
        </div>
        <div class="d-flex align-items-center">
            <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                <i class="bi bi-check-square me-1"></i>
                Tout cocher
            </a>
        </div>
    </div>

    @if($mailsToReturn->count() > 0)
        <div class="mb-3">
            <p class="text-muted">
                <i class="bi bi-info-circle"></i>
                {{ $mailsToReturn->count() }} courrier(s) reçu(s) à retourner
            </p>
        </div>

        <div id="mailList" class="mb-4">
            @foreach ($mailsToReturn as $mail)
                <div class="card mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                    <div class="card-header bg-light d-flex align-items-center py-2">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" value="{{ $mail->id }}" id="mail_{{ $mail->id }}" name="selected_mail[]" />
                        </div>

                        <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $mail->id }}" aria-expanded="false" aria-controls="collapse{{ $mail->id }}">
                            <i class="bi bi-chevron-down fs-5"></i>
                        </button>

                        <h4 class="card-title flex-grow-1 m-0" for="mail_{{ $mail->id }}">
                            <a href="{{ route('mail-received.show', $mail) }}" class="text-decoration-none text-dark">
                                <span class="fs-5 fw-semibold">{{ $mail->code ?? 'N/A' }}</span>
                                <span class="fs-5"> - {{ $mail->name ?? 'N/A' }}</span>

                                @if($mail->action)
                                    <span class="badge bg-warning ms-2">
                                        <i class="bi bi-arrow-return-left"></i>
                                        {{ $mail->action->name }}
                                    </span>
                                @endif

                                @if($mail->typology)
                                    <span class="badge bg-info ms-2">{{ $mail->typology->name }}</span>
                                @endif

                                @if($mail->isOverdue())
                                    <span class="badge bg-danger ms-2">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        En retard
                                    </span>
                                @elseif($mail->isApproachingDeadline())
                                    <span class="badge bg-warning ms-2">
                                        <i class="bi bi-clock"></i>
                                        Urgent
                                    </span>
                                @endif
                            </a>
                        </h4>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary">{{ $mail->status->label() }}</span>
                            <span class="text-muted small">{{ $mail->date ? $mail->date->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="collapse" id="collapse{{ $mail->id }}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Description:</strong> {{ $mail->description ?: 'Aucune description' }}</p>
                                    <p class="mb-1"><strong>Expéditeur:</strong>
                                        @if($mail->sender)
                                            {{ $mail->sender->name }}
                                        @elseif($mail->senderOrganisation)
                                            {{ $mail->senderOrganisation->name }}
                                        @else
                                            Non défini
                                        @endif
                                    </p>
                                    @if($mail->action)
                                        <p class="mb-1"><strong>Action à effectuer:</strong>
                                            <span class="text-warning">
                                                <i class="bi bi-arrow-return-left"></i>
                                                {{ $mail->action->name }}
                                            </span>
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    @if($mail->deadline)
                                        <p class="mb-1"><strong>Échéance:</strong>
                                            <span class="@if($mail->isOverdue()) text-danger @elseif($mail->isApproachingDeadline()) text-warning @endif">
                                                {{ $mail->deadline->format('d/m/Y H:i') }}
                                                @if($mail->isOverdue())
                                                    <i class="bi bi-exclamation-triangle text-danger"></i>
                                                @elseif($mail->isApproachingDeadline())
                                                    <i class="bi bi-clock text-warning"></i>
                                                @endif
                                            </span>
                                        </p>
                                    @endif
                                    @if($mail->priority)
                                        <p class="mb-1"><strong>Priorité:</strong> {{ $mail->priority->name }}</p>
                                    @endif
                                    <p class="mb-1"><strong>Date de réception:</strong> {{ $mail->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>

                            <!-- Actions rapides -->
                            <div class="mt-3 d-flex gap-2">
                                <a href="{{ route('mail-received.show', $mail) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i>
                                    Voir détail
                                </a>
                                <a href="{{ route('mail-received.edit', $mail) }}" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-pencil"></i>
                                    Modifier
                                </a>
                                <button type="button" class="btn btn-sm btn-warning return-action-btn" data-mail-id="{{ $mail->id }}">
                                    <i class="bi bi-arrow-return-left"></i>
                                    Marquer comme retourné
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
            <h4 class="text-muted mt-3">Aucun courrier à retourner</h4>
            <p class="text-muted">Excellent ! Vous n'avez actuellement aucun courrier reçu nécessitant un retour.</p>
            <a href="{{ route('mail-received.index') }}" class="btn btn-primary mt-3">
                <i class="bi bi-inbox"></i>
                Voir tous les courriers reçus
            </a>
        </div>
    @endif

</div>

<!-- Modal Chariot -->
<div class="modal fade" id="cartModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chariot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @foreach($dollies as $dolly)
                        <div class="col-md-6 mb-3">
                            <button type="button" class="btn btn-outline-primary w-100 dolly-btn" data-dolly-id="{{ $dolly->id }}">
                                <i class="bi bi-cart"></i> {{ $dolly->name }}
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Archive -->
<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archiver les courriers sélectionnés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Voulez-vous archiver les courriers sélectionnés ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="confirmArchive">Archiver</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction de sélection/désélection globale
    const checkAllBtn = document.getElementById('checkAllBtn');
    const mailCheckboxes = document.querySelectorAll('input[name="selected_mail[]"]');

    checkAllBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const allChecked = Array.from(mailCheckboxes).every(cb => cb.checked);
        mailCheckboxes.forEach(cb => cb.checked = !allChecked);
        this.innerHTML = allChecked
            ? '<i class="bi bi-check-square me-1"></i>Tout cocher'
            : '<i class="bi bi-square me-1"></i>Tout décocher';
    });

    // Gestion des chariots
    document.querySelectorAll('.dolly-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const dollyId = this.dataset.dollyId;
            const selectedMails = Array.from(document.querySelectorAll('input[name="selected_mail[]"]:checked'))
                .map(cb => cb.value);

            if (selectedMails.length === 0) {
                alert('Veuillez sélectionner au moins un courrier.');
                return;
            }

            // Logique d'ajout au chariot
            console.log('Ajout au chariot', dollyId, selectedMails);
        });
    });

    // Gestion du marquage comme retourné
    document.querySelectorAll('.return-action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const mailId = this.dataset.mailId;

            if (confirm('Marquer ce courrier comme retourné ?')) {
                // Ici, vous pouvez ajouter la logique pour marquer le courrier comme retourné
                // Par exemple, une requête AJAX vers une route spécifique
                console.log('Marquer comme retourné le mail ID:', mailId);

                // Exemple de ce que pourrait être la logique :
                // fetch(`/mails/received/${mailId}/mark-returned`, {
                //     method: 'POST',
                //     headers: {
                //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                //         'Content-Type': 'application/json'
                //     }
                // }).then(response => {
                //     if (response.ok) {
                //         location.reload();
                //     }
                // });
            }
        });
    });
});
</script>
@endsection
