@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Courriers sortants externes</h1>

    <!-- Bandeau de navigation -->
    <div class="d-flex justify-content-start align-items-center bg-light p-2 mb-2 rounded overflow-auto">
        <div class="d-flex align-items-center gap-3 px-2">
            <a href="{{ route('mails.received.external.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers reçus externes">
                <i class="bi bi-inbox fs-5 text-primary"></i>
                <span class="small">Reçus</span>
            </a>
            <a href="{{ route('mails.send.external.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers envoyés externes">
                <i class="bi bi-envelope fs-5 text-success"></i>
                <span class="small fw-bold">Envoyés</span>
            </a>
        </div>

        <div class="ms-auto pe-2">
            <a href="{{ route('mails.send.external.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle"></i> Nouveau courrier externe
            </a>
        </div>
    </div>

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
            <a href="#" id="cartBtn" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#dolliesModal">
                <i class="bi bi-cart me-1"></i>
                Chariot
            </a>
            <a href="#" id="exportBtn" class="btn btn-light btn-sm" data-route="{{ route('mail-transaction.export') }}">
                <i class="bi bi-download me-1"></i>
                Exporter
            </a>
            <a href="#" id="printBtn" class="btn btn-light btn-sm" data-route="{{ route('mail-transaction.print') }}">
                <i class="bi bi-printer me-1"></i>
                Imprimer
            </a>
            <a href="#" id="archiveBtn" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#archiveModal">
                <i class="bi bi-archive me-1"></i>
                Archiver
            </a>
        </div>
        <div class="d-flex align-items-center">
            <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                <i class="bi bi-check-square me-1"></i>
                Tout cocher
            </a>
        </div>
    </div>

    <!-- Liste des courriers -->
    <div class="card shadow-sm border-0 rounded">
        <div class="card-body">
            @if($mails->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Code</th>
                                <th>Nom</th>
                                <th>Date</th>
                                <th>Typologie</th>
                                <th>Destinataire</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mails as $mail)
                                <tr>
                                    <td><input type="checkbox" class="mail-checkbox" value="{{ $mail->id }}"></td>
                                    <td>
                                        <span class="badge bg-dark text-white">{{ $mail->code }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $mail->name }}</strong>
                                        @if($mail->description)
                                            <br><small class="text-muted">{{ Str::limit($mail->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $mail->date ? \Carbon\Carbon::parse($mail->date)->format('d/m/Y') : 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @if($mail->typology)
                                            <span class="badge bg-primary">{{ $mail->typology->name }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mail->externalRecipient)
                                            <i class="bi bi-person-circle text-primary"></i>
                                            {{ $mail->externalRecipient->first_name }} {{ $mail->externalRecipient->last_name }}
                                        @elseif($mail->externalRecipientOrganization)
                                            <i class="bi bi-building text-warning"></i>
                                            {{ $mail->externalRecipientOrganization->name }}
                                        @else
                                            <span class="text-muted">Non défini</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($mail->status->value ?? '') {
                                                'draft' => 'bg-secondary',
                                                'pending' => 'bg-warning',
                                                'approved' => 'bg-success',
                                                'sent' => 'bg-primary',
                                                'rejected' => 'bg-danger',
                                                default => 'bg-light text-dark'
                                            };
                                            $statusText = match($mail->status->value ?? '') {
                                                'draft' => 'Brouillon',
                                                'pending' => 'En attente',
                                                'approved' => 'Approuvé',
                                                'sent' => 'Envoyé',
                                                'rejected' => 'Rejeté',
                                                default => 'Inconnu'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('mails.send.external.show', $mail->id) }}" class="btn btn-sm btn-outline-info" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('mails.send.external.edit', $mail->id) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('mails.send.external.destroy', $mail->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce courrier ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $mails->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-envelope-slash fs-1 text-muted"></i>
                    <h4 class="text-muted mt-2">Aucun courrier sortant externe</h4>
                    <p class="text-muted">Commencez par créer votre premier courrier externe.</p>
                    <a href="{{ route('mails.send.external.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Créer un courrier externe
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Modals and JavaScript -->
    <div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archiveModalLabel">Archiver les documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="archiveForm">
                        <div class="mb-3">
                            <label for="containerId" class="form-label">Conteneur d'archives</label>
                            <select class="form-select" id="containerId" required>
                                <option value="" selected disabled>Sélectionner un conteneur</option>
                                <!-- Les conteneurs seront chargés dynamiquement ici -->
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmArchiveBtn">Confirmer l'archivage</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/mails.js') }}"></script>
    @endpush
@endsection
