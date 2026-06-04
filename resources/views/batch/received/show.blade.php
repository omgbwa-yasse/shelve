@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Détails du parapheur reçu</h1>
                <a href="{{ route('batch-received.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-info-circle me-2 text-primary"></i>
                                Informations principales
                            </h5>
                            <div class="mb-3">
                                <label class="text-muted small">ID du parapheur</label>
                                <p class="fw-semibold mb-0">{{ $batchTransaction->batch_id }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Code du parapheur</label>
                                <p class="fw-semibold mb-0">{{ $batchTransaction->batch->code ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Nom du parapheur</label>
                                <p class="fw-semibold mb-0">{{ $batchTransaction->batch->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-building me-2 text-success"></i>
                                Organisations
                            </h5>
                            <div class="mb-3">
                                <label class="text-muted small">Organisation expéditrice</label>
                                <p class="fw-semibold mb-3">{{ optional($batchTransaction->organisationSend)->name ?? 'N/A' }}</p>

                                <label class="text-muted small">Organisation destinataire</label>
                                <p class="fw-semibold mb-0">{{ optional($batchTransaction->organisationReceived)->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Courriers associés au parapheur --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-envelope-paper me-2 text-primary"></i>
                                    Courriers associés
                                    <span class="badge bg-primary ms-2">{{ $batchTransaction->mails->count() }}</span>
                                </h5>
                                @if($batchTransaction->batch)
                                    <a href="{{ route('batch.mail.index', $batchTransaction->batch->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square me-1"></i>Gérer les courriers
                                    </a>
                                @endif
                            </div>

                            @if($batchTransaction->mails->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Code</th>
                                                <th>Objet</th>
                                                <th>Expéditeur</th>
                                                <th>Date</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($batchTransaction->mails as $mail)
                                                <tr>
                                                    <td class="fw-semibold">{{ $mail->code ?? '—' }}</td>
                                                    <td>{{ $mail->name ?? '—' }}</td>
                                                    <td>{{ optional($mail->senderOrganisation)->name ?? optional($mail->sender)->name ?? '—' }}</td>
                                                    <td>{{ $mail->date ? \Illuminate\Support\Carbon::parse($mail->date)->format('d/m/Y') : '—' }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('mail-received.show', $mail->id) }}" class="btn btn-sm btn-outline-secondary" title="Voir le courrier">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        @if($batchTransaction->batch)
                                                            <form action="{{ route('batch.mail.destroy', [$batchTransaction->batch->id, $mail->id]) }}" method="POST" class="d-inline"
                                                                  onsubmit="return confirm('Retirer ce courrier du parapheur ?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Retirer du parapheur">
                                                                    <i class="bi bi-x-lg"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                    Aucun courrier associé à ce parapheur.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-clock-history me-2 text-info"></i>
                                Historique de la transaction
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="text-muted small">Date de création</label>
                                    <p class="fw-semibold mb-0">{{ $batchTransaction->created_at ? $batchTransaction->created_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Dernière mise à jour</label>
                                    <p class="fw-semibold mb-0">{{ $batchTransaction->updated_at ? $batchTransaction->updated_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: none;
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.card-title {
    color: #2c3e50;
    font-size: 1.1rem;
}

.text-muted {
    color: #6c757d !important;
}

.bi {
    font-size: 1rem;
}

.btn-outline-secondary {
    border-color: #dee2e6;
}

.btn-outline-secondary:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #2c3e50;
}

.text-primary {
    color: #007bff !important;
}

.text-success {
    color: #28a745 !important;
}

.text-info {
    color: #17a2b8 !important;
}
</style>
@endsection
