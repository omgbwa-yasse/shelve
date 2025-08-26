@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">
        {{-- En-tête --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-envelope-open text-primary me-2"></i>
                    <h5 class="mb-0">{{ $mail->name ?? 'N/A' }}</h5>
                    @if($mail->priority && $mail->priority->level === 'high')
                        <span class="badge bg-danger ms-2">Urgent</span>
                    @endif
                </div>
                <div class="text-muted small">
                    <span class="me-3">
                        <i class="bi bi-calendar-event me-1"></i>
                        {{ $mail->date ? \Carbon\Carbon::parse($mail->date)->format('d/m/Y') : 'N/A' }}
                    </span>
                    @if($mail->code)
                        <span class="me-3"><i class="bi bi-hash me-1"></i>{{ $mail->code }}</span>
                    @endif
                    @if($mail->status)
                        <span class="badge bg-{{ $mail->status_color ?? 'secondary' }}">
                            {{ $mail->status->value ?? $mail->status }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="btn-group">
                {{-- Bouton retour dynamique selon le type --}}
                @php
                    $backRoute = match(true) {
                        request()->routeIs('*received.external*') => route('mails.received.external.index'),
                        request()->routeIs('*send.external*') => route('mails.send.external.index'),
                        request()->routeIs('*received*') => route('mail-received.index'),
                        request()->routeIs('*send*') => route('mail-send.index'),
                        default => route('mails.index')
                    };

                    $editRoute = match(true) {
                        request()->routeIs('*received.external*') => route('mails.received.external.edit', $mail->id),
                        request()->routeIs('*send.external*') => route('mails.send.external.edit', $mail->id),
                        request()->routeIs('*received*') => route('mail-received.edit', $mail->id),
                        request()->routeIs('*send*') => route('mail-send.edit', $mail->id),
                        default => '#'
                    };
                @endphp

                <a href="{{ $backRoute }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <a href="{{ $editRoute }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

            {{-- Colonne principale --}}
            <div class="row-md-12">
                {{-- Carte informations principales --}}
                <div class="card mb-3">
                    <div class="card-body p-3">
                        {{-- Section expéditeur et destinataire --}}
                        <div class="row g-3 mb-4">
                            {{-- Expéditeur --}}
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="bi bi-person-fill text-primary"></i>
                                        </div>
                                        <small class="text-muted fw-semibold">EXPÉDITEUR</small>
                                    </div>
                                    <div>
                                        {{-- Gestion des différents types d'expéditeurs --}}
                                        @if($mail->sender)
                                            {{-- Expéditeur interne --}}
                                            <div class="fw-semibold">{{ $mail->sender->name ?? 'N/A' }}</div>
                                            @if($mail->senderOrganisation)
                                                <div class="text-muted small">{{ $mail->senderOrganisation->name ?? 'N/A' }}</div>
                                            @endif
                                        @elseif($mail->externalSender)
                                            {{-- Expéditeur externe (contact) --}}
                                            <div class="fw-semibold">
                                                {{ $mail->externalSender->first_name ?? 'N/A' }} {{ $mail->externalSender->last_name ?? '' }}
                                            </div>
                                            @if($mail->externalSenderOrganization)
                                                <div class="text-muted small">{{ $mail->externalSenderOrganization->name ?? 'N/A' }}</div>
                                            @endif
                                        @elseif($mail->externalSenderOrganization)
                                            {{-- Expéditeur externe (organisation) --}}
                                            <div class="fw-semibold">{{ $mail->externalSenderOrganization->name ?? 'N/A' }}</div>
                                        @else
                                            <div class="text-muted">Non défini</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Destinataire --}}
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                            <i class="bi bi-person-check-fill text-success"></i>
                                        </div>
                                        <small class="text-muted fw-semibold">DESTINATAIRE</small>
                                    </div>
                                    <div>
                                        {{-- Gestion des différents types de destinataires --}}
                                        @if($mail->recipient)
                                            {{-- Destinataire interne --}}
                                            <div class="fw-semibold">{{ $mail->recipient->name ?? 'N/A' }}</div>
                                            @if($mail->recipientOrganisation)
                                                <div class="text-muted small">{{ $mail->recipientOrganisation->name ?? 'N/A' }}</div>
                                            @endif
                                        @elseif($mail->externalRecipient)
                                            {{-- Destinataire externe (contact) --}}
                                            <div class="fw-semibold">
                                                {{ $mail->externalRecipient->first_name ?? 'N/A' }} {{ $mail->externalRecipient->last_name ?? '' }}
                                            </div>
                                            @if($mail->externalRecipientOrganization)
                                                <div class="text-muted small">{{ $mail->externalRecipientOrganization->name ?? 'N/A' }}</div>
                                            @endif
                                        @elseif($mail->externalRecipientOrganization)
                                            {{-- Destinataire externe (organisation) --}}
                                            <div class="fw-semibold">{{ $mail->externalRecipientOrganization->name ?? 'N/A' }}</div>
                                        @else
                                            <div class="text-muted">Non défini</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        @if($mail->description)
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-card-text me-1"></i>Description
                                </h6>
                                <div class="bg-light rounded p-3">
                                    {{ $mail->description }}
                                </div>
                            </div>
                        @endif

                        {{-- Pièces jointes --}}
                        @if($mail->attachments && $mail->attachments->count() > 0)
                            <div class="mb-12">
                                <h6 class="text-muted mb-12">
                                    <i class="bi bi-paperclip me-1"></i>
                                    Pièces jointes ({{ $mail->attachments->count() }})
                                </h6>
                                <div class="row g-2">
                                    @foreach($mail->attachments as $attachment)
                                        <div class="col-md-12 col-sm-6 mb-2">
                                            <div class="border rounded p-2 d-flex align-items-center">
                                                <div class="me-2">
                                                    @if(str_starts_with($attachment->mime_type, 'image/'))
                                                        <i class="bi bi-file-earmark-image text-primary"></i>
                                                    @elseif(str_starts_with($attachment->mime_type, 'video/'))
                                                        <i class="bi bi-file-earmark-play text-info"></i>
                                                    @elseif($attachment->mime_type === 'application/pdf')
                                                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                                                    @else
                                                        <i class="bi bi-file-earmark text-secondary"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1 me-2">
                                                    <div class="fw-semibold small">{{ $attachment->name }}</div>
                                                    <div class="text-muted small">
                                                        {{ $attachment->size ? number_format($attachment->size / 1024, 1) . ' KB' : '' }}
                                                    </div>
                                                </div>
                                                <a href="{{ route('mail-attachment.show', [$mail->id, $attachment->id]) }}"
                                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Colonne latérale --}}
                <div class="row">
                    <div class="col-md-12">
                        {{-- Métadonnées --}}
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-info-circle me-1"></i>Informations</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row row-cols-4 g-3 small">
                                    {{-- Code --}}
                                    @if($mail->code)
                                        <div class="col">
                                            <div class="text-muted">Code</div>
                                            <div class="fw-semibold">{{ $mail->code }}</div>
                                        </div>
                                    @endif

                                    {{-- Date --}}
                                    <div class="col">
                                        <div class="text-muted">Date</div>
                                        <div class="fw-semibold">
                                            {{ $mail->date ? \Carbon\Carbon::parse($mail->date)->format('d/m/Y') : 'N/A' }}
                                        </div>
                                    </div>

                                    {{-- Typologie --}}
                                    @if($mail->typology)
                                        <div class="col">
                                            <div class="text-muted">Typologie</div>
                                            <div class="fw-semibold">{{ $mail->typology->name ?? 'N/A' }}</div>
                                        </div>
                                    @endif

                                    {{-- Action --}}
                                    @if($mail->action)
                                        <div class="col">
                                            <div class="text-muted">Action</div>
                                            <div class="fw-semibold">{{ $mail->action->name ?? 'N/A' }}</div>
                                        </div>
                                    @endif

                                    {{-- Priorité --}}
                                    @if($mail->priority)
                                        <div class="col">
                                            <div class="text-muted">Priorité</div>
                                            <div class="fw-semibold">{{ $mail->priority->name ?? 'N/A' }}</div>
                                        </div>
                                    @endif

                                    {{-- Type de document --}}
                                    @if($mail->document_type)
                                        <div class="col">
                                            <div class="text-muted">Type de document</div>
                                            <div class="fw-semibold">{{ ucfirst($mail->document_type) }}</div>
                                        </div>
                                    @endif

                                    {{-- Containers archivés --}}
                                    @if($mail->containers && $mail->containers->count() > 0)
                                        <div class="col">
                                            <div class="text-muted">Archives</div>
                                            <div class="fw-semibold">
                                                {{ $mail->containers->count() }}
                                                {{ $mail->containers->count() > 1 ? 'copies archivées' : 'copie archivée' }}
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Statut --}}
                                    @if($mail->status && $mail->status->value == 'in_progress')
                                        <div class="col">
                                            <div class="text-muted">Statut</div>
                                            <div>
                                                <span class="badge bg-{{ $mail->status_color ?? 'secondary' }}">
                                                    {{  'En cours d\'approbation' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-md-12 mt-3">
                    @if($mail->status && (
                        ($mail->recipient && $mail->recipient->id == auth()->id()) ||
                        ($mail->recipientOrganisation && auth()->user()->currentOrganisation && $mail->recipientOrganisation->id == auth()->user()->currentOrganisation->id)
                    ))
                        <a href="{{ route('mail-received.approve', $mail->id)}}" target="_blank" class="btn btn-success"> Approuver</a>
                        <a href="{{ route('mail-received.reject', $mail->id)}}" target="_blank" class="btn btn-danger "> Rejecter</a>
                    @endif
                </div>



    </div>

    {{-- Modal de suppression --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir supprimer ce courrier ?
                    <br><strong>{{ $mail->code ?? $mail->name }}</strong>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" action="{{
                        match(true) {
                            request()->routeIs('*received.external*') => route('mails.received.external.destroy', $mail->id),
                            request()->routeIs('*send.external*') => route('mails.send.external.destroy', $mail->id),
                            request()->routeIs('*received*') => route('mail-received.destroy', $mail->id),
                            request()->routeIs('*send*') => route('mail-send.destroy', $mail->id),
                            default => '#'
                        }
                    }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
