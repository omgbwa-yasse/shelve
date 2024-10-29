@extends('layouts.app')

@section('content')
<div id="mailList">
    <div class="container">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Courriers entrants</h1>

    <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
        <div class="d-flex align-items-center">
            <a href="#" id="cartBtn" class="btn btn-light btn-sm me-2">
                <i class="bi bi-cart me-1"></i>
                Chariot ***
            </a>
            <a href="#" id="exportBtn" class="btn btn-light btn-sm me-2">
                <i class="bi bi-download me-1"></i>
                Exporter ***
            </a>
            <a href="#" id="printBtn" class="btn btn-light btn-sm me-2">
                <i class="bi bi-printer me-1"></i>
                Imprimer ***
            </a>
        </div>
        <div class="d-flex align-items-center">
            <a href="#" id="transferBtn" class="btn btn-light btn-sm me-2">
                <i class="bi bi-arrow-repeat me-1"></i>
                Transférer ***
            </a>

            <a href="#" id="communicateBtn" class="btn btn-light btn-sm me-2">
                <i class="bi bi-envelope me-1"></i>
                Communiquer ***
            </a>
            <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                <i class="bi bi-check-square me-1"></i>
                Tout cocher ***
            </a>
        </div>
    </div>


    <div id="transactionList" class="mb-4">
        @foreach ($transactions as $transaction)
            <div class="mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                <div class="card-header bg-light d-flex align-items-center py-2" style="border-bottom: 1px solid rgba(0,0,0,0.125);">
                    <div class="form-check me-3">
                        <input class="form-check-input"
                               type="checkbox"
                               value="{{ $transaction->id }}"
                               id="mail_{{ $transaction->id }}"
                               name="selected_mail[]" />
                    </div>

                    <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#transaction-{{ $transaction->id }}"
                            aria-expanded="false"
                            aria-controls="transaction-{{ $transaction->id }}">
                        <i class="bi bi-chevron-down fs-5"></i>
                    </button>

                    <h4 class="card-title flex-grow-1 m-0" for="mail_{{ $transaction->id }}">
                        <a href="{{ route('mail-received.show', $transaction) }}"
                           class="text-decoration-none text-dark">
                            <span class="fs-5 fw-semibold">{{ $transaction->code ?? 'N/A' }}</span>
                            <span class="fs-5"> - {{ $transaction->mail->name ?? 'N/A' }}</span>
                            <span class="badge bg-danger ms-2">{{ $transaction->action->name }}</span>
                        </a>
                    </h4>
                </div>

                <div class="collapse" id="transaction-{{ $transaction->id }}">
                    <div class="card-body bg-white">
                        @if($transaction->description)
                            <div class="mb-3">
                                <p class="mb-2">
                                    <i class="bi bi-card-text me-2 text-primary"></i>
                                    <strong>Description:</strong> {{ $transaction->description }}
                                </p>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-12">
                                <p class="mb-2">
                                    <i class="bi bi-person-fill me-2 text-primary"></i>
                                    <strong>Envoyé par:</strong>
                                    {{ $transaction->userSend->name ?? 'N/A' }}
                                    ({{ $transaction->organisationSend->name ?? 'N/A' }})
                                    <br>

                                    <i class="bi bi-person-fill me-2 text-primary"></i>
                                    <strong>Reçu par:</strong>
                                    {{ $transaction->userReceived->name ?? 'N/A' }}
                                    ({{ $transaction->organisationReceived->name ?? 'N/A' }})
                                    <br>

                                    <i class="bi bi-file-earmark-text-fill me-2 text-primary"></i>
                                    <strong>Type de document:</strong>
                                    {{ $transaction->documentType->name ?? 'N/A' }}
                                    <br>

                                    <i class="bi bi-calendar-event me-2 text-primary"></i>
                                    <strong>Date:</strong>
                                    {{ $transaction->date_creation ? \Carbon\Carbon::parse($transaction->date_creation)->format('d/m/Y') : 'N/A' }}
                                </p>
                            </div>
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

        .badge {
            font-weight: 500;
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
        </style>


@endsection
