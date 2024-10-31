@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Détails du parapheur</h1>
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
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-building me-2 text-primary"></i>
                                Organisations
                            </h5>
                            <div class="mb-3">
                                <label class="text-muted small">Organisation de départ</label>
                                <p class="fw-semibold mb-3">{{ $batchTransaction->organisationSend->name }}</p>

                                <label class="text-muted small">Organisation d'arrivée</label>
                                <p class="fw-semibold mb-0">{{ $batchTransaction->organisationReceived->name }}</p>
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
</style>
@endsection
