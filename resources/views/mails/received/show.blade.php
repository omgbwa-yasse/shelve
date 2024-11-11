@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">
        {{-- En-tête avec statut et informations principales --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-envelope-open text-primary"></i>
                    <h5 class="mb-0">Courrier entrant : {{ $mail->name ?? 'N/A' }}</h5>
                    @if($mail->priority === 'high')
                        <span class="badge bg-danger">Urgent</span>
                    @elseif($mail->priority === 'medium')
                        <span class="badge bg-warning text-dark">Important</span>
                    @endif
                    <span class="badge bg-{{ $mail->status_color ?? 'secondary' }}">{{ $mail->status ?? 'Nouveau' }}</span>
                    @if($mail->confidentiality === 'confidential')
                        <span class="badge bg-danger"><i class="bi bi-lock-fill me-1"></i>Confidentiel</span>
                    @endif
                </div>
                <div class="text-muted small">
                <span class="me-3">
                    <i class="bi bi-calendar-event me-1"></i>
                    Reçu le {{ date('d/m/Y à H:i', strtotime($mail->date)) }}
                </span>
                    @if($mail->reference)
                        <span class="me-3">
                        <i class="bi bi-hash me-1"></i>
                        Réf: {{ $mail->reference }}
                    </span>
                    @endif
                    @if($mail->channel)
                        <span class="me-3">
                        <i class="bi bi-envelope me-1"></i>
                        Via: {{ $mail->channel }}
                    </span>
                    @endif
                    @if($mail->tracking_number)
                        <span>
                        <i class="bi bi-truck me-1"></i>
                        Suivi: {{ $mail->tracking_number }}
                    </span>
                    @endif
                </div>
            </div>

            <div class="btn-group">
                <a href="{{ route('mail-received.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                </button>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a href="{{ route('mail-received.edit', $mail->id) }}" class="dropdown-item">
                                <i class="bi bi-pencil me-2"></i> Modifier
                            </a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#historyModal">
                                <i class="bi bi-clock-history me-2"></i> Historique complet
                            </a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item" onclick="copyReference()">
                                <i class="bi bi-clipboard me-2"></i> Copier la référence
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="bi bi-trash me-2"></i> Supprimer
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Informations principales --}}
            <div class="col-md-8">
                <div class="row g-3">
                    {{-- Carte des informations principales --}}
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Informations principales</h6>
                                @if($mail->last_updated)
                                    <small class="text-muted">
                                        Dernière mise à jour : {{ $mail->last_updated->diffForHumans() }}
                                    </small>
                                @endif
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex p-2 border rounded bg-light position-relative">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                                                    <i class="bi bi-person text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="ms-2">
                                                <small class="text-muted d-block">Expéditeur</small>
                                                <strong>{{ $mail->sender->name ?? 'N/A' }}</strong>
                                                @if($mail->sender->position)
                                                    <small class="text-muted d-block">{{ $mail->sender->position }}</small>
                                                @endif
                                                @if($mail->sender->email)
                                                    <a href="mailto:{{ $mail->sender->email }}" class="d-block text-muted small">
                                                        <i class="bi bi-envelope-fill me-1"></i>{{ $mail->sender->email }}
                                                    </a>
                                                @endif
                                                @if($mail->sender->phone)
                                                    <a href="tel:{{ $mail->sender->phone }}" class="d-block text-muted small">
                                                        <i class="bi bi-telephone-fill me-1"></i>{{ $mail->sender->phone }}
                                                    </a>
                                                @endif
                                                <small class="text-muted d-block mt-1">
                                                    <i class="bi bi-building me-1"></i>{{ $mail->senderOrganisation->name ?? 'N/A' }}
                                                </small>
                                                @if($mail->senderOrganisation->address)
                                                    <small class="text-muted d-block">
                                                        <i class="bi bi-geo-alt me-1"></i>{{ $mail->senderOrganisation->address }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex p-2 border rounded bg-light">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle bg-success bg-opacity-10 p-2">
                                                    <i class="bi bi-building text-success"></i>
                                                </div>
                                            </div>
                                            <div class="ms-2">
                                                <small class="text-muted d-block">Organisation destinataire</small>
                                                <strong>{{ $mail->recipientOrganisation->name ?? 'N/A' }}</strong>
                                                @if($mail->service)
                                                    <small class="text-muted d-block">
                                                        <i class="bi bi-diagram-3 me-1"></i>Service : {{ $mail->service }}
                                                    </small>
                                                @endif
                                                @if($mail->recipient)
                                                    <small class="text-muted d-block">
                                                        <i class="bi bi-person me-1"></i>À l'attention de : {{ $mail->recipient }}
                                                    </small>
                                                @endif
                                                @if($mail->internal_reference)
                                                    <small class="text-muted d-block">
                                                        <i class="bi bi-hash me-1"></i>Référence interne : {{ $mail->internal_reference }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                        <tr>
                                            <td style="width: 150px;"><small class="text-muted">Affaire</small></td>
                                            <td class="d-flex align-items-center">
                                                <span class="me-2">{{ $mail->typology->name ?? 'N/A' }}</span>
                                                @if($mail->typology->description)
                                                    <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip"
                                                       title="{{ $mail->typology->description }}"></i>
                                                @endif
                                                @if($mail->linked_cases_count)
                                                    <span class="badge bg-info ms-2">
                                                        {{ $mail->linked_cases_count }} affaire(s) liée(s)
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><small class="text-muted">Type de document</small></td>
                                            <td>
                                                {{ $mail->document_type ?? 'N/A' }}
                                                @if($mail->document_language)
                                                    <span class="badge bg-light text-dark ms-2">
                                                        <i class="bi bi-translate me-1"></i>{{ $mail->document_language }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><small class="text-muted">Action requise</small></td>
                                            <td>
                                                <span class="badge bg-primary">{{ $mail->action->name ?? 'N/A' }}</span>
                                                @if($mail->due_date)
                                                    <span class="ms-2 {{ strtotime($mail->due_date) < time() ? 'text-danger' : 'text-muted' }}">
                                                        <i class="bi bi-calendar-event me-1"></i>
                                                        Échéance : {{ date('d/m/Y', strtotime($mail->due_date)) }}
                                                        @if(strtotime($mail->due_date) < time())
                                                            (dépassée)
                                                        @endif
                                                    </span>
                                                @endif
                                                @if($mail->assigned_to)
                                                    <span class="ms-2 text-muted">
                                                        <i class="bi bi-person me-1"></i>
                                                        Assigné à : {{ $mail->assigned_to->name }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($mail->description)
                                            <tr>
                                                <td><small class="text-muted">Description</small></td>
                                                <td>{{ $mail->description }}</td>
                                            </tr>
                                        @endif
                                        @if($mail->keywords)
                                            <tr>
                                                <td><small class="text-muted">Mots-clés</small></td>
                                                <td>
                                                    @foreach(explode(',', $mail->keywords) as $keyword)
                                                        <a href="{{ route('mail-received.index', ['keyword' => trim($keyword)]) }}"
                                                           class="badge bg-light text-dark text-decoration-none me-1">
                                                            {{ trim($keyword) }}
                                                        </a>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endif
                                        @if($mail->notes)
                                            <tr>
                                                <td><small class="text-muted">Notes internes</small></td>
                                                <td>
                                                    <div class="p-2 bg-light rounded border">
                                                        {!! nl2br(e($mail->notes)) !!}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pièces jointes --}}
                    @if($mail->attachments && count($mail->attachments) > 0)
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Pièces jointes ({{ count($mail->attachments) }})</h6>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i> Tout télécharger
                                    </button>
                                </div>
                                <div class="list-group list-group-flush">
                                    @foreach($mail->attachments as $attachment)
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-{{ $attachment->icon }} text-muted me-2"></i>
                                                <div>
                                                    <div>{{ $attachment->name }}</div>
                                                    <small class="text-muted">
                                                        {{ $attachment->size_formatted }} ·
                                                        Ajouté {{ $attachment->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('attachments.preview', $attachment->id) }}"
                                                   class="btn btn-outline-secondary" target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('attachments.download', $attachment->id) }}"
                                                   class="btn btn-outline-primary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Relations et liens --}}
                    @if($mail->relations && count($mail->relations) > 0)
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-2">
                                    <h6 class="mb-0">Documents liés</h6>
                                </div>
                                <div class="list-group list-group-flush">
                                    @foreach($mail->relations as $relation)
                                        <div class="list-group-item py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-{{ $relation->type_icon }} text-{{ $relation->type_color }} me-2"></i>
                                                        <div>
                                                            <a href="{{ $relation->url }}" class="text-decoration-none">
                                                                {{ $relation->name }}
                                                            </a>
                                                            <span class="badge bg-light text-dark ms-2">{{ $relation->type_label }}</span>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted d-block">
                                                        Ref: {{ $relation->reference }} ·
                                                        {{ $relation->date->format('d/m/Y') }}
                                                    </small>
                                                </div>
                                                <div class="badge bg-{{ $relation->status_color }}">
                                                    {{ $relation->status }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Colonne de droite --}}
            <div class="col-md-4">
                {{-- Statut et progression --}}
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h6 class="mb-0">Statut et progression</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small>Progression</small>
                                <small class="text-muted">{{ $mail->progress }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: {{ $mail->progress }}%"></div>
                            </div>
                        </div>
                        <div class="small">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Statut actuel</span>
                                <span class="badge bg-{{ $mail->status_color }}">{{ $mail->status }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Temps de traitement</span>
                                <span>{{ $mail->processing_time ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Dernière activité</span>
                                <span>{{ $mail->last_activity ? $mail->last_activity->diffForHumans() : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions rapides --}}
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h6 class="mb-0">Actions rapides</h6>
                    </div>
                    <div class="list-group list-group-flush">

                        <a href="">
                           class="list-group-item list-group-item-action py-2">
                            <i class="bi bi-forward me-2"></i> Transférer
                        </a>
                        <button class="list-group-item list-group-item-action py-2"
                                data-bs-toggle="modal" data-bs-target="#assignModal">
                            <i class="bi bi-person-plus me-2"></i> Assigner
                            @if($mail->assigned_to)
                                <small class="text-muted float-end">{{ $mail->assigned_to->name }}</small>
                            @endif
                        </button>
                        <button class="list-group-item list-group-item-action py-2"
                                data-bs-toggle="modal" data-bs-target="#statusModal">
                            <i class="bi bi-check2-square me-2"></i> Changer le statut
                        </button>
                        <button class="list-group-item list-group-item-action py-2"
                                data-bs-toggle="modal" data-bs-target="#reminderModal">
                            <i class="bi bi-bell me-2"></i> Définir un rappel
                        </button>
                        <a href=""
                           class="list-group-item list-group-item-action py-2">
                            <i class="bi bi-archive me-2"></i> Archiver
                        </a>
                    </div>
                </div>

                {{-- Commentaires --}}
                @if($mail->comments && count($mail->comments) > 0)
                    <div class="card mb-3">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Commentaires ({{ count($mail->comments) }})</h6>
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#commentModal">
                                <i class="bi bi-plus"></i> Ajouter
                            </button>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach($mail->comments as $comment)
                                <div class="list-group-item p-2">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle bg-light p-2">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        </div>
                                        <div class="ms-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong class="small">{{ $comment->user->name }}</strong>
                                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                            <div class="small mt-1">{{ $comment->content }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Historique --}}
                <div class="card">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Activités récentes</h6>
                        <button class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#historyModal">
                            Voir tout
                        </button>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($mail->history->take(5) as $event)
                            <div class="list-group-item py-2">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle bg-light p-1">
                                            <i class="bi bi-{{ $event->icon }} text-{{ $event->color }}"></i>
                                        </div>
                                    </div>
                                    <div class="ms-2">
                                        <small>{{ $event->description }}</small>
                                        <small class="text-muted d-block">
                                            {{ $event->user->name }} · {{ $event->created_at->diffForHumans() }}
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

    {{-- Modales pour les différentes actions --}}
    @include('mail-received.partials.modals.delete')
    @include('mail-received.partials.modals.assign')
    @include('mail-received.partials.modals.status')
    @include('mail-received.partials.modals.reminder')
    @include('mail-received.partials.modals.comment')
    @include('mail-received.partials.modals.history')

    @push('scripts')
        <script>
            // Fonction pour copier la référence
            function copyReference() {
                navigator.clipboard.writeText('{{ $mail->reference }}')
                    .then(() => {
                        // Feedback visuel (toast ou notification)
                    });
            }

            // Initialisation des tooltips Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        </script>
    @endpush

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
            @media print {
                .btn-group, .list-group-item-action, .modal {
                    display: none !important;
                }
                .card {
                    border: none !important;
                    box-shadow: none !important;
                }
                .container-fluid {
                    width: 100% !important;
                    padding: 0 !important;
                }
            }
        </style>
    @endpush
