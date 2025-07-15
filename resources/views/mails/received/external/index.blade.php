@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Courriers reçus externes</h1>

    <!-- Bandeau de navigation -->
    <div class="d-flex justify-content-start align-items-center bg-light p-2 mb-2 rounded overflow-auto">
        <div class="d-flex align-items-center gap-3 px-2">
            <a href="{{ route('mails.received.external.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers reçus externes">
                <i class="bi bi-inbox fs-5 text-primary"></i>
                <span class="small fw-bold">Reçus</span>
            </a>
            <a href="{{ route('mails.send.external.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers envoyés externes">
                <i class="bi bi-envelope fs-5 text-success"></i>
                <span class="small">Envoyés</span>
            </a>
        </div>

        <div class="ms-auto pe-2">
            <a href="{{ route('mails.received.external.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nouveau courrier reçu
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
                                <th>Expéditeur</th>
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
                                    <td>
                                        <small>{{ $mail->date ? \Carbon\Carbon::parse($mail->date)->format('d/m/Y') : 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @if($mail->typology)
                                            <span class="badge bg-info">{{ $mail->typology->name }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mail->externalSender)
                                            <i class="bi bi-person text-primary"></i>
                                            {{ $mail->externalSender->first_name }} {{ $mail->externalSender->last_name }}
                                        @elseif($mail->externalSenderOrganization)
                                            <i class="bi bi-building text-warning"></i>
                                            {{ $mail->externalSenderOrganization->name }}
                                        @elseif($mail->senderOrganisation)
                                            <i class="bi bi-building text-info"></i>
                                            {{ $mail->senderOrganisation->name }}
                                        @else
                                            <span class="text-muted">Non défini</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($mail->status->value ?? '') {
                                                'transmitted' => 'bg-info',
                                                'received' => 'bg-success',
                                                'processing' => 'bg-warning',
                                                'processed' => 'bg-primary',
                                                'archived' => 'bg-secondary',
                                                default => 'bg-light text-dark'
                                            };
                                            $statusText = match($mail->status->value ?? '') {
                                                'transmitted' => 'Transmis',
                                                'received' => 'Reçu',
                                                'processing' => 'En traitement',
                                                'processed' => 'Traité',
                                                'archived' => 'Archivé',
                                                default => 'Inconnu'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('mails.received.external.show', $mail->id) }}"
                                               class="btn btn-outline-primary" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('mails.received.external.edit', $mail->id) }}"
                                               class="btn btn-outline-warning" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('mails.received.external.destroy', $mail->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce courrier ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Supprimer">
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
                <div class="d-flex justify-content-center mt-4">
                    {{ $mails->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <h4 class="text-muted mt-2">Aucun courrier reçu externe</h4>
                    <p class="text-muted">Commencez par enregistrer votre premier courrier reçu externe.</p>
                    <a href="{{ route('mails.received.external.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Enregistrer un courrier reçu
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
