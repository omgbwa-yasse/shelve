@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">
        {{-- Retour des actions du workflow (cotation, réception, réponse, signature...) --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check2-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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
                        <span class="badge bg-{{ $mail->status->bootstrapColor() }}">
                            {{ $mail->status->label() }}
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <form action="{{ route('mail-transaction.print') }}" method="POST" target="_blank" style="display:inline; margin-right: 1rem;">
                    @csrf
                    <input type="hidden" name="selectedIds[]" value="{{ $mail->id }}">
                    <button type="submit" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-printer"></i> Imprimer PDF
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#mailDetailsModal">
                    <i class="bi bi-eye"></i> Générer une description
                </button>
                <!-- Modal Génération Description Courrier -->
                <div class="modal fade" id="mailDetailsModal" tabindex="-1" aria-labelledby="mailDetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="mailDetailsModalLabel">Générer une description du courrier</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="mail-description-result">
                                    <div class="text-muted mb-2">Cliquez sur le bouton ci-dessous pour générer une description synthétique et professionnelle du courrier, avec mots-clés archivistiques.</div>
                                    <div class="alert alert-info" id="mail-description-loading" style="display:none;">
                                        <span class="spinner-border spinner-border-sm me-2"></span> Génération en cours...
                                    </div>
                                    <div id="mail-description-content" style="min-height:80px;"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-primary" id="generateMailDescriptionBtn">
                                    <i class="bi bi-magic"></i> Générer la description
                                </button>
                                <button type="button" class="btn btn-success" id="saveMailDescriptionBtn" style="display:none;">
                                    <i class="bi bi-floppy"></i> Enregistrer
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
        <!-- Script AJAX génération description -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateBtn = document.getElementById('generateMailDescriptionBtn');
            const saveBtn = document.getElementById('saveMailDescriptionBtn');
            const loading = document.getElementById('mail-description-loading');
            const content = document.getElementById('mail-description-content');
            let currentSummary = '';

            generateBtn.addEventListener('click', function() {
                generateBtn.disabled = true;
                loading.style.display = 'block';
                content.innerHTML = '';
                saveBtn.style.display = 'none';

                fetch(`{{ route('mail.summarize', $mail->id) }}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.style.display = 'none';
                        generateBtn.disabled = false;
                        if(data.summary) {
                            currentSummary = data.summary;
                            content.innerHTML = `<strong>Résumé :</strong><br>${data.summary}<hr><strong>Mots-clés :</strong><br>${data.keywords}`;
                            saveBtn.style.display = 'inline-block';
                        } else {
                            content.innerHTML = '<span class="text-danger">Erreur lors de la génération.</span>';
                        }
                    })
                    .catch(() => {
                        loading.style.display = 'none';
                        generateBtn.disabled = false;
                        content.innerHTML = '<span class="text-danger">Erreur lors de la génération.</span>';
                    });
            });

            saveBtn.addEventListener('click', function() {
                if (!currentSummary) {
                    alert('Aucun résumé à sauvegarder');
                    return;
                }

                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sauvegarde...';

                fetch(`{{ route('mail.saveSummary', $mail->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        summary: currentSummary
                    })
                })
                .then(response => response.json())
                .then(data => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-floppy"></i> Enregistrer';

                    if(data.success) {
                        saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Sauvegardé';
                        saveBtn.classList.remove('btn-success');
                        saveBtn.classList.add('btn-outline-success');

                        // Optionnel: recharger la page pour voir les changements
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        alert('Erreur lors de la sauvegarde');
                    }
                })
                .catch(() => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-floppy"></i> Enregistrer';
                    alert('Erreur lors de la sauvegarde');
                });
            });
        });
        </script>
            </div>
            <div class="btn-group">
                {{-- Bouton retour dynamique selon le type --}}
                @php
                    $backRoute = match(true) {
                        request()->routeIs('*received.external*') => route('mails.received.external.index'),
                        request()->routeIs('*send.external*') => route('mails.send.external.index'),
                        request()->routeIs('*received*') => route('mail-received.index'),
                        request()->routeIs('*send*') => route('mail-send.index'),
                        request()->routeIs('*incoming*') => route('mails.incoming.index'),
                        request()->routeIs('*outgoing*') => route('mails.outgoing.index'),
                        // Repli sur la liste correspondant au type de courrier affiché.
                        ($type ?? null) === 'send' => route('mail-send.index'),
                        default => route('mail-received.index'),
                    };

                    // Les fiches ouvertes depuis le tableau de bord passent par les routes
                    // « incoming » / « outgoing » : sans branche dédiée, le bouton Modifier
                    // retombait sur '#' et ne menait nulle part.
                    $editRoute = match(true) {
                        request()->routeIs('*received.external*') => route('mails.received.external.edit', $mail->id),
                        request()->routeIs('*send.external*') => route('mails.send.external.edit', $mail->id),
                        request()->routeIs('*received*') => route('mail-received.edit', $mail->id),
                        request()->routeIs('*send*') => route('mail-send.edit', $mail->id),
                        request()->routeIs('*incoming*') => $mail->sender_type === 'user'
                            ? route('mail-received.edit', $mail->id)
                            : route('mails.received.external.edit', $mail->id),
                        request()->routeIs('*outgoing*') => $mail->recipient_type === 'user' || $mail->recipient_type === 'organisation'
                            ? route('mail-send.edit', $mail->id)
                            : route('mails.send.external.edit', $mail->id),
                        default => route('mail-send.edit', $mail->id),
                    };
                @endphp

                <a href="{{ $backRoute }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <a href="{{ $editRoute }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i> Supprimer
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
                                            {{-- Expéditeur interne (personne précise) --}}
                                            <div class="fw-semibold">{{ $mail->sender->name ?? 'N/A' }}</div>
                                            @if($mail->senderOrganisation)
                                                <div class="text-muted small">{{ $mail->senderOrganisation->name ?? 'N/A' }}</div>
                                            @endif
                                        @elseif($mail->senderOrganisation)
                                            {{-- Expéditeur interne (organisation/service, sans personne précise) --}}
                                            <div class="fw-semibold">{{ $mail->senderOrganisation->name ?? 'N/A' }}</div>
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
                                            {{-- Destinataire interne (personne précise) --}}
                                            <div class="fw-semibold">{{ $mail->recipient->name ?? 'N/A' }}</div>
                                            @if($mail->recipientOrganisation)
                                                <div class="text-muted small">{{ $mail->recipientOrganisation->name ?? 'N/A' }}</div>
                                            @endif
                                        @elseif($mail->recipientOrganisation)
                                            {{-- Destinataire interne (organisation/service, sans personne précise) --}}
                                            <div class="fw-semibold">{{ $mail->recipientOrganisation->name ?? 'N/A' }}</div>
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

                                    {{-- Activité du plan de classement --}}
                                    @if($mail->activity)
                                        <div class="col">
                                            <div class="text-muted">Activité (plan de classement)</div>
                                            <div class="fw-semibold">{{ $mail->activity->name }}</div>
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
                                    @if($mail->status)
                                        <div class="col">
                                            <div class="text-muted">Statut</div>
                                            <div>
                                                <span class="badge bg-{{ $mail->status->bootstrapColor() }}">
                                                    {{ $mail->status->label() }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Les actions de traitement du courrier sont gérées par le workflow ci-dessous
                     (cotation, validation de réception, soumission, signature DG). --}}

                {{-- Actions du workflow courrier « zéro papier » --}}
                @php
                    $u = auth()->user();
                    $isDg = $u && ($u->isSuperAdmin() || $u->hasRoleInOrganisation('DG', $u->current_organisation_id));
                    $statusVal = $mail->status?->value;
                    // L'utilisateur courant est-il le validateur attendu à ce niveau ?
                    // Pendant la remontée, le validateur courant est dans assigned_to.
                    $isN1 = $statusVal === 'pending_review'
                        && (int) $mail->assigned_to === (int) $u->id;
                    // Repli : supérieur direct de l'initiateur si assigned_to est vide.
                    if (!$isN1 && $statusVal === 'pending_review' && !$mail->assigned_to && $mail->sender_user_id) {
                        $senderUser = \App\Models\User::find($mail->sender_user_id);
                        $sup = $senderUser?->hierarchicalSuperior($mail->sender_organisation_id);
                        $isN1 = $sup && (int) $sup->id === (int) $u->id;
                    }
                    $isInitiator = (int) $mail->sender_user_id === (int) $u->id
                        || (int) $mail->sender_organisation_id === (int) $u->current_organisation_id;
                    // Cotations multi-directions
                    $cotations = $mail->cotations()->with('organisation', 'action', 'activity', 'assignedTo')->get();
                    // Directions dont cet utilisateur a la charge : la sienne, ou celle
                    // d'un intérim dont le volet couvre l'activité de ce courrier.
                    $handledOrgIds = $mail->organisationsHandledBy($u);
                    $myCotation = $cotations->first(fn ($c) => in_array((int) $c->organisation_id, $handledOrgIds, true));
                    // Volets d'intérim de l'utilisateur sur les directions cotées (affichage).
                    $myInterimVolets = \App\Models\OrganisationInterim::activeVoletsOf($u->id)
                        ->whereIn('organisation_id', $cotations->pluck('organisation_id')->all());
                @endphp
                <div class="col-md-12 mt-3">
                    <div class="d-flex flex-wrap gap-2">

                        {{-- Courrier ENTRANT : cotation par le DG (une ou plusieurs directions) --}}
                        @if($mail->mail_type === 'incoming' && $isDg && $statusVal !== 'completed')
                            <a href="{{ route('mails.workflow.cote-form', $mail->id) }}" class="btn btn-primary">
                                <i class="bi bi-diagram-3"></i>
                                {{ $cotations->isEmpty() ? 'Coter (affecter à des directions)' : 'Modifier / ajouter des directions' }}
                            </a>
                        @endif

                        {{-- Courrier ENTRANT coté : validation de réception par la direction dont j'ai la charge --}}
                        @if($mail->mail_type === 'incoming' && $myCotation && $myCotation->status !== 'completed')
                            <form action="{{ route('mails.workflow.confirm-reception', $mail->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check2-circle"></i>
                                    Valider la réception
                                    @if((int) $myCotation->organisation_id !== (int) $u->current_organisation_id)
                                        (intérim {{ $myCotation->organisation->code ?? '' }})
                                    @else
                                        (ma direction)
                                    @endif
                                </button>
                            </form>
                        @elseif($mail->mail_type === 'incoming' && !$cotations->count() && $mail->assigned_organisation_id
                                && in_array((int) $mail->assigned_organisation_id, $handledOrgIds, true) && $statusVal !== 'completed')
                            {{-- Rétro-compat : courrier coté avant la multi-cotation --}}
                            <form action="{{ route('mails.workflow.confirm-reception', $mail->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check2-circle"></i> Valider la réception
                                </button>
                            </form>
                        @endif

                        {{-- Courrier SORTANT initié par le DG : il signe et transmet directement
                             (aucune validation intermédiaire nécessaire). --}}
                        @if($mail->mail_type === 'outgoing' && in_array($statusVal, ['draft', 'in_progress']) && $isDg && $isInitiator)
                            <form action="{{ route('mails.workflow.sign', $mail->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-pen"></i> Signer et transmettre
                                </button>
                            </form>

                        {{-- Courrier SORTANT ou INTERNE en cours (agent/directeur) : soumettre pour validation --}}
                        @elseif(in_array($mail->mail_type, ['outgoing', 'internal']) && in_array($statusVal, ['draft', 'in_progress']) && $isInitiator && !$mail->assigned_organisation_id)
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#submitModal">
                                <i class="bi bi-send"></i> Soumettre pour validation
                            </button>
                        @endif

                        {{-- Courrier SORTANT / INTERNE en revue N+1 : validation / renvoi par le supérieur --}}
                        @if(in_array($mail->mail_type, ['outgoing', 'internal']) && $statusVal === 'pending_review' && ($isN1 || $isDg))
                            <form action="{{ route('mails.workflow.validate-n1', $mail->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check2-all"></i>
                                    {{ $mail->mail_type === 'internal' ? 'Valider (N+1) et transmettre au service' : 'Valider (N+1) et transmettre au DG' }}
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#revisionModal">
                                <i class="bi bi-arrow-counterclockwise"></i> Renvoyer pour révision
                            </button>
                        @endif

                        {{-- Courrier INTERNE livré au service destinataire : le responsable affecte à un agent --}}
                        @if($mail->mail_type === 'internal' && $mail->assigned_organisation_id
                            && (int) $mail->assigned_organisation_id === (int) $u->current_organisation_id
                            && !$mail->assigned_to && $statusVal !== 'completed')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
                                <i class="bi bi-person-check"></i> Affecter à un agent
                            </button>
                        @endif

                        {{-- Courrier INTERNE affecté : l'agent (ou le service) valide la réception --}}
                        @if($mail->mail_type === 'internal' && $mail->assigned_organisation_id
                            && (int) $mail->assigned_organisation_id === (int) $u->current_organisation_id
                            && $mail->assigned_to && $statusVal !== 'completed')
                            <form action="{{ route('mails.workflow.confirm-reception', $mail->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check2-circle"></i> Valider la réception / traitement
                                </button>
                            </form>
                        @endif

                        {{-- Courrier SORTANT soumis au DG : signature / renvoi / rejet --}}
                        @if($mail->mail_type === 'outgoing' && $statusVal === 'pending_approval' && $isDg)
                            <form action="{{ route('mails.workflow.sign', $mail->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-pen"></i> Signer et transmettre
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#revisionModal">
                                <i class="bi bi-arrow-counterclockwise"></i> Renvoyer pour révision
                            </button>
                            <form action="{{ route('mails.workflow.reject', $mail->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-x-circle"></i> Rejeter
                                </button>
                            </form>
                        @endif

                        {{-- Courrier SORTANT ou note interne renvoyé pour révision : l'initiateur corrige et resoumet --}}
                        @if(in_array($mail->mail_type, ['outgoing', 'internal']) && $statusVal === 'rejected' && $isInitiator)
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#resubmitModal">
                                <i class="bi bi-arrow-repeat"></i> Corriger et resoumettre
                            </button>
                        @endif

                        {{-- Répondre / donner suite : crée un courrier rattaché à celui-ci --}}
                        @php
                            // Un intérimaire ne répond qu'aux courriers de son volet :
                            // organisationsHandledBy() applique déjà cette restriction.
                            $canReply = $isDg
                                || (int) $mail->sender_user_id === (int) $u->id
                                || !empty($handledOrgIds);
                        @endphp
                        @if($canReply && $statusVal !== 'draft')
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#replyModal">
                                <i class="bi bi-reply"></i> Répondre / donner suite
                            </button>
                        @endif
                    </div>

                    {{-- L'utilisateur agit ici au titre d'un intérim : on rappelle son volet --}}
                    @if($myInterimVolets->count())
                        @foreach($myInterimVolets as $volet)
                            <div class="alert alert-info mt-3 mb-0 py-2">
                                <i class="bi bi-person-badge"></i>
                                Vous intervenez sur ce courrier au titre de votre <strong>intérim</strong> à
                                « {{ $volet->organisation->name ?? '' }} » —
                                volet <strong>{{ $volet->voletLabel() }}</strong>.
                                @unless($volet->coversActivity($mail->activityForOrganisation((int) $volet->organisation_id)))
                                    <span class="d-block small">
                                        Ce courrier relève d'une autre activité : il est traité par l'autre intérimaire.
                                    </span>
                                @endunless
                            </div>
                        @endforeach
                    @endif

                    {{-- Chaînage des courriers : d'où vient ce courrier, ce qu'il a produit --}}
                    @php
                        $parentMail = $mail->parentMail;
                        $replies = $mail->replies()->orderBy('id')->get();
                        $showRoute = fn ($m) => $m->mail_type === 'incoming'
                            ? route('mails.incoming.show', $m->id)
                            : ($m->mail_type === 'outgoing' ? route('mails.outgoing.show', $m->id) : route('mail-send.show', $m->id));
                    @endphp

                    @if($parentMail)
                        <div class="alert alert-secondary mt-3 mb-0">
                            <i class="bi bi-arrow-90deg-up"></i>
                            En réponse à :
                            <a href="{{ $showRoute($parentMail) }}"><strong>{{ $parentMail->code }}</strong> — {{ $parentMail->name }}</a>
                        </div>
                    @endif

                    @if($replies->count())
                        <div class="mt-3">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-diagram-2"></i> Suites données à ce courrier
                                <span class="badge bg-secondary">{{ $replies->count() }}</span>
                            </h6>
                            <ul class="list-group list-group-flush">
                                @foreach($replies as $reply)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>
                                            <a href="{{ $showRoute($reply) }}"><strong>{{ $reply->code }}</strong></a>
                                            — {{ $reply->name }}
                                            <span class="text-muted small">
                                                ({{ $reply->mail_type === 'outgoing' ? 'courrier sortant' : 'courrier interne' }},
                                                créé le {{ optional($reply->created_at)->format('d/m/Y') }})
                                            </span>
                                        </span>
                                        <span class="badge bg-{{ $reply->status->bootstrapColor() }}">{{ $reply->status->label() }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Suivi de la cotation par direction : le DG suit chaque réponse individuellement --}}
                    @if($mail->mail_type === 'incoming' && $cotations->count())
                        <div class="mt-3">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-diagram-3"></i> Suivi de la cotation
                                @php $doneCount = $cotations->where('status', 'completed')->count(); @endphp
                                <span class="badge bg-secondary">{{ $doneCount }}/{{ $cotations->count() }} direction(s) ont validé</span>
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Direction</th>
                                            <th>Activité (plan de classement)</th>
                                            <th>Instruction</th>
                                            <th>Statut</th>
                                            <th>Validée le</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cotations as $cot)
                                            <tr>
                                                <td>{{ $cot->organisation->name ?? '—' }}</td>
                                                <td>
                                                    @if($cot->activity)
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="bi bi-diagram-2"></i> {{ $cot->activity->name }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($cot->action)
                                                        <span class="badge bg-info text-dark">{{ $cot->action->name }}</span>
                                                    @endif
                                                    @if($cot->instruction)
                                                        <span class="text-muted small">{{ $cot->instruction }}</span>
                                                    @endif
                                                    @if(!$cot->action && !$cot->instruction)
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($cot->status === 'completed')
                                                        <span class="badge bg-success"><i class="bi bi-check2-circle"></i> Réception validée</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> En attente</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $cot->confirmed_at ? $cot->confirmed_at->format('d/m/Y H:i') : '—' }}
                                                    @if($cot->status === 'completed' && $cot->assignedTo)
                                                        <span class="text-muted small d-block">par {{ $cot->assignedTo->name }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Bandeau signature DG --}}
                    @if($mail->dg_signature_status === 'signed')
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="bi bi-patch-check"></i>
                            Signé par le DG ({{ $mail->dgSigner->name ?? '—' }}) le {{ optional($mail->dg_signed_at)->format('d/m/Y H:i') }}.
                            @if($mail->dg_signature_note) — « {{ $mail->dg_signature_note }} » @endif
                        </div>
                    @elseif($mail->dg_signature_status === 'rejected')
                        @php
                            // Distinguer un renvoi pour révision d'un rejet définitif via le dernier événement.
                            $lastAction = isset($timeline) ? optional($timeline->first())->action : null;
                            $isRevision = $lastAction === 'returned_for_revision';
                        @endphp
                        <div class="alert {{ $isRevision ? 'alert-warning' : 'alert-danger' }} mt-3 mb-0">
                            <i class="bi {{ $isRevision ? 'bi-arrow-counterclockwise' : 'bi-x-octagon' }}"></i>
                            {{ $isRevision ? 'Renvoyé pour révision' : 'Rejeté' }}
                            le {{ optional($mail->dg_signed_at)->format('d/m/Y H:i') }}.
                            @if($mail->dg_signature_note) — « {{ $mail->dg_signature_note }} » @endif
                        </div>
                    @endif

                    {{-- Bandeau accusé de réception (courrier entrant / interne validé par le destinataire) --}}
                    @if(in_array($mail->mail_type, ['incoming', 'internal']) && $statusVal === 'completed')
                        @php
                            // Cotation multi-directions : l'accusé porte sur toutes les directions
                            // cotées (le détail nominatif figure dans le suivi par direction).
                            $cotationsRecues = $mail->cotations;
                            if ($cotationsRecues->count()) {
                                $receiverLabel = $cotationsRecues
                                    ->map(fn ($c) => $c->organisation->name ?? null)
                                    ->filter()
                                    ->implode(', ');
                            } else {
                                $receiver = $mail->assignedTo;
                                $receiverLabel = $receiver ? trim($receiver->name . ' ' . ($receiver->surname ?? '')) : '—';
                            }
                        @endphp
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="bi bi-check2-circle"></i>
                            Accusé de réception : reçu par {{ $receiverLabel }} le {{ optional($mail->processed_at)->format('d/m/Y H:i') }}.
                        </div>
                    @endif
                </div>

                {{-- Traçabilité : historique du courrier (qui / quand / durée par étape) --}}
                @isset($timeline)
                @if($timeline->count())
                @php
                    $actionLabels = [
                        'created' => 'Créé',
                        'updated' => 'Modifié',
                        'coted' => 'Coté par le DG',
                        'reception_confirmed' => 'Réception validée',
                        'submitted_for_approval' => 'Soumis pour validation',
                        'n1_validated' => 'Visa hiérarchique (N+1)',
                        'n1_escalated' => 'Transmis au niveau supérieur',
                        'assigned_to_user' => 'Affecté à un agent',
                        'dg_signed' => 'Signé par le DG',
                        'dg_rejected' => 'Rejeté par le DG',
                        'returned_for_revision' => 'Renvoyé pour révision',
                        'resubmitted' => 'Corrigé et resoumis',
                        'replied' => 'Réponse / suite créée',
                        'created_from_reply' => 'Créé en réponse à un courrier',
                        'deleted' => 'Supprimé',
                    ];
                @endphp
                <div class="col-md-12 mt-4">
                    <h5 class="text-secondary mb-3"><i class="bi bi-clock-history"></i> Historique du courrier</h5>
                    <ul class="list-group">
                        @foreach($timeline as $i => $event)
                            @php
                                // Délai depuis l'étape précédente (les événements sont triés du plus récent au plus ancien).
                                $previous = $timeline[$i + 1] ?? null;
                                $delay = $previous ? $previous->created_at->diffForHumans($event->created_at, ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) : null;
                            @endphp
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span>
                                        <strong>{{ $actionLabels[$event->action] ?? ucfirst($event->action) }}</strong>
                                        @if($event->user) — {{ $event->user->name }} @endif
                                        @if($event->description)
                                            <br><small class="text-muted">{{ $event->description }}</small>
                                        @endif
                                    </span>
                                    <span class="text-nowrap text-muted small">
                                        {{ $event->created_at->format('d/m/Y H:i') }}
                                        @if($delay)
                                            <br><span class="badge bg-light text-dark">+ {{ $delay }}</span>
                                        @endif
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @endisset

    </div>

    {{-- Modal de soumission pour validation (courrier sortant ou interne).
         Pas affichée pour le DG initiateur d'un sortant : il signe directement. --}}
    @php
        $dgInitiatesOutgoing = $mail->mail_type === 'outgoing'
            && auth()->user()->hasRoleInOrganisation('DG', auth()->user()->current_organisation_id)
            && (int) $mail->sender_user_id === (int) auth()->id();
    @endphp
    @if(in_array($mail->mail_type, ['outgoing', 'internal']) && in_array($mail->status?->value, ['draft', 'in_progress']) && !$mail->assigned_organisation_id && !$dgInitiatesOutgoing)
    <div class="modal fade" id="submitModal" tabindex="-1" aria-labelledby="submitModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('mails.workflow.submit', $mail->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="submitModalLabel">Soumettre pour validation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="explanatory_note" class="form-label">Note explicative (facultatif)</label>
                        <textarea name="explanatory_note" id="explanatory_note" class="form-control" rows="4"
                                  placeholder="Justification / contexte à l'attention du validateur ou du DG"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Soumettre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de renvoi pour révision (N+1 ou DG) --}}
    @if($mail->mail_type === 'outgoing' && in_array($mail->status?->value, ['pending_review', 'pending_approval']))
    <div class="modal fade" id="revisionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('mails.workflow.return-for-revision', $mail->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Renvoyer pour révision</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="revision_note" class="form-label">Modifications à apporter <span class="text-danger">*</span></label>
                        <textarea name="note" id="revision_note" class="form-control" rows="4" required
                                  placeholder="Précisez ce que l'initiateur doit corriger ou compléter"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Renvoyer pour révision</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de resoumission après révision (initiateur, avec pièces jointes) --}}
    @if($mail->mail_type === 'outgoing' && $mail->status?->value === 'rejected')
    <div class="modal fade" id="resubmitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('mails.workflow.resubmit', $mail->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Corriger et resoumettre</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($mail->dg_signature_note)
                            <div class="alert alert-warning">
                                <strong>Modifications demandées :</strong><br>{{ $mail->dg_signature_note }}
                            </div>
                        @endif
                        <label for="resubmit_note" class="form-label">Commentaire (facultatif)</label>
                        <textarea name="note" id="resubmit_note" class="form-control mb-3" rows="3"
                                  placeholder="Décrivez les corrections apportées"></textarea>
                        <label for="resubmit_attachments" class="form-label">Pièces jointes correctives (facultatif)</label>
                        <input type="file" name="attachments[]" id="resubmit_attachments" class="form-control" multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Resoumettre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal d'affectation interne (responsable du service destinataire) --}}
    @if($mail->mail_type === 'internal' && $mail->assigned_organisation_id
        && (int) $mail->assigned_organisation_id === (int) auth()->user()->current_organisation_id
        && !$mail->assigned_to)
    @php
        // Agents du service destinataire à qui affecter le courrier.
        $serviceUsers = \App\Models\Organisation::find(auth()->user()->current_organisation_id)?->users ?? collect();
        $dgInstructions = \App\Models\MailAction::whereIn('name', ['Donner suite', "M'expliquer", 'En parler', 'Classer'])->get();
    @endphp
    <div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('mails.workflow.assign-user', $mail->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Affecter à un agent</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="assign_user" class="form-label">Agent du service <span class="text-danger">*</span></label>
                        <select name="assigned_to" id="assign_user" class="form-select mb-3" required>
                            <option value="">Choisir un agent</option>
                            @foreach($serviceUsers as $su)
                                <option value="{{ $su->id }}">{{ $su->name }} {{ $su->surname }}</option>
                            @endforeach
                        </select>
                        <label for="assign_action" class="form-label">Instruction (facultatif)</label>
                        <select name="action_id" id="assign_action" class="form-select mb-3">
                            <option value="">Aucune instruction</option>
                            @foreach($dgInstructions as $instr)
                                <option value="{{ $instr->id }}">{{ $instr->name }}</option>
                            @endforeach
                        </select>
                        <label for="assign_instruction" class="form-label">Précisions (facultatif)</label>
                        <textarea name="instruction" id="assign_instruction" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Affecter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal : répondre / donner suite (le nouveau courrier reste rattaché à celui-ci) --}}
    <div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('mails.workflow.reply', $mail->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Répondre / donner suite</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">
                            Le courrier créé reste <strong>rattaché à {{ $mail->code }}</strong> : on garde la trace
                            de tout ce qu'a produit ce courrier. Il part en brouillon et suit ensuite le circuit
                            normal (soumission, visas, signature du DG).
                        </p>

                        <label for="reply_name" class="form-label">Objet <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="reply_name" class="form-control mb-3" maxlength="255"
                               value="RE : {{ \Illuminate\Support\Str::limit($mail->name, 200, '') }}" required>

                        <label for="reply_type" class="form-label">Type de courrier</label>
                        <select name="mail_type" id="reply_type" class="form-select mb-3">
                            <option value="outgoing" {{ $mail->mail_type === 'incoming' ? 'selected' : '' }}>
                                Sortant (réponse à un tiers extérieur)
                            </option>
                            <option value="internal" {{ $mail->mail_type !== 'incoming' ? 'selected' : '' }}>
                                Interne (note / transmission à un autre service)
                            </option>
                        </select>

                        <label for="reply_description" class="form-label">Contenu / éléments de réponse</label>
                        <textarea name="description" id="reply_description" class="form-control" rows="4"
                                  placeholder="Rédigez ici les éléments de réponse…"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-reply"></i> Créer la réponse
                        </button>
                    </div>
                </form>
            </div>
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
