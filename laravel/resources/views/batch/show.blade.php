@extends('layouts.app')
@section('content')
<div class="container">
    <h1 class="mt-5">Parapheur : fiche</h1>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Champ</th>
                    <th scope="col">Valeur</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">Référence</th>
                    <td>{{ $mailBatch->code }}</td>
                </tr>
                <tr>
                    <th scope="row">Désignation</th>
                    <td>{{ $mailBatch->name }}</td>
                </tr>
            </tbody>
        </table>
    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ route('batch.index') }}" class="btn btn-secondary">Retour</a>
        <a href="{{ route('batch.edit', $mailBatch->id) }}" class="btn btn-warning">Modifier</a>

        <form action="{{ route('batch.destroy', $mailBatch->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce parapheur ?')">Supprimer</button>
        </form>

        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addMailModal">Ajouter des courriers</button>

        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('batch.export.pdf', $mailBatch) }}" class="btn btn-info">Export (pdf)</a>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transferToBoxModal">Transférer vers boîtes</a>
            <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#transferToDollyModal">Transférer vers un chariot</a>
        </div>
    </div>
</div>

{{-- Section pour sélectionner les mails à transférer --}}
<div class="mt-3 mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="selectAllMailsForTransfer">
        <label class="form-check-label" for="selectAllMailsForTransfer">
            <strong>Sélectionner tous les courriers pour transfert</strong>
        </label>
    </div>
    <div class="mt-2">
        <span id="selectedMailsCount" class="text-muted">0 courrier(s) sélectionné(s) pour transfert</span>
    </div>
</div>

@foreach ( $mailBatch->mails as $mail)

        <div class="card text-start mt-1">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div class="form-check me-3 mt-1">
                    <input class="form-check-input mail-transfer-checkbox" type="checkbox" value="{{ $mail->id }}" id="transfer_mail_{{ $mail->id }}">
                </div>
                <div class="flex-grow-1">
                    <h4 class="card-title mb-2">
                        @php
                            $showRoute = '#';
                            if ($mail->isIncoming()) {
                                $showRoute = route('mail-received.show', $mail->id);
                            } elseif ($mail->isOutgoing()) {
                                $showRoute = route('mail-send.show', $mail->id);
                            } elseif ($mail->external_sender_id || $mail->external_recipient_id) {
                                // Pour les courriers externes, utiliser les routes externes si disponibles
                                $showRoute = route('mails.incoming.show', $mail->id);
                            } else {
                                // Fallback pour les courriers internes
                                $showRoute = route('mails.incoming.show', $mail->id);
                            }
                        @endphp
                        <a href="{{ $showRoute }}">
                            <strong>{{ $mail->code }} : {{ $mail->name }}
                                @if($mail->attachments->count() > 1 )
                                    ({{ $mail->attachments->count() }} fichiers)
                                @elseif($mail->attachments->count() == 1 )
                                    ({{ $mail->attachments->count() }} fichier)
                                @endif
                            </strong>
                        </a>
                    </h4>
                    <p class="card-text mb-1">
                        Du {{ $mail->date }} Par
                        @foreach($mail->authors as $author)
                            {{ $author->name }}
                        @endforeach
                    </p>
                    <p class="mb-1">{{ $mail->description }}</p>
                    <p class="mb-0">
                        <small>
                            Priority: {{ $mail->priority ? $mail->priority->name : 'N/A' }}
                            | Mail Type: {{ $mail->type ? $mail->type->name : 'N/A' }}
                            | Business Type: {{ $mail->typology ? $mail->typology->name : 'N/A' }}
                            | Nature: {{ $mail->document_type ?: 'N/A' }}
                        </small>
                    </p>
                </div>
                <form action="{{ route('batch.mail.destroy', [$mailBatch, $mail->id]) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm ms-2" onclick="return confirm('Voulez-vous vraiment retirer ce courrier du parapheur ?')">Retirer</button>
                </form>
            </div>
        </div>

@endforeach

{{-- Modals --}}
{{-- Add Mail Modal --}}
<div class="modal fade" id="addMailModal" tabindex="-1" aria-labelledby="addMailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMailModalLabel">Ajouter un courrier au parapheur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMailForm">
                    @csrf
                    <div class="mb-3">
                        <label for="mail_search" class="form-label">Rechercher un courrier</label>
                        <input type="text" class="form-control" id="mail_search" placeholder="Entrez un code, un nom...">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="selectAllMails">
                            <label class="form-check-label" for="selectAllMails">
                                Sélectionner tous sur cette page
                            </label>
                        </div>
                        <div id="mail_search_results" class="mt-2"></div>
                        <div id="pagination_container" class="mt-3"></div>
                    </div>
                    <input type="hidden" name="mail_ids" id="selected_mail_ids">
                </form>
                <div id="addMailError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <div class="me-auto">
                    <span id="selected_count" class="text-muted">0 courrier(s) sélectionné(s)</span>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="saveMailButton">Ajouter les courriers sélectionnés</button>
            </div>
        </div>
    </div>
</div>

{{-- Transfer to Box Modal --}}
<div class="modal fade" id="transferToBoxModal" tabindex="-1" aria-labelledby="transferToBoxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transferToBoxModalLabel">Transférer vers une ou plusieurs boîte(s)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="boxTab">
                    <li class="nav-item">
                        <button class="nav-link active" id="select-box-tab" data-bs-toggle="tab" data-bs-target="#select-box" type="button" role="tab" aria-controls="select-box" aria-selected="true">Sélectionner</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="create-box-tab" data-bs-toggle="tab" data-bs-target="#create-box" type="button" role="tab" aria-controls="create-box" aria-selected="false">Créer</button>
                    </li>
                </ul>
                <div class="tab-content" id="boxTabContent">
                    <div class="tab-pane fade show active" id="select-box" role="tabpanel" aria-labelledby="select-box-tab">
                        <div class="my-3">
                            <input type="text" id="box-search" class="form-control" placeholder="Rechercher une boîte...">
                        </div>
                        <div id="box-list-container" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-center">
                                    <div class="spinner-border" aria-hidden="true">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="create-box" role="tabpanel" aria-labelledby="create-box-tab">
                        <form id="createBoxForm" class="mt-3">
                            <div class="mb-3">
                                <label for="new_box_code" class="form-label">Code</label>
                                <input type="text" class="form-control" id="new_box_code" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_box_name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="new_box_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_box_property_id" class="form-label">Propriété</label>
                                <select class="form-select" id="new_box_property_id" required>
                                    <option value="">Chargement...</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Créer et sélectionner</button>
                        </form>
                    </div>
                </div>
                <div id="transferBoxError" class="alert alert-danger d-none mt-3" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="saveTransferToBoxButton">Transférer</button>
            </div>
        </div>
    </div>
</div>

{{-- Transfer to Dolly Modal --}}
<div class="modal fade" id="transferToDollyModal" tabindex="-1" aria-labelledby="transferToDollyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transferToDollyModalLabel">Transférer vers un ou plusieurs chariot(s)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="dollyTab">
                    <li class="nav-item">
                        <button class="nav-link active" id="select-dolly-tab" data-bs-toggle="tab" data-bs-target="#select-dolly" type="button" role="tab" aria-controls="select-dolly" aria-selected="true">Sélectionner</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="create-dolly-tab" data-bs-toggle="tab" data-bs-target="#create-dolly" type="button" role="tab" aria-controls="create-dolly" aria-selected="false">Créer</button>
                    </li>
                </ul>
                <div class="tab-content" id="dollyTabContent">
                    <div class="tab-pane fade show active" id="select-dolly" role="tabpanel" aria-labelledby="select-dolly-tab">
                        <div class="my-3">
                            <input type="text" id="dolly-search" class="form-control" placeholder="Rechercher un chariot...">
                        </div>
                        <div id="dolly-list-container" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-center">
                                    <div class="spinner-border" aria-hidden="true">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="create-dolly" role="tabpanel" aria-labelledby="create-dolly-tab">
                        <form id="createDollyForm" class="mt-3">
                            <div class="mb-3">
                                <label for="new_dolly_code" class="form-label">Code</label>
                                <input type="text" class="form-control" id="new_dolly_code" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_dolly_name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="new_dolly_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Créer et sélectionner</button>
                        </form>
                    </div>
                </div>
                <div id="transferDollyError" class="alert alert-danger d-none mt-3" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="saveTransferToDollyButton">Transférer</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Charger dynamiquement les propriétés de boîte pour le select via la route Laravel
    fetch("{{ route('mail-container.properties') }}")
        .then(response => response.json())
        .then(properties => {
            const select = document.getElementById('new_box_property_id');
            select.innerHTML = '<option value="">Choisir...</option>';
            properties.forEach(prop => {
                const opt = document.createElement('option');
                opt.value = prop.id;
                opt.textContent = prop.name;
                select.appendChild(opt);
            });
        });
    // Small utility: debounce
    function debounce(fn, delay = 300) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), delay);
        };
    }
    const batchId = {{ $mailBatch->id }};
    // Backend endpoints (named routes)
    const transferBoxesUrl = "{{ route('batch.transfer.boxes', $mailBatch) }}";
    const transferDolliesUrl = "{{ route('batch.transfer.dollies', $mailBatch) }}";

    // Helper to get CSRF token
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    }

    // Modal Handlers
    const addMailModal = new bootstrap.Modal(document.getElementById('addMailModal'));
    const transferToBoxModal = new bootstrap.Modal(document.getElementById('transferToBoxModal'));
    const transferToDollyModal = new bootstrap.Modal(document.getElementById('transferToDollyModal'));

    // --- Add Mail Logic ---
    // Variables globales pour la gestion de la sélection
    let selectedMailIds = new Set();
    let currentPage = 1;
    let totalPages = 1;

    // Add search functionality for mails
    const mailSearchInput = document.getElementById('mail_search');
    const mailSearchResults = document.getElementById('mail_search_results');
    const selectedMailIdsInput = document.getElementById('selected_mail_ids');
    const paginationContainer = document.getElementById('pagination_container');
    const selectedCountSpan = document.getElementById('selected_count');
    const selectAllCheckbox = document.getElementById('selectAllMails');

    function updateSelectedCount() {
        const count = selectedMailIds.size;
        selectedCountSpan.textContent = `${count} courrier(s) sélectionné(s)`;
        selectedMailIdsInput.value = Array.from(selectedMailIds).join(',');
    }

    // Fonction globale pour la pagination
    window.searchMails = function(query = '', page = 1) {
        return searchMailsInternal(query, page);
    };

    function searchMailsInternal(query = '', page = 1) {
        if (query.length < 2 && query.length > 0) {
            mailSearchResults.innerHTML = '';
            paginationContainer.innerHTML = '';
            return;
        }

    // Show loading indicator (build via DOM to avoid innerHTML)
    mailSearchResults.textContent = '';
    paginationContainer.textContent = '';
    const loadingWrap = document.createElement('div');
    loadingWrap.className = 'text-center';
    const loadingSpinner = document.createElement('div');
    loadingSpinner.className = 'spinner-border spinner-border-sm';
    const loadingSpan = document.createElement('span');
    loadingSpan.className = 'visually-hidden';
    loadingSpan.textContent = 'Recherche...';
    loadingSpinner.appendChild(loadingSpan);
    loadingWrap.appendChild(loadingSpinner);
    mailSearchResults.appendChild(loadingWrap);

        const searchUrl = `/mails/batch/${batchId}/available-mails?q=${encodeURIComponent(query)}&page=${page}`;

        fetch(searchUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.mails && data.mails.length > 0) {
                mailSearchResults.textContent = '';
                const listGroup = document.createElement('div');
                listGroup.className = 'list-group';
                data.mails.forEach(mail => {
                    const isSelected = selectedMailIds.has(mail.id);

                    const item = document.createElement('div');
                    item.className = 'list-group-item';

                    const row = document.createElement('div');
                    row.className = 'd-flex align-items-start';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'form-check-input me-2 mail-checkbox';
                    checkbox.value = String(mail.id);
                    checkbox.id = `mail_${mail.id}`;
                    checkbox.checked = !!isSelected;

                    const content = document.createElement('div');
                    content.className = 'flex-grow-1';
                    const label = document.createElement('label');
                    label.setAttribute('for', `mail_${mail.id}`);
                    label.className = 'form-check-label w-100';

                    const header = document.createElement('div');
                    header.className = 'd-flex w-100 justify-content-between';
                    const title = document.createElement('h6');
                    title.className = 'mb-1';
                    title.textContent = `${mail.code ?? ''} - ${mail.name ?? ''}`.trim();
                    const smallDate = document.createElement('small');
                    smallDate.className = 'text-muted';
                    smallDate.textContent = mail.date ?? '';
                    header.appendChild(title);
                    header.appendChild(smallDate);

                    const desc = document.createElement('p');
                    desc.className = 'mb-1';
                    desc.textContent = mail.description ?? '';

                    const meta = document.createElement('small');
                    meta.className = 'text-muted';
                    const parts = [];
                    if (mail.direction) parts.push(mail.direction);
                    parts.push(`Type: ${mail.type?.name ?? 'N/A'}`);
                    parts.push(`Priorité: ${mail.priority?.name ?? 'N/A'}`);
                    meta.textContent = parts.join(' | ');

                    label.appendChild(header);
                    label.appendChild(desc);
                    label.appendChild(meta);
                    content.appendChild(label);

                    row.appendChild(checkbox);
                    row.appendChild(content);
                    item.appendChild(row);
                    listGroup.appendChild(item);
                });
                mailSearchResults.appendChild(listGroup);

                // Gérer la pagination
                if (data.pagination) {
                    currentPage = data.pagination.current_page;
                    totalPages = data.pagination.last_page;

                    if (totalPages > 1) {
                        paginationContainer.textContent = '';
                        const nav = document.createElement('nav');
                        const ul = document.createElement('ul');
                        ul.className = 'pagination pagination-sm justify-content-center';

                        const mkPageItem = (label, targetPage, disabled = false, active = false) => {
                            const li = document.createElement('li');
                            li.className = 'page-item';
                            if (disabled) li.classList.add('disabled');
                            if (active) li.classList.add('active');
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'page-link';
                            btn.textContent = label;
                            if (!disabled && !active) {
                                btn.addEventListener('click', () => searchMails(query, targetPage));
                            }
                            li.appendChild(btn);
                            return li;
                        };

                        ul.appendChild(mkPageItem('Précédent', currentPage - 1, currentPage <= 1));
                        const start = Math.max(1, currentPage - 2);
                        const end = Math.min(totalPages, currentPage + 2);
                        for (let i = start; i <= end; i++) {
                            ul.appendChild(mkPageItem(String(i), i, false, i === currentPage));
                        }
                        ul.appendChild(mkPageItem('Suivant', currentPage + 1, currentPage >= totalPages));

                        nav.appendChild(ul);
                        paginationContainer.appendChild(nav);
                    }
                }

                // Ajouter les gestionnaires d'événements pour les cases à cocher
                document.querySelectorAll('.mail-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const mailId = parseInt(this.value);
                        if (this.checked) {
                            selectedMailIds.add(mailId);
                        } else {
                            selectedMailIds.delete(mailId);
                        }
                        updateSelectedCount();

                        // Mettre à jour la case "Sélectionner tout"
                        const allCheckboxes = document.querySelectorAll('.mail-checkbox');
                        const checkedCheckboxes = document.querySelectorAll('.mail-checkbox:checked');
                        selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
                    });
                });

                // Mettre à jour l'état de la case "Sélectionner tout"
                const allCheckboxes = document.querySelectorAll('.mail-checkbox');
                const checkedCheckboxes = document.querySelectorAll('.mail-checkbox:checked');
                selectAllCheckbox.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;

            } else {
                mailSearchResults.textContent = '';
                const empty = document.createElement('div');
                empty.className = 'text-muted p-2';
                empty.textContent = 'Aucun courrier trouvé ou tous les courriers sont déjà dans ce parapheur.';
                mailSearchResults.appendChild(empty);
                paginationContainer.textContent = '';
            }
        })
        .catch(error => {
            console.error('Error searching mails:', error);
            mailSearchResults.textContent = '';
            const errDiv = document.createElement('div');
            errDiv.className = 'text-danger p-2';
            errDiv.textContent = 'Erreur lors de la recherche.';
            mailSearchResults.appendChild(errDiv);
            paginationContainer.textContent = '';
        });
    }

    if (mailSearchInput) {
            const onMailSearch = debounce(function() {
                const query = mailSearchInput.value.trim();
                currentPage = 1;
                searchMailsInternal(query, currentPage);
            }, 300);
            mailSearchInput.addEventListener('input', onMailSearch);
    }

    // Charger une première page par défaut à l'ouverture du modal d'ajout
    const addMailModalEl = document.getElementById('addMailModal');
    if (addMailModalEl) {
        addMailModalEl.addEventListener('show.bs.modal', function() {
            document.getElementById('addMailError')?.classList.add('d-none');
            searchMailsInternal('', 1);
                setTimeout(() => { mailSearchInput?.focus(); }, 150);
        });
    }

    // Gestionnaire pour "Sélectionner tout"
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.mail-checkbox');
            checkboxes.forEach(checkbox => {
                const mailId = parseInt(checkbox.value);
                if (this.checked) {
                    checkbox.checked = true;
                    selectedMailIds.add(mailId);
                } else {
                    checkbox.checked = false;
                    selectedMailIds.delete(mailId);
                }
            });
            updateSelectedCount();
        });
    }

    const saveMailButton = document.getElementById('saveMailButton');
    if(saveMailButton) {
        saveMailButton.addEventListener('click', function() {
            if (selectedMailIds.size === 0) {
                document.getElementById('addMailError').textContent = 'Veuillez sélectionner au moins un courrier.';
                document.getElementById('addMailError').classList.remove('d-none');
                return;
            }
            document.getElementById('addMailError').classList.add('d-none');

            // Convertir le Set en Array pour l'envoi
            const mailIds = Array.from(selectedMailIds);

            fetch(`/mails/batches/${batchId}/mail`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ mail_ids: mailIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addMailModal.hide();
                    location.reload(); // Reload to see the new mails
                } else {
                    document.getElementById('addMailError').textContent = data.message || 'Une erreur est survenue.';
                    document.getElementById('addMailError').classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('addMailError').textContent = 'Une erreur réseau est survenue.';
                document.getElementById('addMailError').classList.remove('d-none');
            });
        });
    }

    // Clear search when modal is hidden
    document.getElementById('addMailModal').addEventListener('hidden.bs.modal', function() {
        mailSearchInput.value = '';
        selectedMailIds.clear();
        selectedMailIdsInput.value = '';
        mailSearchResults.innerHTML = '';
        paginationContainer.innerHTML = '';
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        updateSelectedCount();
        document.getElementById('addMailError').classList.add('d-none');
    });

    // --- Transfer to Box Logic ---
    const transferToBoxModalEl = document.getElementById('transferToBoxModal');
    if(transferToBoxModalEl) {
        transferToBoxModalEl.addEventListener('show.bs.modal', function () {
            // reset erreurs
            const err = document.getElementById('transferBoxError');
            if (err) { err.textContent = ''; err.classList.add('d-none'); }
            loadSelectableList("{{ route('mail-container.list') }}", '#box-list-container', 'boxes');
        });

        document.getElementById('box-search').addEventListener('keyup', function() {
            loadSelectableList("{{ route('mail-container.list') }}?q=" + this.value, '#box-list-container', 'boxes');
            });
            document.getElementById('createBoxForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const code = document.getElementById('new_box_code').value;
                const name = document.getElementById('new_box_name').value;
                const property_id = document.getElementById('new_box_property_id').value;
                fetch("{{ route('mail-container.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: code, name: name, property_id: property_id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.id) {
                        // Après création, recharger la liste et sélectionner la nouvelle boîte
                        loadSelectableList("{{ route('mail-container.list') }}", '#box-list-container', 'boxes');
                        const tab = new bootstrap.Tab(document.getElementById('select-box-tab'));
                        tab.show();
                        // Attendre que la liste soit rechargée puis cocher la nouvelle boîte
                        setTimeout(() => {
                            const newBoxCheckbox = document.getElementById(`boxes-${data.id}`);
                            if (newBoxCheckbox) {
                                newBoxCheckbox.checked = true;
                            }
                        }, 500); // délai pour laisser le temps au DOM de se mettre à jour
                    } else {
                        // Handle error
                        const errorDiv = document.getElementById('transferBoxError');
                        errorDiv.textContent = data.message || 'Erreur lors de la création.';
                        errorDiv.classList.remove('d-none');
                    }
                });
            });

        document.getElementById('createBoxForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const code = document.getElementById('new_box_code').value;
            const name = document.getElementById('new_box_name').value;
            fetch("{{ route('mail-container.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: code, name: name })
            })
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    // Creation successful, reload the list and switch back to the select tab
                    loadSelectableList("{{ route('mail-container.list') }}", '#box-list-container', 'boxes');
                    const tab = new bootstrap.Tab(document.getElementById('select-box-tab'));
                    tab.show();
                } else {
                    // Handle error
                    const errorDiv = document.getElementById('transferBoxError');
                    errorDiv.textContent = data.message || 'Erreur lors de la création.';
                    errorDiv.classList.remove('d-none');
                }
            });
        });
    }


    // --- Transfer to Dolly Logic ---
    const transferToDollyModalEl = document.getElementById('transferToDollyModal');
    if(transferToDollyModalEl) {
        transferToDollyModalEl.addEventListener('show.bs.modal', function () {
            // reset erreurs
            const err = document.getElementById('transferDollyError');
            if (err) { err.textContent = ''; err.classList.add('d-none'); }
            loadSelectableList("{{ route('dollies.list') }}", '#dolly-list-container', 'dollies');
        });

        document.getElementById('dolly-search').addEventListener('keyup', function() {
            loadSelectableList("{{ route('dollies.list') }}?q=" + encodeURIComponent(this.value), '#dolly-list-container', 'dollies');
            });
            document.getElementById('createDollyForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const code = document.getElementById('new_dolly_code').value;
                const name = document.getElementById('new_dolly_name').value;
                fetch("{{ route('dollies.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: code, name: name })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.id) {
                        // Creation successful, reload the list and switch back to the select tab
                        loadSelectableList("{{ route('dollies.list') }}", '#dolly-list-container', 'dollies');
                        const tab = new bootstrap.Tab(document.getElementById('select-dolly-tab'));
                        tab.show();
                        document.getElementById('createDollyForm').reset();
                    } else {
                        // Handle error
                        const errorDiv = document.getElementById('transferDollyError');
                        errorDiv.textContent = data.message || 'Erreur lors de la création.';
                        errorDiv.classList.remove('d-none');
                    }
                });
        });

        document.getElementById('createDollyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const code = document.getElementById('new_dolly_code').value;
            const name = document.getElementById('new_dolly_name').value;
            // Utiliser une URL de route connue
            fetch("{{ route('dollies.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: code, name: name })
            })
            .then(response => response.json())
        .then(data => {
                if (data.id) {
                    // Creation successful, reload the list and switch back to the select tab
            loadSelectableList("{{ route('dollies.list') }}", '#dolly-list-container', 'dollies');
                    const tab = new bootstrap.Tab(document.getElementById('select-dolly-tab'));
                    tab.show();
                    document.getElementById('createDollyForm').reset();
                } else {
                    // Handle error
                    const errorDiv = document.getElementById('transferDollyError');
                    errorDiv.textContent = data.message || 'Erreur lors de la création.';
                    errorDiv.classList.remove('d-none');
                }
            });
        });
    }

    function loadSelectableList(url, containerSelector, type) {
        const container = document.querySelector(containerSelector);

        if (!container) {
        // Conteneur non trouvé, on arrête silencieusement
            return;
        }

    // Loading placeholder
    container.textContent = '';
    const spinnerWrap = document.createElement('div');
    spinnerWrap.className = 'text-center';
    const spinner = document.createElement('div');
    spinner.className = 'spinner-border';
    const spinnerSpan = document.createElement('span');
    spinnerSpan.className = 'visually-hidden';
    spinnerSpan.textContent = 'Loading...';
    spinner.appendChild(spinnerSpan);
    spinnerWrap.appendChild(spinner);
    container.appendChild(spinnerWrap);

    // Utiliser directement l'URL fournie (déjà une route Laravel)
    fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const items = Array.isArray(data) ? data : (data.data || []);
            container.textContent = '';
            const ul = document.createElement('ul');
            ul.className = 'list-group';

            if (items.length > 0) {
                items.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    const checkWrap = document.createElement('div');
                    checkWrap.className = 'form-check';

                    const input = document.createElement('input');
                    input.className = 'form-check-input';
                    input.type = 'checkbox';
                    input.value = String(item.id);
                    input.id = `${type}-${item.id}`;

                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.setAttribute('for', `${type}-${item.id}`);
                    label.textContent = `${item.code || ''} - ${item.name || ''}`.trim();

                    checkWrap.appendChild(input);
                    checkWrap.appendChild(label);
                    li.appendChild(checkWrap);
                    ul.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.className = 'list-group-item text-muted';
                li.textContent = 'Aucun élément trouvé.';
                ul.appendChild(li);
            }
            container.appendChild(ul);
        })
        .catch(error => {
            container.textContent = '';
            const err = document.createElement('div');
            err.className = 'alert alert-danger';
            err.textContent = `Erreur de chargement de la liste: ${error.message}`;
            container.appendChild(err);
        });
    }

    // --- Gestion des cases à cocher pour transfert ---
    const selectedTransferMailIds = new Set();
    const selectAllTransferCheckbox = document.getElementById('selectAllMailsForTransfer');
    const selectedMailsCountSpan = document.getElementById('selectedMailsCount');

    function updateSelectedTransferCount() {
        const count = selectedTransferMailIds.size;
        if (selectedMailsCountSpan) {
            selectedMailsCountSpan.textContent = `${count} courrier(s) sélectionné(s) pour transfert`;
        }

        // Mettre à jour l'état de la case "Sélectionner tout"
        const allTransferCheckboxes = document.querySelectorAll('.mail-transfer-checkbox');
        const checkedTransferCheckboxes = document.querySelectorAll('.mail-transfer-checkbox:checked');

        if (!selectAllTransferCheckbox) return;

        if (allTransferCheckboxes.length === 0) {
            selectAllTransferCheckbox.checked = false;
            selectAllTransferCheckbox.indeterminate = false;
        } else if (checkedTransferCheckboxes.length === allTransferCheckboxes.length) {
            selectAllTransferCheckbox.checked = true;
            selectAllTransferCheckbox.indeterminate = false;
        } else if (checkedTransferCheckboxes.length > 0) {
            selectAllTransferCheckbox.checked = false;
            selectAllTransferCheckbox.indeterminate = true;
        } else {
            selectAllTransferCheckbox.checked = false;
            selectAllTransferCheckbox.indeterminate = false;
        }
    }

    // Gestionnaire pour les cases à cocher individuelles
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('mail-transfer-checkbox')) {
            const mailId = parseInt(e.target.value);
            if (e.target.checked) {
                selectedTransferMailIds.add(mailId);
            } else {
                selectedTransferMailIds.delete(mailId);
            }
            updateSelectedTransferCount();
        }
    });

    // Gestionnaire pour "Sélectionner tout" - Version améliorée
    if (selectAllTransferCheckbox) {
        // Initialisation immédiate du sélecteur "tous"
        const allCheckboxes = document.querySelectorAll('.mail-transfer-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.mail-transfer-checkbox:checked');
        // Synchroniser l'ensemble sélectionné avec l'état initial
        selectedTransferMailIds.clear();
        checkedCheckboxes.forEach(cb => selectedTransferMailIds.add(parseInt(cb.value)));
        selectAllTransferCheckbox.checked = (allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length);
        selectAllTransferCheckbox.indeterminate = (checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length);

        // Attacher l'événement click plutôt que change pour meilleure compatibilité
        selectAllTransferCheckbox.addEventListener('click', function() {
            const isChecked = this.checked;

            // Sélectionner/désélectionner toutes les cases
            const checkboxes = document.querySelectorAll('.mail-transfer-checkbox');

            // Désactiver temporairement pour éviter des déclenchements multiples
            this.disabled = true;

            checkboxes.forEach((checkbox, index) => {
                if (checkbox.checked !== isChecked) {
                    checkbox.checked = isChecked;
                    const mailId = parseInt(checkbox.value);
                    if (isChecked) {
                        selectedTransferMailIds.add(mailId);
                    } else {
                        selectedTransferMailIds.delete(mailId);
                    }
                }
            });

            // Réactiver après traitement
            this.disabled = false;
            this.indeterminate = false;

            updateSelectedTransferCount();
        });
    }

    // Initialisation du compteur
    updateSelectedTransferCount();

    // Modifier les gestionnaires de transfert pour inclure la sélection
    document.getElementById('saveTransferToBoxButton').addEventListener('click', function() {
        const errorDiv = document.getElementById('transferBoxError');
        errorDiv.textContent = '';
        errorDiv.classList.add('d-none');

        const selectedBoxes = Array.from(document.querySelectorAll('#box-list-container input:checked'))
            .map(el => parseInt(el.value))
            .filter(v => !Number.isNaN(v));
        const selectedMails = Array.from(selectedTransferMailIds).map(v => parseInt(v));

        if (selectedBoxes.length === 0) {
            errorDiv.textContent = 'Veuillez sélectionner au moins une boîte.';
            errorDiv.classList.remove('d-none');
            return;
        }

        if (selectedMails.length === 0) {
            alert('Veuillez sélectionner au moins un courrier à transférer.');
            return;
        }

        const btn = this;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Transfert...';

        fetch(transferBoxesUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ mail_ids: selectedMails, box_ids: selectedBoxes })
        })
        .then(async (response) => {
            const data = await response.json().catch(() => ({ success: false, message: 'Réponse invalide du serveur.' }));
            if (!response.ok || !data.success) {
                const msg = data.message || 'Le transfert a échoué.';
                throw new Error(msg);
            }
            return data;
        })
        .then(() => {
            transferToBoxModal.hide();
            location.reload();
        })
        .catch(err => {
            errorDiv.textContent = err.message || 'Une erreur est survenue lors du transfert.';
            errorDiv.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });

    document.getElementById('saveTransferToDollyButton').addEventListener('click', function() {
        const errorDiv = document.getElementById('transferDollyError');
        errorDiv.textContent = '';
        errorDiv.classList.add('d-none');

        const selectedDollies = Array.from(document.querySelectorAll('#dolly-list-container input:checked'))
            .map(el => parseInt(el.value))
            .filter(v => !Number.isNaN(v));
        const selectedMails = Array.from(selectedTransferMailIds).map(v => parseInt(v));

        if (selectedDollies.length === 0) {
            errorDiv.textContent = 'Veuillez sélectionner au moins un chariot.';
            errorDiv.classList.remove('d-none');
            return;
        }

        if (selectedMails.length === 0) {
            alert('Veuillez sélectionner au moins un courrier à transférer.');
            return;
        }

        const btn = this;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Transfert...';

        fetch(transferDolliesUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ mail_ids: selectedMails, dolly_ids: selectedDollies })
        })
        .then(async (response) => {
            const data = await response.json().catch(() => ({ success: false, message: 'Réponse invalide du serveur.' }));
            if (!response.ok || !data.success) {
                const msg = data.message || 'Le transfert a échoué.';
                throw new Error(msg);
            }
            return data;
        })
        .then(() => {
            transferToDollyModal.hide();
            location.reload();
        })
        .catch(err => {
            errorDiv.textContent = err.message || 'Une erreur est survenue lors du transfert.';
            errorDiv.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });
});
</script>
@endpush
@endsection
