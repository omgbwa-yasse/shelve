@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">
        <!-- Header compact -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-1">{{ $mailTransaction->code }} - {{ $mailTransaction->mail->name }}</h5>
                <small class="text-muted">
                    Créé le {{ $mailTransaction->date_creation ? date('d/m/Y H:i', strtotime($mailTransaction->date_creation)) : 'N/A' }}
                </small>
            </div>
            <div class="btn-group">
                <a href="{{ route('mail-send.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#transferModal">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-download me-2"></i>Exporter</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-share me-2"></i>Partager</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="bi bi-trash me-2"></i>Supprimer
                            </button></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Informations principales -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="bi bi-person text-primary"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Expéditeur</small>
                                        <span class="text-body">{{ $mailTransaction->userSend->name ?? 'N/A' }}</span>
                                        <small class="text-muted d-block">{{ $mailTransaction->organisationSend->name ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                        <i class="bi bi-person-check text-success"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Destinataire</small>
                                        <span class="text-body">{{ $mailTransaction->userReceived->name ?? 'N/A' }}</span>
                                        <small class="text-muted d-block">{{ $mailTransaction->organisationReceived->name ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2">

                        <div class="row g-2">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Type de document</small>
                                <span>{{ $mailTransaction->documentType->name ?? 'N/A' }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Action requise</small>
                                <span class="badge bg-primary">{{ $mailTransaction->action->name ?? 'N/A' }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Date création</small>
                                <span>{{ $mailTransaction->date_creation ? date('d/m/Y', strtotime($mailTransaction->date_creation)) : 'N/A' }}</span>
                            </div>
                        </div>

                        @if($mailTransaction->description)
                            <div class="mt-2">
                                <small class="text-muted d-block">Description</small>
                                <p class="mb-0">{{ $mailTransaction->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pièces jointes -->
                @if($mailTransaction->mail->attachments->count() > 0)
                    <div class="card mt-3">
                        <div class="card-body p-2">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($mailTransaction->mail->attachments as $attachment)
                                    <div class="border rounded p-2 d-flex align-items-center">
                                        <i class="bi bi-paperclip me-2"></i>
                                        <span class="me-2">{{ $attachment->name }}</span>
                                        <a href="#" class="text-primary"><i class="bi bi-download"></i></a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Historique -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header py-2 px-3">
                        <h6 class="mb-0">Historique</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($mailHistory as $history)
                                <div class="list-group-item py-2 px-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light p-1 me-2">
                                            <i class="bi bi-clock text-secondary small"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <small class="d-block">{{ $history->action->name }}</small>
                                            <small class="text-muted">
                                                {{ $history->userSend->name }} → {{ $history->userReceived->name }}
                                            </small>
                                            <small class="text-muted d-block">
                                                {{ date('d/m/Y H:i', strtotime($history->created_at)) }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de transfert (version compacte) -->
    <div class="modal fade" id="transferModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Transférer</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-2">
                            <label class="form-label small">Organisation</label>
                            <select class="form-select form-select-sm">
                                <option value="">Sélectionner...</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Utilisateur</label>
                            <select class="form-select form-select-sm">
                                <option value="">Sélectionner...</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Commentaire</label>
                            <textarea class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-sm btn-primary">Transférer</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Confirmation</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Êtes-vous sûr de vouloir supprimer ce courrier ?</p>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('mail-send.destroy', $mailTransaction->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('styles')
        <style>
            .btn-group .btn {
                padding: 0.25rem 0.5rem;
            }
            .rounded-circle {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .list-group-item {
                border-left: none;
                border-right: none;
            }
            .card {
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
        </style>
    @endpush
@endsection
