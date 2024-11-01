@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-envelope-open text-primary me-2"></i>
                    Courrier entrant : {{ $mailTransaction->mail->name ?? 'N/A' }}
                </h5>
                <small class="text-muted">
                    Reçu le {{ $mailTransaction->mail->date ? date('d/m/Y H:i', strtotime($mailTransaction->mail->date)) : 'N/A' }}
                </small>
            </div>
            <div class="btn-group">
                <a href="{{ route('mail-received.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <a href="{{ route('mail-received.edit', $mailTransaction->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body p-3">
                        <!-- Informations principales -->
                        <div class="row g-3">
                            <!-- Expéditeur -->
                            <div class="col-md-6">
                                <div class="d-flex p-2 border rounded bg-light">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                                            <i class="bi bi-person text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="ms-2">
                                        <small class="text-muted d-block">Expéditeur</small>
                                        <strong>{{ $mailTransaction->userSend->name ?? 'N/A' }}</strong>
                                        <small class="text-muted d-block">{{ $mailTransaction->organisationSend->name ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Destinataire -->
                            <div class="col-md-6">
                                <div class="d-flex p-2 border rounded bg-light">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-2">
                                            <i class="bi bi-building text-success"></i>
                                        </div>
                                    </div>
                                    <div class="ms-2">
                                        <small class="text-muted d-block">Organisation destinataire</small>
                                        <strong>{{ $mailTransaction->organisationReceived->name ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Détails -->
                        <div class="mt-3">
                            <table class="table table-sm table-borderless">
                                <tbody>
                                <tr>
                                    <td style="width: 150px;"><small class="text-muted">Affaire</small></td>
                                    <td>{{ $mailTransaction->mail->typology->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted">Type de document</small></td>
                                    <td>{{ $mailTransaction->documentType->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted">Action requise</small></td>
                                    <td><span class="badge bg-primary">{{ $mailTransaction->action->name ?? 'N/A' }}</span></td>
                                </tr>
                                @if($mailTransaction->description)
                                    <tr>
                                        <td><small class="text-muted">Description</small></td>
                                        <td>{{ $mailTransaction->description }}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header py-2">
                        <h6 class="mb-0">Actions rapides</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action py-2">
                            <i class="bi bi-reply me-2"></i> Répondre
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-2">
                            <i class="bi bi-forward me-2"></i> Transférer
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-2">
                            <i class="bi bi-archive me-2"></i> Archiver
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-2">
                            <i class="bi bi-printer me-2"></i> Imprimer
                        </a>

                    </div>
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
                    Êtes-vous sûr de vouloir supprimer ce courrier ?
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('mail-received.destroy', $mailTransaction->id) }}" method="POST" class="d-inline">
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
            .rounded-circle {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .card {
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .list-group-item {
                transition: background-color 0.2s;
            }
            .list-group-item:hover {
                background-color: rgba(0,0,0,0.02);
            }
        </style>
    @endpush

@endsection
