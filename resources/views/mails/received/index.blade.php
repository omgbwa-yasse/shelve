@extends('layouts.app')

@section('content')
<div id="mailList">
    <div class="container-fluid">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Courriers entrants</h1>

        <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
            <div class="d-flex align-items-center">
                <a href="#" id="cartBtn" class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#dolliesModal">
                    <i class="bi bi-cart me-1"></i>
                    Chariot 
                </a>
                <a href="#" id="exportBtn" class="btn btn-light btn-sm me-2" data-route="{{ route('mail-transaction.export') }}">
                    <i class="bi bi-download me-1"></i>
                    Exporter
                </a>
                <a href="#" id="printBtn" class="btn btn-light btn-sm me-2" data-route="{{ route('mail-transaction.print') }}">
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

        <div id="mailList" class="mb-4">
            @foreach ($mails as $mail)
                <div class="mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                    <div class="card-header bg-light d-flex align-items-center py-2" style="border-bottom: 1px solid rgba(0,0,0,0.125);">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" value="{{ $mail->id }}" id="mail_{{ $mail->id }}" name="selected_mail[]" />
                        </div>

                        <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3" type="button" data-bs-toggle="collapse" data-bs-target="#mail-{{ $mail->id }}" aria-expanded="false" aria-controls="mail-{{ $mail->id }}">
                            <i class="bi bi-chevron-down fs-5"></i>
                        </button>

                        <h4 class="card-title flex-grow-1 m-0" for="mail_{{ $mail->id }}">
                            <a href="{{ route('mail-received.show', $mail) }}" class="text-decoration-none text-dark">
                                <span class="fs-5 fw-semibold">{{ $mail->code ?? 'N/A' }}</span>
                                <span class="fs-5"> - {{ $mail->name ?? 'N/A' }}</span>
                                <span class="badge bg-danger ms-2">{{ $mail->action->name ?? 'N/A' }}</span>
                            </a>
                        </h4>
                    </div>

                    <div class="collapse" id="mail-{{ $mail->id }}">
                        <div class="card-body bg-white">
                            @if($mail->description)
                                <div class="mb-3">
                                    <p class="mb-2">
                                        <i class="bi bi-card-text me-2 text-primary"></i>
                                        <strong>Description:</strong> {{ $mail->description }}
                                    </p>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-12">
                                    <p class="mb-2">
                                        <i class="bi bi-person-fill me-2 text-primary"></i>
                                        <strong>Envoyé par:</strong>
                                        {{ $mail->sender->name ?? 'N/A' }} ({{ $mail->senderOrganisation->name ?? 'N/A' }})
                                        <br>

                                        <i class="bi bi-person-fill me-2 text-primary"></i>
                                        <strong>Reçu par:</strong>
                                        {{ $mail->recipient->name ?? 'N/A' }} ({{ $mail->recipientOrganisation->name ?? 'N/A' }})
                                        <br>

                                        <i class="bi bi-file-earmark-text-fill me-2 text-primary"></i>
                                        <strong>Type de document:</strong>
                                        {{ $mail->document_type ?? 'N/A' }}
                                        <br>

                                        <i class="bi bi-calendar-event me-2 text-primary"></i>
                                        <strong>Date:</strong>
                                        {{ date('d/m/Y', strtotime($mail->date)) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    <div class="modal fade" id="dolliesModal" tabindex="-1" aria-labelledby="dolliesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dolliesModalLabel">Chariot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dolliesList">
                        <p>Aucun chariot chargé</p>
                    </div>
                    <div id="dollyForm" style="display: none;">
                        <form id="createDollyForm" action="{{ route('dolly.create') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label"> Categories </label>
                                <select class="form-select" id="category" name="category" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category }}" {{ $category == 'mail' ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Ajouter au chariot
                                </button>
                                <button type="button" class="btn btn-secondary" id="backToListBtn">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Retour à la liste
                                </button>
                            </div>
                        </form>
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Fermer
                    </button>
                    <button type="button" class="btn btn-primary" id="addDollyBtn">
                        <i class="bi bi-plus-circle me-1"></i> Nouveau chariot
                    </button>
                </div>
            </div>
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
    
    <script src="{{ asset('js/mails.js') }}"></script>
    <script src="{{ asset('js/dollies.js') }}"></script>
    
@endsection