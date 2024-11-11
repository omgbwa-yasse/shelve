@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">
        {{-- En-tête --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-envelope-open text-primary me-2"></i>
                    <h5 class="mb-0">{{ $mail->name ?? 'N/A' }}</h5>
                    @if($mail->importance === 'high')
                        <span class="badge bg-danger ms-2">Urgent</span>
                    @endif
                </div>
                <div class="text-muted small">
                    <span class="me-3"><i class="bi bi-calendar-event me-1"></i>Reçu le {{ date('d/m/Y', strtotime($mail->date)) }}</span>
                    @if($mail->mail_number)
                        <span class="me-3"><i class="bi bi-hash me-1"></i>N° {{ $mail->mail_number }}</span>
                    @endif
                    @if($mail->status)
                        <span class="badge bg-{{ $mail->status_color ?? 'secondary' }}">{{ $mail->status }}</span>
                    @endif
                </div>
            </div>
            <div class="btn-group">
                <a href="{{ route('mail-received.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <a href="{{ route('mail-received.edit', $mail->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        {{-- Corps principal --}}
        <div class="row g-3">
            {{-- Colonne principale --}}
            <div class="col-md-8">
                {{-- Carte informations principales --}}
                <div class="card mb-3">
                    <div class="card-body p-3">
                        {{-- Section expéditeur et destinataire --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="bi bi-person text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Expéditeur</small>
                                            <div class="fw-bold">{{ $mail->sender->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="small">
                                        <div class="text-muted mb-1">{{ $mail->senderOrganisation->name ?? 'N/A' }}</div>
                                        @if($mail->sender_address)
                                            <div><i class="bi bi-geo-alt me-1"></i>{{ $mail->sender_address }}</div>
                                        @endif
                                        @if($mail->sender_email)
                                            <div><i class="bi bi-envelope me-1"></i>{{ $mail->sender_email }}</div>
                                        @endif
                                        @if($mail->sender_phone)
                                            <div><i class="bi bi-telephone me-1"></i>{{ $mail->sender_phone }}</div>
                                        @endif
                                        @if($mail->sender_reference)
                                            <div class="mt-1">
                                                <span class="badge bg-light text-dark">Réf: {{ $mail->sender_reference }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                            <i class="bi bi-building text-success"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Destinataire</small>
                                            <div class="fw-bold">{{ $mail->recipientOrganisation->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="small">
                                        @if($mail->recipient_service)
                                            <div><i class="bi bi-diagram-3 me-1"></i>{{ $mail->recipient_service }}</div>
                                        @endif
                                        @if($mail->recipient_department)
                                            <div><i class="bi bi-grid me-1"></i>{{ $mail->recipient_department }}</div>
                                        @endif
                                        @if($mail->recipient_name)
                                            <div class="mt-1">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-person me-1"></i>{{ $mail->recipient_name }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section détails --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3">Détails du courrier</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <table class="table table-sm m-0">
                                                <tbody>
                                                <tr>
                                                    <td class="text-muted" style="width: 40%">Affaire</td>
                                                    <td>{{ $mail->typology->name ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Type</td>
                                                    <td>{{ $mail->document_type ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Catégorie</td>
                                                    <td>{{ $mail->category ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Nature</td>
                                                    <td>{{ $mail->nature ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Canal</td>
                                                    <td>{{ $mail->reception_channel ?? 'N/A' }}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm m-0">
                                                <tbody>
                                                <tr>
                                                    <td class="text-muted" style="width: 40%">Action</td>
                                                    <td>
                                                        <span class="badge bg-primary">{{ $mail->action->name ?? 'N/A' }}</span>
                                                        @if($mail->due_date)
                                                            <div class="small text-muted mt-1">
                                                                Échéance: {{ date('d/m/Y', strtotime($mail->due_date)) }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Importance</td>
                                                    <td>{{ $mail->importance ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Sous-catégorie</td>
                                                    <td>{{ $mail->subcategory ?? 'N/A' }}</td>
                                                </tr>
                                                @if($mail->keywords)
                                                    <tr>
                                                        <td class="text-muted">Mots-clés</td>
                                                        <td>
                                                            @foreach(explode(',', $mail->keywords) as $keyword)
                                                                <span class="badge bg-light text-dark me-1">{{ trim($keyword) }}</span>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section description et notes --}}
                        @if($mail->description || $mail->comments || $mail->observations || $mail->processing_notes)
                            <div class="border rounded p-3 mt-3">
                                <h6 class="mb-3">Notes et observations</h6>
                                @if($mail->description)
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">Description</div>
                                        <div>{{ $mail->description }}</div>
                                    </div>
                                @endif
                                @if($mail->comments)
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">Commentaires</div>
                                        <div>{{ $mail->comments }}</div>
                                    </div>
                                @endif
                                @if($mail->observations)
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">Observations</div>
                                        <div>{{ $mail->observations }}</div>
                                    </div>
                                @endif
                                @if($mail->processing_notes)
                                    <div>
                                        <div class="text-muted small mb-1">Notes de traitement</div>
                                        <div>{{ $mail->processing_notes }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Pièces jointes --}}
                @if($mail->attachments && count($mail->attachments) > 0)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h6 class="mb-0">Pièces jointes ({{ count($mail->attachments) }})</h6>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i>Tout télécharger
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Taille</th>
                                    <th>Ajouté le</th>
                                    <th>Par</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($mail->attachments as $attachment)
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-earmark me-1"></i>
                                            {{ $attachment->filename }}
                                        </td>
                                        <td><small>{{ $attachment->filetype }}</small></td>
                                        <td><small>{{ $attachment->filesize }}</small></td>
                                        <td><small>{{ $attachment->created_at->format('d/m/Y H:i') }}</small></td>
                                        <td><small>{{ $attachment->user->name }}</small></td>
                                        <td class="text-end">
                                            <a href="{{ route('attachments.view', $attachment->id) }}"
                                               class="btn btn-sm btn-link" target="_blank">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('attachments.download', $attachment->id) }}"
                                               class="btn btn-sm btn-link">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Historique --}}
                @if($mail->history && $mail->history->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0">Historique</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Utilisateur</th>
                                    <th>Commentaire</th>
                                    <th>Statut</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($mail->history as $history)
                                    <tr>
                                        <td><small>{{ $history->created_at->format('d/m/Y H:i') }}</small></td>
                                        <td>{{ $history->action }}</td>
                                        <td><small>{{ $history->user->name }}</small></td>
                                        <td>{{ $history->comment }}</td>
                                        <td>
                                            @if($history->status)
                                                <span class="badge bg-{{ $history->status_color }}">
                                                        {{ $history->status }}
                                                    </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Réponses et transferts --}}
                @if($mail->replies && count($mail->replies) > 0)
                    <div class="card">
                        <div class="card-header py-2">
                            <h6 class="mb-0">Suivi des réponses</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Destinataire</th>
                                    <th>Envoyé par</th>
                                    <th>Statut</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($mail->replies as $reply)
                                    <tr>
                                        <td><small>{{ $reply->created_at->format('d/m/Y H:i') }}</small></td>
                                        <td>
                                            @if($reply->type === 'reply')
                                                <i class="bi bi-reply me-1 text-primary"></i>Réponse
                                            @else
                                                <i class="bi bi-forward me-1 text-success"></i>Transfert
                                            @endif
                                        </td>
                                        <td>{{ $reply->recipient }}</td>
                                        <td><small>{{ $reply->user->name }}</small></td>
                                        <td>
                                                <span class="badge bg-{{ $reply->status_color }}">
                                                    {{ $reply->status }}
                                                </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('mail-received.show', $reply->id) }}"
                                               class="btn btn-sm btn-link">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Colonne latérale --}}
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 1rem;">
                    <div class="card-header py-2">
                        <h6 class="mb-0">Actions rapides</h6>
                    </div>
                    <div class="list-group list-group-flush">

                        <a href=""
                           class="list-group-item list-group-item-action py-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-forward text-success me-3"></i>
                                <div>Transférer</div>
                            </div>
                        </a>
                        <a href=""
                           class="list-group-item list-group-item-action py-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-archive text-warning me-3"></i>
                                <div>Archiver</div>
                            </div>
                        </a>
                        <button onclick="window.print()"
                                class="list-group-item list-group-item-action py-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-printer text-secondary me-3"></i>
                                <div>Imprimer</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de suppression --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Confirmation</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Êtes-vous sûr de vouloir supprimer ce courrier ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('mail-received.destroy', $mail->id) }}" method="POST" class="d-inline">
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
            .card {
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .rounded-circle {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .list-group-item {
                transition: all 0.2s;
            }
            .list-group-item:hover {
                background-color: rgba(0,0,0,0.02);
                padding-left: 1.25rem;
            }
            .table > :not(caption) > * > * {
                padding: 0.5rem;
            }
            .btn-link {
                color: inherit;
                text-decoration: none;
            }
            .btn-link:hover {
                color: var(--bs-primary);
            }
            @media print {
                .col-md-4, .modal, .btn-group {
                    display: none !important;
                }
                .col-md-8 {
                    width: 100% !important;
                }
                .card {
                    border: none !important;
                    box-shadow: none !important;
                }
            }
        </style>
    @endpush
@endsection
