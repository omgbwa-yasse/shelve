
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

    <div id="mailList" class="mb-4">
        @foreach ($mails as $mail)
            <div class="mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                <div class="card-header bg-light d-flex align-items-center py-2" style="border-bottom: 1px solid rgba(0,0,0,0.125);">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="checkbox" value="{{ $mail->id }}" id="mail_{{ $mail->id }}" name="selected_mail[]" />
                    </div>

                    <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3" type="button" data-bs-toggle="collapse" data-bs-target="#mail-{{ $mail->id }}" aria-expanded="false" aria-controls="mail-{{ $mail->id }}">
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

                <div class="collapse" id="mail-{{ $mail->id }}">
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">{{ __('add_to_cart') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('add_to_cart') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="confirmCart">{{ __('confirm') }}</button>
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
@endsection



@push('scripts')
    <script>
        document.getElementById('cartBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un enregistrement.');
                return;
            }

            var cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
            cartModal.show();
        });

        document.getElementById('confirmCart').addEventListener('click', function() {
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            fetch('{{ route("dolly.createWithRecords") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ records: checkedRecords })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Un nouveau chariot a été créé avec les enregistrements sélectionnés.');
                    } else {
                        alert('Une erreur est survenue lors de la création du chariot.');
                    }
                });

            var cartModal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
            cartModal.hide();
        });

        document.getElementById('printBtn').addEventListener('click', function(e) {
            e.preventDefault();
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            if (checkedRecords.length === 0) {
                alert('Veuillez sélectionner au moins un enregistrement à imprimer.');
                return;
            }

            var printModal = new bootstrap.Modal(document.getElementById('printModal'));
            printModal.show();
        });

        document.getElementById('confirmPrint').addEventListener('click', function() {
            let checkedRecords = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            fetch('{{ route("records.print") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ records: checkedRecords })
            })
                .then(response => response.blob())
                .then(blob => {
                    let url = window.URL.createObjectURL(blob);
                    let a = document.createElement('a');
                    a.href = url;
                    a.download = 'records_print.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                });

            var printModal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
            printModal.hide();
        });

        function confirmDelete(mailId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce courrier ?')) {
                document.getElementById('delete-form-' + mailId).submit();
            }
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
                    button.querySelector('i').classList.replace('bi-chevron-down', 'bi-chevron-up');
                });
                collapse.addEventListener('hide.bs.collapse', function () {
                    const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
                    button.querySelector('i').classList.replace('bi-chevron-up', 'bi-chevron-down');
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
