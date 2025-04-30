@extends('layouts.app')

@section('content')
<div id="mailList">

     <h1 class="text-3xl font-bold text-gray-900 mb-6">Courriers sortants</h1>

     <!-- Bandeau de recherche avec icônes -->
     <div class="d-flex justify-content-start align-items-center bg-light p-2 mb-2 rounded overflow-auto">
        <div class="d-flex align-items-center gap-3 px-2">
            <a href="{{ route('mail-received.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers reçus">
                <i class="bi bi-inbox fs-5 text-primary"></i>
                <span class="small">Reçus</span>
            </a>
            <a href="{{ route('mail-send.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Courriers envoyés">
                <i class="bi bi-envelope fs-5 text-primary"></i>
                <span class="small">Envoyés</span>
            </a>
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
            <a href="{{ route('batch.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Mes parapheurs">
                <i class="bi bi-folder fs-5 text-primary"></i>
                <span class="small">Mes parapheurs</span>
            </a>
            <a href="{{ route('batch-received.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Parapheurs reçus">
                <i class="bi bi-folder-check fs-5 text-primary"></i>
                <span class="small">Reçus</span>
            </a>
            <a href="{{ route('batch-send.index') }}" class="text-decoration-none text-dark d-flex flex-column align-items-center" title="Parapheurs envoyés">
                <i class="bi bi-folder-symlink fs-5 text-primary"></i>
                <span class="small">Envoyés</span>
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
                        <a href="{{ route('mail-send.show', $mail) }}" class="text-decoration-none text-dark">
                            <span class="fs-5 fw-semibold">{{ $mail->code ?? 'N/A' }} [{{ $mail->recipientOrganisation->code ?? 'Entrant' }}]</span>
                            <span class="fs-5"> - {{ $mail->name ?? 'N/A' }}</span>
                            <span class="badge bg-danger ms-2">{{ $mail->action->name ?? '' }}</span>
                            @if ( $mail->containers->count()>1)
                            <span class="badge bg-primary ms-2">
                                copies {{ $mail->containers->count() }} archivées
                            </span>
                            @elseif ($mail->containers->count() == 1)
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
                                    <i class="bi bi-person-fill me-2 text-primary"></i>
                                    <strong>Envoyé par:</strong>
                                    {{ $mail->sender->name ?? 'N/A' }} ({{ $mail->senderOrganisation->name ?? 'N/A' }})
                                    <br>

                                    <i class="bi bi-person-fill me-2 text-primary"></i>
                                    <strong>Reçu par:</strong>
                                    {{ $mail->recipient->name ?? 'N/A' }} ({{ $mail->recipientOrganisation->name ?? 'N/A' }})
                                    <br>

                                    <i class="bi bi-file-earmark-text-fill me-2 text-primary"></i>
                                    <strong>Type de document:</strong>
                                    {{ $mail->document_type ?? 'N/A' }}
                                    <br>

                                    <i class="bi bi-calendar-event me-2 text-primary"></i>
                                    <strong>Date:</strong>
                                    {{ date('d/m/Y', strtotime($mail->date)) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal d'archivage -->
    <div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archiveModalLabel">Archiver les documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="archiveForm">
                        <div class="mb-3">
                            <label for="containerId" class="form-label">Conteneur d'archives</label>
                            <select class="form-select" id="containerId" required>
                                <option value="" selected disabled>Sélectionner un conteneur</option>
                                <!-- Les conteneurs seront chargés dynamiquement ici -->
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="documentType" class="form-label">Type de document</label>
                            <select class="form-select" id="documentType" required>
                                <option value="duplicata"> Duplicata </option>
                                <option value="original"> original </option>
                                <option value="copy"> copie </option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmArchiveBtn">Confirmer l'archivage</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour les chariots (dollies) -->
    <div class="modal fade" id="dolliesModal" tabindex="-1" aria-labelledby="dolliesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dolliesModalLabel">Chariot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dolliesList">
                        <p>Aucun chariot chargé</p>
                    </div>
                    <div id="dollyForm" style="display: none;">
                        <form id="createDollyForm" action="{{ route('dolly.create') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label"> Categories </label>
                                @php
                                    $categories = \App\Models\Dolly::categories();
                                @endphp
                                <select class="form-select" id="category" name="category" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category }}" {{ $category == 'mail' ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Ajouter au chariot
                                </button>
                                <button type="button" class="btn btn-secondary" id="backToListBtn">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Retour à la liste
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Fermer
                    </button>
                    <button type="button" class="btn btn-primary" id="addDollyBtn">
                        <i class="bi bi-plus-circle me-1"></i> Nouveau chariot
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-header {
            transition: background-color 0.2s ease;
        }

        .card-header:hover {
            background-color: #f8f9fa !important;
        }

        .bi {
            font-size: 0.9rem;
        }

        .badge {
            font-weight: 500;
        }

        .collapse {
            transition: all 0.3s ease-out;
        }

        .btn-link:focus {
            box-shadow: none;
        }

        .bi-chevron-down {
            transition: transform 0.3s ease;
        }

        [aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }
    </style>

    <script src="{{ asset('js/mails.js') }}"></script>
    <script src="{{ asset('js/dollies.js') }}"></script>

    <script>
        // Fonction pour charger les conteneurs
        function loadContainers() {
            console.log("Chargement des containers ...");

            const containerSelect = document.getElementById('containerId');

            // Vider le select sauf la première option
            while (containerSelect.options.length > 1) {
                containerSelect.remove(1);
            }

            // Ajouter un indicateur de chargement
            const loadingOption = document.createElement('option');
            loadingOption.textContent = 'Chargement...';
            loadingOption.disabled = true;
            containerSelect.appendChild(loadingOption);

            // Récupérer les conteneurs via une requête AJAX
            fetch('/mails/containers/list', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                // Supprimer l'option de chargement
                containerSelect.remove(containerSelect.options.length - 1);

                // Ajouter les conteneurs au select
                data.forEach(container => {
                    const option = document.createElement('option');
                    option.value = container.id;
                    option.textContent = `${container.code} - ${container.name}`;
                    containerSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Erreur:', error);
                containerSelect.remove(containerSelect.options.length - 1);

                const errorOption = document.createElement('option');
                errorOption.textContent = 'Erreur de chargement';
                errorOption.disabled = true;
                containerSelect.appendChild(errorOption);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Gestion du bouton "Tout cocher"
            const checkAllBtn = document.getElementById('checkAllBtn');
            const mailCheckboxes = document.querySelectorAll('input[name="selected_mail[]"]');

            let allChecked = false;
            checkAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                allChecked = !allChecked;

                mailCheckboxes.forEach(checkbox => {
                    checkbox.checked = allChecked;
                });

                // Mise à jour du texte du bouton
                if (allChecked) {
                    checkAllBtn.innerHTML = '<i class="bi bi-square me-1"></i> Tout décocher';
                } else {
                    checkAllBtn.innerHTML = '<i class="bi bi-check-square me-1"></i> Tout cocher';
                }
            });

            // Gestion du modal d'archivage
            const archiveBtn = document.getElementById('archiveBtn');
            const confirmArchiveBtn = document.getElementById('confirmArchiveBtn');

            if(archiveBtn){
                loadContainers();
            }

            // Empêcher l'ouverture du modal si aucun mail n'est sélectionné
            archiveBtn.addEventListener('click', function(e) {
                const selectedMails = Array.from(mailCheckboxes).filter(checkbox => checkbox.checked);

                if (selectedMails.length === 0) {
                    e.preventDefault(); // Empêcher l'ouverture du modal
                    alert('Veuillez sélectionner au moins un mail à archiver.');
                }
            });

            // Ajouter l'événement de clic sur le bouton de confirmation
            confirmArchiveBtn.addEventListener('click', archiveMails);

            // Fonction pour archiver les mails
            function archiveMails() {
                const containerSelect = document.getElementById('containerId');
                const containerId = containerSelect.value;

                if (!containerId) {
                    alert('Veuillez sélectionner un conteneur d\'archives.');
                    return;
                }

                const documentTypeSelect = document.getElementById('documentType');
                const mailCheckboxes = document.querySelectorAll('input[name="selected_mail[]"]');

                const selectedMails = Array.from(mailCheckboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => {
                        return {
                            id: checkbox.value,
                            document_type: documentTypeSelect.value
                        };
                    });

                if (selectedMails.length === 0) {
                    alert('Veuillez sélectionner au moins un mail à archiver.');
                    return;
                }

                // Envoyer les données au serveur
                fetch(`/mails/archives/${containerId}/add-mails`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        mails: selectedMails
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modalElement = document.getElementById('archiveModal');
                        const modalInstance = new bootstrap.Modal(modalElement);
                        modalInstance.hide();
                        alert('Les mails ont été archivés avec succès !');
                        window.location.reload();
                    } else {
                        alert('Une erreur est survenue lors de l\'archivage.');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de l\'archivage.');
                });
            }
        });
    </script>
@endsection
