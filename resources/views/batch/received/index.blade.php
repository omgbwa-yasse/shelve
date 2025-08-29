@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Parapheurs reçus</h1>
        <a href="{{ route('batch-received.create') }}" class="btn btn-success">
            <i class="bi bi-inbox me-2"></i>Recevoir un parapheur
        </a>
    </div>

    <div id="batchTransactionList" class="mb-4">
        @foreach ($batchTransactions as $batchTransaction)
            <div class="mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                <div class="card-header bg-light d-flex align-items-center py-2" style="border-bottom: 1px solid rgba(0,0,0,0.125);">
                    <div class="form-check me-3">
                        <input class="form-check-input"
                               type="checkbox"
                               value="{{ $batchTransaction->id }}"
                               id="batch_{{ $batchTransaction->id }}"
                               name="selected_batch[]" />
                    </div>

                    <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#batch-{{ $batchTransaction->id }}"
                            aria-expanded="false"
                            aria-controls="batch-{{ $batchTransaction->id }}">
                        <i class="bi bi-chevron-down fs-5"></i>
                    </button>

                    <h4 class="card-title flex-grow-1 m-0" for="batch_{{ $batchTransaction->id }}">
                        <a href="{{ route('batch-received.show', $batchTransaction) }}"
                           class="text-decoration-none text-dark">
                            <span class="fs-5 fw-semibold">{{ $batchTransaction->batch->code ?? 'N/A' }}</span>
                            <span class="fs-5"> - {{ $batchTransaction->batch->name ?? 'N/A' }}</span>
                        </a>
                    </h4>

                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('batch-received.edit', $batchTransaction) }}"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('batch-received.destroy', $batchTransaction) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce parapheur ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="collapse" id="batch-{{ $batchTransaction->id }}">
                    <div class="card-body bg-white">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <i class="bi bi-building me-2 text-primary"></i>
                                    <strong>Organisation de départ:</strong>
                                    <p class="ms-4 mb-0">{{ $batchTransaction->organisationSend->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <i class="bi bi-building me-2 text-primary"></i>
                                    <strong>Organisation d'arrivée:</strong>
                                    <p class="ms-4 mb-0">{{ $batchTransaction->organisationReceived->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <i class="bi bi-calendar-event me-2 text-primary"></i>
                            <strong>Date de création:</strong>
                            <span class="ms-2">{{ $batchTransaction->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
.card-header {
    transition: background-color 0.2s ease;
}

.card-header:hover {
    background-color: #f8f9fa !important;
}

.bi {
    font-size: 0.9rem;
}

.collapse {
    transition: all 0.3s ease-out;
}

.btn-link:focus {
    box-shadow: none;
}

.bi-chevron-down {
    transition: transform 0.3s ease;
}

[aria-expanded="true"] .bi-chevron-down {
    transform: rotate(180deg);
}

.btn-sm {
    padding: 0.25rem 0.75rem;
}

.btn-sm i {
    font-size: 0.875rem;
}
</style>
@endsection
