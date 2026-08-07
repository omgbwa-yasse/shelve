@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $organization->name }}</h4>
                    <div>
                        <a href="{{ route('external.organizations.edit', $organization->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="{{ route('external.organizations.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> Liste
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Informations générales</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 200px;">Nom:</th>
                                            <td>{{ $organization->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Forme juridique:</th>
                                            <td>{{ $organization->legal_form ?: 'Non spécifié' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Numéro d'immatriculation:</th>
                                            <td>{{ $organization->registration_number ?: 'Non spécifié' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Statut:</th>
                                            <td>
                                                @if ($organization->is_verified)
                                                    <span class="badge bg-success">Vérifié</span>
                                                @else
                                                    <span class="badge bg-warning">Non vérifié</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Coordonnées</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 200px;">Email:</th>
                                            <td>{{ $organization->email ?: 'Non spécifié' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Téléphone:</th>
                                            <td>{{ $organization->phone ?: 'Non spécifié' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Site web:</th>
                                            <td>
                                                @if ($organization->website)
                                                    <a href="{{ $organization->website }}" target="_blank">{{ $organization->website }}</a>
                                                @else
                                                    Non spécifié
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Adresse:</th>
                                            <td>
                                                @if ($organization->address)
                                                    {{ $organization->address }}
                                                    @if ($organization->postal_code || $organization->city)
                                                        <br>
                                                        {{ $organization->postal_code }} {{ $organization->city }}
                                                    @endif
                                                    @if ($organization->country && $organization->country != 'France')
                                                        <br>
                                                        {{ $organization->country }}
                                                    @endif
                                                @else
                                                    Non spécifié
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>Contacts</h5>
                                    <a href="{{ route('external.contacts.create', ['organization_id' => $organization->id]) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Ajouter un contact
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Position</th>
                                                    <th>Email</th>
                                                    <th>Téléphone</th>
                                                    <th>Statut</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($organization->contacts as $contact)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('external.contacts.show', $contact->id) }}">
                                                                {{ $contact->first_name }} {{ $contact->last_name }}
                                                            </a>
                                                            @if ($contact->is_primary_contact)
                                                                <span class="badge bg-info">Contact principal</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $contact->position }}</td>
                                                        <td>{{ $contact->email }}</td>
                                                        <td>{{ $contact->phone }}</td>
                                                        <td>
                                                            @if ($contact->is_verified)
                                                                <span class="badge bg-success">Vérifié</span>
                                                            @else
                                                                <span class="badge bg-warning">Non vérifié</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('external.contacts.show', $contact->id) }}" class="btn btn-info btn-sm">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('external.contacts.edit', $contact->id) }}" class="btn btn-primary btn-sm">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">Aucun contact associé à cette organisation</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Courriers envoyés par cette organisation</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Date</th>
                                                    <th>Destinataire</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($sentMails as $mail)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('mail-received.show', $mail->id) }}">
                                                                {{ $mail->code }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $mail->date->format('d/m/Y') }}</td>
                                                        <td>
                                                            @if ($mail->recipient_type == 'user' && $mail->recipient)
                                                                {{ $mail->recipient->name }}
                                                            @elseif ($mail->recipient_type == 'organisation' && $mail->recipientOrganisation)
                                                                {{ $mail->recipientOrganisation->name }}
                                                            @else
                                                                Non spécifié
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $mail->status->color() }}">
                                                                {{ $mail->status->label() }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">Aucun courrier envoyé</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                        @if (count($sentMails) > 0)
                                            <div class="text-center mt-3">
                                                <a href="#" class="btn btn-link">Voir tous les courriers envoyés</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Courriers reçus par cette organisation</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Date</th>
                                                    <th>Expéditeur</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($receivedMails as $mail)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('mail-send.show', $mail->id) }}">
                                                                {{ $mail->code }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $mail->date->format('d/m/Y') }}</td>
                                                        <td>
                                                            @if ($mail->sender_type == 'user' && $mail->sender)
                                                                {{ $mail->sender->name }}
                                                            @elseif ($mail->sender_type == 'organisation' && $mail->senderOrganisation)
                                                                {{ $mail->senderOrganisation->name }}
                                                            @else
                                                                Non spécifié
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $mail->status->color() }}">
                                                                {{ $mail->status->label() }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">Aucun courrier reçu</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                        @if (count($receivedMails) > 0)
                                            <div class="text-center mt-3">
                                                <a href="#" class="btn btn-link">Voir tous les courriers reçus</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($organization->notes)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5>Notes</h5>
                                    </div>
                                    <div class="card-body">
                                        {{ $organization->notes }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash"></i> Supprimer cette organisation
                            </button>
                        </div>
                    </div>

                    <!-- Modal de confirmation de suppression -->
                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Êtes-vous sûr de vouloir supprimer l'organisation <strong>{{ $organization->name }}</strong> ?
                                    <br><br>
                                    Cette action est irréversible. Les contacts associés à cette organisation ne seront pas supprimés mais perdront leur association avec cette organisation.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <form action="{{ route('external.organizations.destroy', $organization->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
