@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $contact->first_name }} {{ $contact->last_name }}</h4>
                    <div>
                        <a href="{{ route('external.contacts.edit', $contact->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="{{ route('external.contacts.index') }}" class="btn btn-secondary btn-sm">
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
                                    <h5>Informations personnelles</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 200px;">Prénom:</th>
                                            <td>{{ $contact->first_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nom:</th>
                                            <td>{{ $contact->last_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Organisation:</th>
                                            <td>
                                                @if ($contact->organization)
                                                    <a href="{{ route('external.organizations.show', $contact->organization->id) }}">
                                                        {{ $contact->organization->name }}
                                                    </a>
                                                    @if ($contact->is_primary_contact)
                                                        <span class="badge bg-info">Contact principal</span>
                                                    @endif
                                                @else
                                                    <em>Aucune</em>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Position:</th>
                                            <td>{{ $contact->position ?: 'Non spécifiée' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Statut:</th>
                                            <td>
                                                @if ($contact->is_verified)
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
                                            <td>{{ $contact->email ?: 'Non spécifié' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Téléphone:</th>
                                            <td>{{ $contact->phone ?: 'Non spécifié' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Adresse:</th>
                                            <td>{{ $contact->address ?: 'Non spécifiée' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Courriers envoyés par ce contact</h5>
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
                                    <h5>Courriers reçus par ce contact</h5>
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

                    @if ($contact->notes)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5>Notes</h5>
                                    </div>
                                    <div class="card-body">
                                        {{ $contact->notes }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash"></i> Supprimer ce contact
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
                                    Êtes-vous sûr de vouloir supprimer le contact <strong>{{ $contact->first_name }} {{ $contact->last_name }}</strong> ?
                                    <br><br>
                                    Cette action est irréversible.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <form action="{{ route('external.contacts.destroy', $contact->id) }}" method="POST">
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
