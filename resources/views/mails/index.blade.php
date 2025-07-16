
@extends('layouts.app')

@section('content')
<div id="mailList">

    {{-- Titre dynamique selon le type --}}
    @php
        $titles = [
            'received' => 'Courriers reçus',
            'send' => 'Courriers envoyés',
            'received_external' => 'Courriers reçus externes',
            'send_external' => 'Courriers envoyés externes'

        ];
        $currentTitle = $titles[$type ?? 'received'] ?? 'Courriers';
    @endphp

    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ $currentTitle }}</h1>

    <!-- Bandeau de recherche avec icônes -->
    <div class="d-flex justify-content-start align-items-center bg-light p-2 mb-2 rounded overflow-auto">
        <div class="d-flex align-items-center gap-3 px-2">
            {{-- Navigation adaptée selon le type --}}
            @if(in_array($type ?? '', ['received_external', 'send_external']))
                {{-- Navigation pour courriers externes --}}
                <a href="{{ route('mails.received.external.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers reçus externes">
                    <i class="bi bi-inbox fs-5 text-primary"></i>
                    <span class="small {{ ($type ?? '') === 'received_external' ? 'fw-bold' : '' }}">Reçus</span>
                </a>
                <a href="{{ route('mails.send.external.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers envoyés externes">
                    <i class="bi bi-envelope fs-5 text-primary"></i>
                    <span class="small {{ ($type ?? '') === 'send_external' ? 'fw-bold' : '' }}">Envoyés</span>
                </a>
            @else
                {{-- Navigation pour courriers internes --}}
                <a href="{{ route('mail-received.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers reçus">
                    <i class="bi bi-inbox fs-5 text-primary"></i>
                    <span class="small {{ ($type ?? '') === 'received' ? 'fw-bold' : '' }}">Reçus</span>
                </a>
                <a href="{{ route('mail-send.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers envoyés">
                    <i class="bi bi-envelope fs-5 text-primary"></i>
                    <span class="small {{ ($type ?? '') === 'send' ? 'fw-bold' : '' }}">Envoyés</span>
                </a>
            @endif

            <a href="{{ route('mails.archived') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers archivés">
                <i class="bi bi-archive fs-5 text-primary"></i>
                <span class="small">Archives</span>
            </a>
            <div class="vr mx-1"></div>
            <a href="{{ route('mail-select-date') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Recherche par dates">
                <i class="bi bi-calendar fs-5 text-primary"></i>
                <span class="small">Dates</span>
            </a>
            <a href="{{ route('mail-container.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Boîtes d'archives">
                <i class="bi bi-box fs-5 text-primary"></i>
                <span class="small">Boîtes</span>
            </a>
            <div class="vr mx-1"></div>
            <a href="{{ route('mails.advanced.form') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Recherche avancée">
                <i class="bi bi-sliders fs-5 text-primary"></i>
                <span class="small">Avancée</span>
            </a>
        </div>

        <div class="ms-auto pe-2">
            <form class="d-flex" action="{{ route('mails.search') }}" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control form-control-sm" placeholder="Recherche rapide..." name="q" aria-label="Recherche">
                    <button class="btn btn-outline-secondary" type="submit" title="Rechercher">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
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
            <a href="#" id="cartBtn" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#cartModal">
                <i class="bi bi-cart me-1"></i>
                Chariot
            </a>
            <a href="#" id="exportBtn" class="btn btn-light btn-sm">
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

    <div id="mailList" class="mb-4">
        @foreach ($mails as $mail)
            <div class="card mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                <div class="card-header bg-light d-flex align-items-center py-2">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="checkbox" value="{{ $mail->id }}" id="mail_{{ $mail->id }}" name="selected_mail[]" />
                    </div>

                    <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $mail->id }}" aria-expanded="false" aria-controls="collapse{{ $mail->id }}">
                        <i class="bi bi-chevron-down fs-5"></i>
                    </button>

                    <h4 class="card-title flex-grow-1 m-0" for="mail_{{ $mail->id }}">
                        @php
                            $showRoute = match($type ?? 'received') {
                                'received_external' => route('mails.received.external.show', $mail),
                                'send_external' => route('mails.send.external.show', $mail),
                                'received' => route('mail-received.show', $mail),
                                'send' => route('mail-send.show', $mail),
                                default => '#'
                            };
                        @endphp
                        <a href="{{ $showRoute }}" class="text-decoration-none text-dark">
                            <span class="fs-5 fw-semibold">{{ $mail->code ?? 'N/A' }}</span>
                            <span class="fs-5"> - {{ $mail->name ?? 'N/A' }}</span>

                            {{-- Badges adaptés selon le type --}}
                            @if(in_array($type ?? '', ['received_external', 'send_external']))
                                {{-- Pour les courriers externes, afficher la typologie --}}
                                @if($mail->typology)
                                    <span class="badge bg-danger ms-2">{{ $mail->typology->name ?? '' }}</span>
                                @endif
                            @else
                                {{-- Pour les courriers internes, afficher l'action --}}
                                @if($mail->action)
                                    <span class="badge bg-danger ms-2">{{ $mail->action->name ?? '' }}</span>
                                @endif
                                {{-- Ou la priorité si pas d'action --}}
                                @if(!$mail->action && $mail->priority)
                                    <span class="badge bg-{{ $mail->priority->color ?? 'secondary' }} ms-2">{{ $mail->priority->name ?? '' }}</span>
                                @endif
                            @endif

                            {{-- Badge archives (commun à tous) --}}
                            @if ($mail->containers && $mail->containers->count() > 1)
                            <span class="badge bg-primary ms-2">
                                copies {{ $mail->containers->count() }} archivées
                            </span>
                            @elseif ($mail->containers && $mail->containers->count() == 1)
                            <span class="badge bg-primary ms-2">
                                copie {{ $mail->containers->count() }} archivée
                            </span>
                            @endif
                        </a>
                    </h4>
                </div>

                <div class="collapse" id="collapse{{ $mail->id }}">
                    <div class="card-body bg-white">
                        @if($mail->description)
                            <div class="mb-3">
                                <p class="mb-2">
                                    <i class="bi bi-card-text me-2 text-primary"></i>
                                    <strong>Description:</strong> {{ $mail->description }}
                                </p>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-12">
                                <p class="mb-2">
                                    {{-- Affichage adapté selon le type --}}
                                    @if(in_array($type ?? '', ['received_external', 'send_external']))
                                        {{-- Courriers externes --}}
                                        @if($mail->externalSender)
                                            <i class="bi bi-person-fill me-2 text-primary"></i>
                                            <strong>Envoyé par:</strong>
                                            {{ $mail->externalSender->first_name ?? 'N/A' }} {{ $mail->externalSender->last_name ?? '' }}
                                            @if($mail->externalSenderOrganization)
                                                ({{ $mail->externalSenderOrganization->name ?? 'N/A' }})
                                            @endif
                                            <br>
                                        @endif

                                        @if($mail->externalRecipient)
                                            <i class="bi bi-person-fill me-2 text-primary"></i>
                                            <strong>Reçu par:</strong>
                                            {{ $mail->externalRecipient->first_name ?? 'N/A' }} {{ $mail->externalRecipient->last_name ?? '' }}
                                            @if($mail->externalRecipientOrganization)
                                                ({{ $mail->externalRecipientOrganization->name ?? 'N/A' }})
                                            @endif
                                            <br>
                                        @endif

                                        @if($mail->typology)
                                            <i class="bi bi-file-earmark-text-fill me-2 text-primary"></i>
                                            <strong>Type de document:</strong>
                                            {{ $mail->typology->name ?? 'N/A' }}
                                            <br>
                                        @endif
                                    @else
                                        {{-- Courriers internes --}}
                                        @if($mail->sender)
                                            <i class="bi bi-person-fill me-2 text-primary"></i>
                                            <strong>Envoyé par:</strong>
                                            {{ $mail->sender->name ?? 'N/A' }}
                                            @if($mail->senderOrganisation)
                                                ({{ $mail->senderOrganisation->name ?? 'N/A' }})
                                            @endif
                                            <br>
                                        @endif

                                        @if($mail->recipient)
                                            <i class="bi bi-person-fill me-2 text-primary"></i>
                                            <strong>Reçu par:</strong>
                                            {{ $mail->recipient->name ?? 'N/A' }}
                                            @if($mail->recipientOrganisation)
                                                ({{ $mail->recipientOrganisation->name ?? 'N/A' }})
                                            @endif
                                            <br>
                                        @endif

                                        @if($mail->action)
                                            <i class="bi bi-exclamation-triangle-fill me-2 text-primary"></i>
                                            <strong>Action:</strong>
                                            {{ $mail->action->name ?? 'N/A' }}
                                            <br>
                                        @endif

                                        @if($mail->priority)
                                            <i class="bi bi-exclamation-triangle-fill me-2 text-primary"></i>
                                            <strong>Priorité:</strong>
                                            {{ $mail->priority->name ?? 'N/A' }}
                                            <br>
                                        @endif
                                    @endif

                                    <i class="bi bi-calendar-event me-2 text-primary"></i>
                                    <strong>Date:</strong>
                                    {{ $mail->date ? \Carbon\Carbon::parse($mail->date)->format('d/m/Y') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item {{ $mails->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $mails->previousPageUrl() }}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                @foreach ($mails->getUrlRange(1, $mails->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $mails->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
                <li class="page-item {{ $mails->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $mails->nextPageUrl() }}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Attachments Modals -->
        @foreach ($mails as $mail)
            @if($mail->attachments->count() > 0)
                <div class="modal fade" id="attachmentsModal{{ $mail->id }}" tabindex="-1" aria-labelledby="attachmentsModalLabel{{ $mail->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="attachmentsModalLabel{{ $mail->id }}">
                                    <i class="bi bi-paperclip me-2"></i>{{ __('attachments') }} - {{ $mail->code }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-group">
                                    @foreach($mail->attachments as $attachment)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <i class="bi bi-file-earmark me-2"></i>{{ $attachment->name }}
                                            <a href="{{ route('mail-attachment.show', [$mail->id, $attachment->id]) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Ajouter au chariot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Sélectionnez le chariot qui recevra les <span id="selectedMailsCountCart">0</span> courrier(s) sélectionné(s) :</p>

                    <!-- Zone de chargement -->
                    <div id="dolliesLoading" class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2 text-muted">Chargement des chariots...</p>
                    </div>

                    <!-- Liste des chariots -->
                    <div id="dolliesList" class="d-none">
                        <div class="row" id="dolliesGrid">
                            <!-- Les chariots seront ajoutés ici via JavaScript -->
                        </div>

                        <!-- Option pour créer un nouveau chariot -->
                        <div class="border-top pt-3 mt-3">
                            <h6>Ou créer un nouveau chariot :</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="newDollyName" placeholder="Nom du chariot" maxlength="70">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="newDollyDescription" placeholder="Description (optionnel)" maxlength="100">
                                </div>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="cart_option" value="new" id="newDollyOption">
                                <label class="form-check-label fw-semibold" for="newDollyOption">
                                    Créer un nouveau chariot avec ces courriers
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Message d'erreur -->
                    <div id="dolliesError" class="alert alert-danger d-none">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Erreur lors du chargement des chariots.
                    </div>

                    <!-- Informations -->
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Les courriers seront ajoutés au chariot sélectionné pour faciliter leur gestion groupée.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmCart" disabled>Ajouter au chariot</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for Print -->
    <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">{{ __('print_records') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('print_records') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="confirmPrint">{{ __('print') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Archive -->
    <div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archiveModalLabel">Archiver les courriers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Sélectionnez la boîte d'archives qui recevra les <span id="selectedMailsCount">0</span> courrier(s) sélectionné(s) :</p>

                    <!-- Zone de chargement -->
                    <div id="containersLoading" class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2 text-muted">Chargement des boîtes d'archives...</p>
                    </div>

                    <!-- Liste des conteneurs -->
                    <div id="containersList" class="d-none">
                        <div class="row" id="containersGrid">
                            <!-- Les conteneurs seront ajoutés ici via JavaScript -->
                        </div>
                    </div>

                    <!-- Message d'erreur -->
                    <div id="containersError" class="alert alert-danger d-none">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Erreur lors du chargement des boîtes d'archives.
                    </div>

                    <!-- Informations -->
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Les courriers archivés seront déplacés vers la boîte sélectionnée et ne seront plus visibles dans cette liste.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmArchive" disabled>Archiver</button>
                </div>
            </div>
        </div>
    </div>
@endsection



@push('scripts')
    <style>
        .container-card {
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .container-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: #007bff;
        }

        .container-card:has(.container-radio:checked) {
            background-color: #e3f2fd;
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.2);
        }

        .container-radio:checked + label {
            color: #0056b3;
        }

        #containersGrid .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }

        /* Styles pour les chariots */
        .dolly-card {
            transition: all 0.2s ease;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .dolly-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: #28a745;
        }

        .dolly-card:has(.dolly-radio:checked) {
            background-color: #d4edda;
            border-color: #28a745;
            box-shadow: 0 2px 8px rgba(40,167,69,0.2);
        }

        .dolly-radio:checked + label {
            color: #155724;
        }

        #dolliesGrid .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
    </style>
    <script>
        console.log('Script chargé');

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');

            // Test direct des boutons collapse
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
                button.addEventListener('click', function(e) {
                    console.log('Bouton collapse cliqué:', this.getAttribute('data-bs-target'));
                });
            });
        });

        document.getElementById('cartBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[name="selected_mail[]"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un courrier.');
                return;
            }

            console.log('IDs sélectionnés pour le chariot:', checkedRecords);

            // Mettre à jour le compteur
            document.getElementById('selectedMailsCountCart').textContent = checkedRecords.length;

            // Afficher le modal
            var cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
            cartModal.show();

            // Charger les chariots
            loadDollies();
        });

        document.getElementById('confirmCart').addEventListener('click', function() {
            let checkedRecords = Array.from(document.querySelectorAll('input[name="selected_mail[]"]:checked'))
                .map(checkbox => checkbox.value);

            // Vérifier l'option sélectionnée
            const selectedDolly = document.querySelector('input[name="cart_option"]:checked');

            if (!selectedDolly) {
                alert('Veuillez sélectionner un chariot ou créer un nouveau chariot.');
                return;
            }

            // Désactiver le bouton pendant l'opération
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Traitement...';

            if (selectedDolly.value === 'new') {
                // Créer un nouveau chariot
                const dollyName = document.getElementById('newDollyName').value.trim();
                if (!dollyName) {
                    alert('Veuillez saisir un nom pour le nouveau chariot.');
                    this.disabled = false;
                    this.innerHTML = 'Ajouter au chariot';
                    return;
                }

                const dollyDescription = document.getElementById('newDollyDescription').value.trim();

                // Créer le chariot puis ajouter les mails
                createNewDollyWithMails(dollyName, dollyDescription, checkedRecords);
            } else {
                // Ajouter aux chariot existant
                addMailsToDolly(selectedDolly.value, checkedRecords);
            }
        });

        document.getElementById('exportBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[name="selected_mail[]"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un courrier à exporter.');
                return;
            }

            console.log('IDs sélectionnés pour export:', checkedRecords);

            fetch('{{ route("mail-transaction.export") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ selectedIds: checkedRecords })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP: ' + response.status);
                    }
                    return response.blob();
                })
                .then(blob => {
                    let url = window.URL.createObjectURL(blob);
                    let a = document.createElement('a');
                    a.href = url;
                    a.download = 'courriers_export.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);

                    // Décocher tous les checkboxes après export
                    document.querySelectorAll('input[name="selected_mail[]"]:checked').forEach(cb => cb.checked = false);
                })
                .catch(error => {
                    console.error('Erreur lors de l\'export:', error);
                    alert('Erreur lors de l\'export: ' + error.message);
                });
        });

        document.getElementById('printBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[name="selected_mail[]"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un courrier à imprimer.');
                return;
            }

            console.log('IDs sélectionnés pour impression:', checkedRecords);
            var printModal = new bootstrap.Modal(document.getElementById('printModal'));
            printModal.show();
        });

        document.getElementById('confirmPrint').addEventListener('click', function() {
            let checkedRecords = Array.from(document.querySelectorAll('input[name="selected_mail[]"]:checked'))
                .map(checkbox => checkbox.value);

            console.log('Envoi des IDs pour impression:', checkedRecords);

            fetch('{{ route("mail-transaction.print") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ selectedIds: checkedRecords })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP: ' + response.status);
                    }
                    return response.blob();
                })
                .then(blob => {
                    let url = window.URL.createObjectURL(blob);
                    let a = document.createElement('a');
                    a.href = url;
                    a.download = 'courriers_impression.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);

                    // Décocher tous les checkboxes après impression
                    document.querySelectorAll('input[name="selected_mail[]"]:checked').forEach(cb => cb.checked = false);
                })
                .catch(error => {
                    console.error('Erreur lors de l\'impression:', error);
                    alert('Erreur lors de l\'impression: ' + error.message);
                });

            var printModal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
            printModal.hide();
        });

        document.getElementById('archiveBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[name="selected_mail[]"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un courrier à archiver.');
                return;
            }

            console.log('IDs sélectionnés pour archivage:', checkedRecords);

            // Mettre à jour le compteur
            document.getElementById('selectedMailsCount').textContent = checkedRecords.length;

            // Afficher le modal
            var archiveModal = new bootstrap.Modal(document.getElementById('archiveModal'));
            archiveModal.show();

            // Charger les conteneurs
            loadContainers();
        });

        function loadContainers() {
            // Afficher le loading
            document.getElementById('containersLoading').classList.remove('d-none');
            document.getElementById('containersList').classList.add('d-none');
            document.getElementById('containersError').classList.add('d-none');

            // Réinitialiser le bouton
            document.getElementById('confirmArchive').disabled = true;

            fetch('{{ route("mail-container.list") }}', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    displayContainers(data.containers || data);
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des conteneurs:', error);
                    document.getElementById('containersLoading').classList.add('d-none');
                    document.getElementById('containersError').classList.remove('d-none');
                });
        }

        function displayContainers(containers) {
            document.getElementById('containersLoading').classList.add('d-none');

            if (!containers || containers.length === 0) {
                document.getElementById('containersError').innerHTML =
                    '<i class="bi bi-exclamation-triangle me-2"></i>Aucune boîte d\'archives disponible.';
                document.getElementById('containersError').classList.remove('d-none');
                return;
            }

            const grid = document.getElementById('containersGrid');
            grid.innerHTML = '';

            containers.forEach(container => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-6 col-lg-4 mb-3';

                colDiv.innerHTML = `
                    <div class="card container-card h-100" style="cursor: pointer;" data-container-id="${container.id}">
                        <div class="card-body p-3">
                            <div class="form-check">
                                <input class="form-check-input container-radio" type="radio" name="selected_container" value="${container.id}" id="container_${container.id}">
                                <label class="form-check-label w-100" for="container_${container.id}">
                                    <div class="d-flex align-items-start">
                                        <div class="me-2">
                                            <i class="bi bi-box-seam fs-4 text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">${container.code || 'Code non défini'}</div>
                                            <div class="text-muted small">${container.name || 'Nom non défini'}</div>
                                            ${container.description ? `<div class="text-muted small mt-1">${container.description.substring(0, 50)}${container.description.length > 50 ? '...' : ''}</div>` : ''}
                                            <div class="mt-2">
                                                <span class="badge bg-info">ID: ${container.id}</span>
                                                ${container.location ? `<span class="badge bg-secondary ms-1">${container.location}</span>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                `;

                grid.appendChild(colDiv);
            });

            document.getElementById('containersList').classList.remove('d-none');

            // Ajouter les événements
            setupContainerSelection();
        }

        function setupContainerSelection() {
            // Sélection par clic sur la carte
            document.querySelectorAll('.container-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.type !== 'radio') {
                        const radio = this.querySelector('.container-radio');
                        radio.checked = true;
                        updateConfirmButton();
                    }
                });
            });

            // Sélection par radio button
            document.querySelectorAll('.container-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    updateConfirmButton();
                });
            });
        }

        function updateConfirmButton() {
            const selectedContainer = document.querySelector('input[name="archive_container"]:checked');
            document.getElementById('confirmArchive').disabled = !selectedContainer;
        }

        document.getElementById('confirmArchive').addEventListener('click', function() {
            let checkedRecords = Array.from(document.querySelectorAll('input[name="selected_mail[]"]:checked'))
                .map(checkbox => checkbox.value);

            const selectedContainer = document.querySelector('input[name="archive_container"]:checked');

            if (!selectedContainer) {
                alert('Veuillez sélectionner une boîte d\'archives.');
                return;
            }

            console.log('Envoi des IDs pour archivage:', checkedRecords);
            console.log('Conteneur sélectionné:', selectedContainer.value);

            // Désactiver le bouton pendant l'opération
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Archivage...';

            fetch('{{ route("mail-transaction.archive") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    mail_ids: checkedRecords,
                    container_id: selectedContainer.value
                })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Les courriers ont été archivés avec succès.');
                        // Recharger la page pour voir les changements
                        location.reload();
                    } else {
                        alert('Erreur lors de l\'archivage: ' + (data.message || 'Erreur inconnue'));
                        // Réactiver le bouton
                        this.disabled = false;
                        this.innerHTML = 'Archiver';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de l\'archivage:', error);
                    alert('Erreur lors de l\'archivage: ' + error.message);
                    // Réactiver le bouton
                    this.disabled = false;
                    this.innerHTML = 'Archiver';
                });

            var archiveModal = bootstrap.Modal.getInstance(document.getElementById('archiveModal'));
            archiveModal.hide();
        });

        function confirmDelete(mailId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce courrier ?')) {
                document.getElementById('delete-form-' + mailId).submit();
            }
        }
        function loadDollies() {
            // Afficher le loading
            document.getElementById('dolliesLoading').classList.remove('d-none');
            document.getElementById('dolliesList').classList.add('d-none');
            document.getElementById('dolliesError').classList.add('d-none');

            // Réinitialiser le bouton
            document.getElementById('confirmCart').disabled = true;

            fetch('/dolly-handler/list?category=mail', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    displayDollies(data.dollies || data);
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des chariots:', error);
                    document.getElementById('dolliesLoading').classList.add('d-none');
                    document.getElementById('dolliesError').classList.remove('d-none');
                });
        }

        function displayDollies(dollies) {
            document.getElementById('dolliesLoading').classList.add('d-none');

            const grid = document.getElementById('dolliesGrid');
            grid.innerHTML = '';

            if (dollies && dollies.length > 0) {
                dollies.forEach(dolly => {
                    const colDiv = document.createElement('div');
                    colDiv.className = 'col-md-6 col-lg-4 mb-3';

                    colDiv.innerHTML = `
                        <div class="card dolly-card h-100" data-dolly-id="${dolly.id}">
                            <div class="card-body p-3">
                                <div class="form-check">
                                    <input class="form-check-input dolly-radio" type="radio" name="cart_option" value="${dolly.id}" id="dolly_${dolly.id}">
                                    <label class="form-check-label w-100" for="dolly_${dolly.id}">
                                        <div class="d-flex align-items-start">
                                            <div class="me-2">
                                                <i class="bi bi-cart-fill fs-4 text-success"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">${dolly.name || 'Nom non défini'}</div>
                                                ${dolly.description ? `<div class="text-muted small">${dolly.description.substring(0, 50)}${dolly.description.length > 50 ? '...' : ''}</div>` : ''}
                                                <div class="mt-2">
                                                    <span class="badge bg-success">ID: ${dolly.id}</span>
                                                    ${dolly.category ? `<span class="badge bg-secondary ms-1">${dolly.category}</span>` : ''}
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    `;

                    grid.appendChild(colDiv);
                });
            }

            document.getElementById('dolliesList').classList.remove('d-none');

            // Ajouter les événements
            setupDollySelection();
        }

        function setupDollySelection() {
            // Sélection par clic sur la carte
            document.querySelectorAll('.dolly-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.type !== 'radio') {
                        const radio = this.querySelector('.dolly-radio');
                        radio.checked = true;
                        updateCartConfirmButton();
                    }
                });
            });

            // Sélection par radio button
            document.querySelectorAll('input[name="cart_option"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    updateCartConfirmButton();
                });
            });
        }

        function updateCartConfirmButton() {
            const selectedOption = document.querySelector('input[name="cart_option"]:checked');
            document.getElementById('confirmCart').disabled = !selectedOption;
        }

        function createNewDollyWithMails(name, description, mailIds) {
            fetch('/dolly-handler/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: name,
                    description: description,
                    category: 'mail'
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Ajouter les mails au nouveau chariot
                        addMailsToDolly(data.data.id, mailIds);
                    } else {
                        alert('Erreur lors de la création du chariot: ' + (data.message || 'Erreur inconnue'));
                        resetCartButton();
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la création du chariot:', error);
                    alert('Erreur lors de la création du chariot: ' + error.message);
                    resetCartButton();
                });
        }

        function addMailsToDolly(dollyId, mailIds) {
            fetch('/dolly-handler/add-items', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    dolly_id: dollyId,
                    category: 'mail',
                    items: mailIds
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success || response.ok) {
                        alert(`${mailIds.length} courrier(s) ajouté(s) au chariot avec succès.`);

                        // Fermer le modal
                        var cartModal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
                        cartModal.hide();

                        // Décocher tous les checkboxes
                        document.querySelectorAll('input[name="selected_mail[]"]:checked').forEach(cb => cb.checked = false);

                        // Réinitialiser le bouton
                        resetCartButton();
                    } else {
                        alert('Erreur lors de l\'ajout au chariot: ' + (data.message || 'Erreur inconnue'));
                        resetCartButton();
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de l\'ajout au chariot:', error);
                    alert('Erreur lors de l\'ajout au chariot: ' + error.message);
                    resetCartButton();
                });
        }

        function resetCartButton() {
            const confirmButton = document.getElementById('confirmCart');
            confirmButton.disabled = false;
            confirmButton.innerHTML = 'Ajouter au chariot';
        }

        let checkAllBtn = document.getElementById('checkAllBtn');
        checkAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            let checkboxes = document.querySelectorAll('input[name="selected_mail[]"]');
            let allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);

            checkboxes.forEach(function(checkbox) {
                checkbox.checked = !allChecked;
            });

            this.innerHTML = allChecked ?
                '<i class="bi bi-check-square me-1"></i>Tout cocher' :
                '<i class="bi bi-square me-1"></i>Tout décocher';

            console.log('Tous les courriers', allChecked ? 'décochés' : 'cochés');
        });

        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input, filter, cards, card, i, txtValue;
            input = document.getElementById('searchInput');
            filter = input.value.toUpperCase();
            cards = document.getElementById('mailList').getElementsByClassName('card');

            for (i = 0; i < cards.length; i++) {
                card = cards[i];
                txtValue = card.textContent || card.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const collapseElements = document.querySelectorAll('.collapse');
            collapseElements.forEach(collapse => {
                collapse.addEventListener('show.bs.collapse', function () {
                    const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
                    if(button) {
                        const icon = button.querySelector('i');
                        if(icon) {
                            icon.classList.remove('bi-chevron-down');
                            icon.classList.add('bi-chevron-up');
                        }
                    }
                });
                collapse.addEventListener('hide.bs.collapse', function () {
                    const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
                    if(button) {
                        const icon = button.querySelector('i');
                        if(icon) {
                            icon.classList.remove('bi-chevron-up');
                            icon.classList.add('bi-chevron-down');
                        }
                    }
                });
            });

            // Gestion du "voir plus / voir moins" pour le contenu
            document.querySelectorAll('.content-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('data-target');
                    const targetElement = document.getElementById(targetId);
                    const fullText = this.getAttribute('data-full-text');

                    if (this.textContent === 'Voir plus') {
                        targetElement.textContent = fullText;
                        this.textContent = 'Voir moins';
                    } else {
                        targetElement.textContent = fullText.substr(0, 200) + '...';
                        this.textContent = 'Voir plus';
                    }
                });
            });
        });
    </script>
@endpush
