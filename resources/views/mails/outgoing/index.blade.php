@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Courriers sortants</h1>

    <!-- Bandeau de navigation -->
    <div class="d-flex justify-content-start align-items-center bg-light p-2 mb-2 rounded overflow-auto">
        <div class="d-flex align-items-center gap-3 px-2">
            <a href="{{ route('mails.incoming.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers entrants">
                <i class="bi bi-inbox fs-5 text-primary"></i>
                <span class="small">Entrants</span>
            </a>
            <a href="{{ route('mails.outgoing.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers sortants">
                <i class="bi bi-envelope fs-5 text-primary"></i>
                <span class="small">Sortants</span>
            </a>
        </div>

        <div class="ms-auto pe-2">
            <a href="{{ route('mails.outgoing.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nouveau courrier sortant
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

    <!-- Liste des courriers -->
    <div class="card">
        <div class="card-body">
            @if($mails->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Nom</th>
                                <th>Date</th>
                                <th>Typologie</th>
                                <th>Priorité</th>
                                <th>Action</th>
                                <th>Destinataire</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mails as $mail)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $mail->code }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $mail->name }}</strong>
                                        @if($mail->description)
                                            <br><small class="text-muted">{{ Str::limit($mail->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $mail->date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($mail->typology)
                                            <span class="badge bg-info">{{ $mail->typology->name }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mail->priority)
                                            <span class="badge bg-warning">{{ $mail->priority->name }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mail->action)
                                            <span class="badge bg-success">{{ $mail->action->name }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mail->externalRecipient)
                                            {{ $mail->externalRecipient->full_name }}
                                        @elseif($mail->externalRecipientOrganization)
                                            {{ $mail->externalRecipientOrganization->name }}
                                        @else
                                            <span class="text-muted">Non défini</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mail->status)
                                            <span class="badge bg-primary">{{ ucfirst($mail->status->value) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('mails.outgoing.show', $mail->id) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('mails.outgoing.edit', $mail->id) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('mails.outgoing.destroy', $mail->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce courrier ?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $mails->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-envelope fs-1 text-muted"></i>
                    <h5 class="mt-3">Aucun courrier sortant</h5>
                    <p class="text-muted">Aucun courrier sortant n'a été trouvé.</p>
                    <a href="{{ route('mails.outgoing.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Créer le premier courrier sortant
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
