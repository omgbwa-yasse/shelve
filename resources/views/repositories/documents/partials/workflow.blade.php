{{-- Gestion Workflow Approbation --}}
@if($document->requires_approval)
<div class="card mb-3">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-list-check"></i> Workflow Approbation
    </div>
    <div class="card-body">
        @if($document->approved_at)
            {{-- Approuvé --}}
            <span class="badge bg-success mb-2">
                <i class="bi bi-check-circle"></i> Approuvé
            </span>
            <p class="small mb-0">
                <strong>Par:</strong> {{ $document->approver->name ?? 'N/A' }}<br>
                <strong>Le:</strong> {{ $document->approved_at->format('d/m/Y à H:i') }}
                @if($document->approval_notes)
                    <br><strong>Notes:</strong> {{ $document->approval_notes }}
                @endif
            </p>
        @else
            {{-- En attente approbation --}}
            <span class="badge bg-warning text-dark mb-3">
                <i class="bi bi-clock"></i> En attente d'approbation
            </span>

            {{-- Formulaire Approve --}}
            <form action="{{ route('documents.approve', $document) }}" method="POST" class="mb-2">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Notes d'approbation (optionnel)</label>
                    <textarea name="approval_notes" class="form-control form-control-sm" rows="2"
                              placeholder="Notes pour cette approbation..."></textarea>
                </div>
                <button type="submit" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-check-lg"></i> Approuver
                </button>
            </form>

            {{-- Formulaire Reject --}}
            <button type="button" class="btn btn-outline-danger btn-sm w-100"
                    data-bs-toggle="collapse" data-bs-target="#rejectForm">
                <i class="bi bi-x-lg"></i> Rejeter
            </button>
            <div id="rejectForm" class="collapse mt-2">
                <form action="{{ route('documents.reject', $document) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Raison du rejet (requis)</label>
                        <textarea name="rejection_reason" class="form-control form-control-sm"
                                  rows="3" required placeholder="Expliquez la raison du rejet..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-slash-circle"></i> Confirmer le rejet
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endif
