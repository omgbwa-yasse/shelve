@extends('layouts.app')

@section('content')
<div id="mailList">

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Courriers retournés</h1>

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
        </div>
        <div class="d-flex align-items-center">
            <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                <i class="bi bi-check-square me-1"></i>
                Tout cocher
            </a>
        </div>
    </div>

    <!-- Section: Courriers émis retournés -->
    @if($mails->count() > 0)
        <div class="mb-5">
            <h3 class="text-xl font-bold text-gray-800 mb-3">
                <i class="bi bi-send text-primary"></i>
                Courriers émis retournés ({{ $mails->count() }})
            </h3>
            <div id="sentMailList" class="mb-4">
                @foreach ($mails as $mail)
                    <div class="card mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                        <div class="card-header bg-light d-flex align-items-center py-2">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="checkbox" value="{{ $mail->id }}" id="mail_{{ $mail->id }}" name="selected_mail[]" />
                            </div>

                            <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $mail->id }}" aria-expanded="false" aria-controls="collapse{{ $mail->id }}">
                                <i class="bi bi-chevron-down fs-5"></i>
                            </button>

                            <h4 class="card-title flex-grow-1 m-0" for="mail_{{ $mail->id }}">
                                <a href="{{ route('mail-send.show', $mail) }}" class="text-decoration-none text-dark">
                                    <span class="fs-5 fw-semibold">{{ $mail->code ?? 'N/A' }}</span>
                                    <span class="fs-5"> - {{ $mail->name ?? 'N/A' }}</span>

                                    @if($mail->action)
                                        <span class="badge bg-warning ms-2">{{ $mail->action->name }}</span>
                                    @endif

                                    @if($mail->typology)
                                        <span class="badge bg-info ms-2">{{ $mail->typology->name }}</span>
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
                                        <p class="mb-1"><strong>Destinataire:</strong>
                                            @if($mail->recipient)
                                                {{ $mail->recipient->name }}
                                            @elseif($mail->recipientOrganisation)
                                                {{ $mail->recipientOrganisation->name }}
                                            @else
                                                Non défini
                                            @endif
                                        </p>
                                        @if($mail->action)
                                            <p class="mb-1"><strong>Action:</strong> {{ $mail->action->name }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        @if($mail->deadline)
                                            <p class="mb-1"><strong>Échéance:</strong>
                                                <span class="@if($mail->isOverdue()) text-danger @elseif($mail->isApproachingDeadline()) text-warning @endif">
                                                    {{ $mail->deadline->format('d/m/Y H:i') }}
                                                </span>
                                            </p>
                                        @endif
                                        @if($mail->priority)
                                            <p class="mb-1"><strong>Priorité:</strong> {{ $mail->priority->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($mails->count() == 0)
        <div class="text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h4 class="text-muted mt-3">Aucun courrier retourné</h4>
            <p class="text-muted">Il n'y a actuellement aucun courrier qui vous a été retourné.</p>
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
});
</script>
@endsection
